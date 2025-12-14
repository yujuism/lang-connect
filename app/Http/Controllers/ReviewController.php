<?php

namespace App\Http\Controllers;

use App\Models\PracticeSession;
use App\Models\SessionReview;
use App\Services\AchievementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Store a new review for a session.
     */
    public function store(Request $request, PracticeSession $session)
    {
        // Verify user is part of this session
        if ($session->user1_id !== Auth::id() && $session->user2_id !== Auth::id()) {
            abort(403);
        }

        // Check if session is completed
        if ($session->status !== 'completed') {
            return back()->with('error', 'You can only review completed sessions.');
        }

        // Determine who is being reviewed
        $reviewedUserId = $session->user1_id === Auth::id() ? $session->user2_id : $session->user1_id;

        // Check if user already reviewed this session
        $existingReview = SessionReview::where('session_id', $session->id)
            ->where('reviewer_id', Auth::id())
            ->first();

        if ($existingReview) {
            return back()->with('error', 'You have already reviewed this session.');
        }

        // Validate input
        $validated = $request->validate([
            'overall_rating' => 'required|integer|min:1|max:5',
            'helpfulness_rating' => 'required|integer|min:1|max:5',
            'patience_rating' => 'required|integer|min:1|max:5',
            'clarity_rating' => 'required|integer|min:1|max:5',
            'engagement_rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'is_public' => 'boolean',
            'topics_rated_well' => 'nullable|array',
        ]);

        // Create review
        SessionReview::create([
            'session_id' => $session->id,
            'reviewer_id' => Auth::id(),
            'reviewed_user_id' => $reviewedUserId,
            'overall_rating' => $validated['overall_rating'],
            'helpfulness_rating' => $validated['helpfulness_rating'],
            'patience_rating' => $validated['patience_rating'],
            'clarity_rating' => $validated['clarity_rating'],
            'engagement_rating' => $validated['engagement_rating'],
            'comment' => $validated['comment'] ?? null,
            'is_public' => $request->has('is_public'),
            'topics_rated_well' => $validated['topics_rated_well'] ?? [],
        ]);

        // Award karma points to reviewed user based on rating
        $reviewedUser = \App\Models\User::find($reviewedUserId);
        if ($reviewedUser->progress) {
            $karmaPoints = $this->calculateKarmaFromRating($validated['overall_rating']);
            $reviewedUser->progress->increment('karma_points', $karmaPoints);

            // Check for new achievements (rating-based)
            $achievementService = new AchievementService();
            $newAchievements = $achievementService->checkAndAwardAchievements($reviewedUser);

            if (!empty($newAchievements)) {
                session()->flash('new_achievements', $newAchievements);
            }
        }

        return redirect()->route('sessions.index')
            ->with('success', 'Thank you for your review! Karma points awarded.');
    }

    /**
     * Calculate karma points based on rating.
     */
    protected function calculateKarmaFromRating(int $rating): int
    {
        return match($rating) {
            5 => 20,  // Excellent
            4 => 15,  // Good
            3 => 10,  // Average
            2 => 5,   // Below average
            1 => 0,   // Poor
            default => 0,
        };
    }
}
