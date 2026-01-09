<?php

namespace App\Http\Controllers;

use App\Models\PracticeSession;
use App\Models\LearningRequest;
use App\Models\UserProgress;
use App\Services\AchievementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SessionController extends Controller
{
    /**
     * Display a listing of user's sessions.
     */
    public function index()
    {
        $sessions = PracticeSession::where('user1_id', Auth::id())
            ->orWhere('user2_id', Auth::id())
            ->with(['user1', 'user2', 'language'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('sessions.index', compact('sessions'));
    }

    /**
     * Start a new session from a learning request.
     */
    public function startFromRequest(LearningRequest $learningRequest)
    {
        // Verify user is part of this request
        if ($learningRequest->user_id !== Auth::id() && $learningRequest->matched_with_user_id !== Auth::id()) {
            abort(403, 'You are not authorized to start this session.');
        }

        // Check if request is matched
        if ($learningRequest->status !== 'matched') {
            return back()->with('error', 'This request must be matched before starting a session.');
        }

        // Create practice session
        $session = PracticeSession::create([
            'request_id' => $learningRequest->id,
            'user1_id' => $learningRequest->user_id,
            'user2_id' => $learningRequest->matched_with_user_id,
            'language_id' => $learningRequest->language_id,
            'topic' => $learningRequest->topic_name ?? ucfirst($learningRequest->topic_category),
            'session_type' => 'random',
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        return redirect()->route('sessions.show', $session)
            ->with('success', 'Session started! Good luck with your practice.');
    }

    /**
     * Display the specified session.
     */
    public function show(PracticeSession $session)
    {
        // Verify user is part of this session
        if ($session->user1_id !== Auth::id() && $session->user2_id !== Auth::id()) {
            abort(403);
        }

        $session->load(['user1', 'user2', 'language', 'request']);

        // Determine partner
        $partner = $session->user1_id === Auth::id() ? $session->user2 : $session->user1;

        // Get PDF URL if exists
        $pdfUrl = $session->pdf_path ? Storage::disk('minio')->url($session->pdf_path) : null;

        return view('sessions.show', compact('session', 'partner', 'pdfUrl'));
    }

    /**
     * Save session notes (AJAX).
     */
    public function saveNotes(Request $request, PracticeSession $session)
    {
        // Verify user is part of this session
        if ($session->user1_id !== Auth::id() && $session->user2_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:10000',
        ]);

        $session->update([
            'notes' => $validated['notes'],
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Complete a session.
     */
    public function complete(Request $request, PracticeSession $session)
    {
        // Verify user is part of this session
        if ($session->user1_id !== Auth::id() && $session->user2_id !== Auth::id()) {
            abort(403);
        }

        if ($session->status !== 'in_progress') {
            return back()->with('error', 'This session is not in progress.');
        }

        // Use duration from request (from frontend timer) or calculate it
        $duration = $request->input('duration_minutes');
        if (!$duration) {
            $startedAt = Carbon::parse($session->started_at);
            $duration = $startedAt->diffInMinutes(now());
        }

        // Update session
        $session->update([
            'status' => 'completed',
            'completed_at' => now(),
            'duration_minutes' => $duration,
        ]);

        // Update user progress for helper
        $helper = $session->user2; // Matched helper
        if ($helper->progress) {
            $helper->progress->increment('total_sessions');
            $helper->progress->increment('members_helped');
            $helper->progress->increment('contribution_hours', $duration / 60);

            // Update level based on contribution hours
            $this->updateUserLevel($helper->progress);

            // Check for new achievements for helper
            $achievementService = new AchievementService();
            $newAchievements = $achievementService->checkAndAwardAchievements($helper);

            if (!empty($newAchievements)) {
                session()->flash('new_achievements_helper', $newAchievements);
            }
        }

        // Update user progress for learner
        $learner = $session->user1;
        if ($learner->progress) {
            $learner->progress->increment('sessions_received');

            // Check for achievements for learner (streaks, etc.)
            $achievementService = new AchievementService();
            $newAchievements = $achievementService->checkAndAwardAchievements($learner);

            if (!empty($newAchievements)) {
                session()->flash('new_achievements_learner', $newAchievements);
            }
        }

        // Update learning request status
        if ($session->request) {
            $session->request->update(['status' => 'completed']);
        }

        return redirect()->route('sessions.review', $session)
            ->with('success', 'Session completed! Please leave a review.');
    }

    /**
     * Show review form for a session.
     */
    public function review(PracticeSession $session)
    {
        // Verify user is part of this session
        if ($session->user1_id !== Auth::id() && $session->user2_id !== Auth::id()) {
            abort(403);
        }

        if ($session->status !== 'completed') {
            return back()->with('error', 'You can only review completed sessions.');
        }

        $session->load(['user1', 'user2', 'language']);

        // Determine who to review (the other person)
        $reviewedUser = $session->user1_id === Auth::id() ? $session->user2 : $session->user1;

        return view('sessions.review', compact('session', 'reviewedUser'));
    }

    /**
     * Update user level based on contribution hours.
     */
    protected function updateUserLevel(UserProgress $progress)
    {
        $hours = $progress->contribution_hours;

        // Level thresholds
        $levels = [
            1 => 0,
            2 => 5,
            3 => 15,
            4 => 30,
            5 => 50,
            6 => 75,
            7 => 100,
            8 => 150,
            9 => 200,
            10 => 250,
        ];

        foreach (array_reverse($levels, true) as $level => $requiredHours) {
            if ($hours >= $requiredHours) {
                if ($progress->level < $level) {
                    $progress->update(['level' => $level]);
                }
                break;
            }
        }
    }
}
