<?php

namespace App\Http\Controllers;

use App\Models\LearningRequest;
use App\Models\Language;
use App\Services\MatchingService;
use App\Mail\MatchFoundMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class LearningRequestController extends Controller
{
    protected $matchingService;

    public function __construct(MatchingService $matchingService)
    {
        $this->matchingService = $matchingService;
    }

    /**
     * Display a listing of the user's learning requests.
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $requests = $user->learningRequests()
            ->with(['language', 'matchedWithUser'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('learning-requests.index', compact('requests'));
    }

    /**
     * Show the form for creating a new learning request.
     */
    public function create()
    {
        $languages = Language::where('is_active', true)->get();

        $topicCategories = [
            'grammar' => 'Grammar',
            'vocabulary' => 'Vocabulary',
            'pronunciation' => 'Pronunciation',
            'expression' => 'Expression',
            'conversation' => 'Conversation',
            'other' => 'Other',
        ];

        $proficiencyLevels = [
            'A1' => 'A1 - Beginner',
            'A2' => 'A2 - Elementary',
            'B1' => 'B1 - Intermediate',
            'B2' => 'B2 - Upper Intermediate',
            'C1' => 'C1 - Advanced',
            'C2' => 'C2 - Proficient',
        ];

        return view('learning-requests.create', compact('languages', 'topicCategories', 'proficiencyLevels'));
    }

    /**
     * Store a newly created learning request in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'language_id' => 'required|exists:languages,id',
            'topic_category' => 'required|in:grammar,vocabulary,pronunciation,expression,conversation,other',
            'topic_name' => 'nullable|string|max:255',
            'specific_question' => 'required|string|max:1000',
            'proficiency_level' => 'required|in:A1,A2,B1,B2,C1,C2',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $learningRequest = $user->learningRequests()->create([
            ...$validated,
            'status' => 'pending',
        ]);

        // Notify potential helpers (don't auto-match)
        $notifiedCount = $this->matchingService->notifyPotentialHelpers($learningRequest);

        if ($notifiedCount > 0) {
            return redirect()->route('learning-requests.show', $learningRequest)
                ->with('success', "Your request has been posted! We've notified {$notifiedCount} potential helpers who can assist you.");
        }

        return redirect()->route('learning-requests.show', $learningRequest)
            ->with('success', 'Your learning request has been posted. Helpers can browse and accept it.');
    }

    /**
     * Display the specified learning request.
     */
    public function show(LearningRequest $learningRequest)
    {
        $learningRequest->load(['language', 'matchedWithUser.progress', 'user.progress']);

        $isOwner = $learningRequest->user_id === Auth::id();
        $potentialMatches = collect();

        // Only show potential matches to the owner
        if ($isOwner) {
            $potentialMatches = $this->matchingService->findMultipleMatches($learningRequest, 5);
        }

        return view('learning-requests.show', compact('learningRequest', 'potentialMatches', 'isOwner'));
    }

    /**
     * Cancel a learning request.
     */
    public function cancel(LearningRequest $learningRequest)
    {
        // Ensure user owns this request
        if ($learningRequest->user_id !== Auth::id()) {
            abort(403);
        }

        if ($learningRequest->status === 'completed') {
            return back()->with('error', 'Cannot cancel a completed request.');
        }

        $learningRequest->update(['status' => 'cancelled']);

        return redirect()->route('learning-requests.index')
            ->with('success', 'Request cancelled successfully.');
    }

    /**
     * Browse all pending learning requests (for helpers).
     */
    public function browse()
    {
        $requests = LearningRequest::where('status', 'pending')
            ->where('user_id', '!=', Auth::id())
            ->with(['user', 'language'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('learning-requests.browse', compact('requests'));
    }

    /**
     * Accept a learning request (become the helper).
     */
    public function accept(LearningRequest $learningRequest)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($learningRequest->status !== 'pending') {
            return back()->with('error', 'This request is no longer available.');
        }

        // Don't allow user to accept their own request
        if ($learningRequest->user_id === $user->id) {
            return back()->with('error', 'You cannot accept your own request.');
        }

        $learningRequest->update([
            'matched_with_user_id' => $user->id,
            'matched_at' => now(),
            'status' => 'matched',
        ]);

        // Reload the learning request with language relationship
        $learningRequest->load('language');

        // Get the requester
        $requester = $learningRequest->user;

        // Send in-app notification to the requester
        \App\Models\Notification::createNotification(
            $learningRequest->user_id,
            'request_matched',
            'Your Request Has Been Matched!',
            $user->name . ' has accepted your request for ' . $learningRequest->language->name . ' help',
            [
                'request_id' => $learningRequest->id,
                'helper_id' => $user->id,
                'helper_name' => $user->name,
            ]
        );

        // Send email notification if user has email notifications enabled
        if ($requester->email && (!isset($requester->email_notifications_enabled) || $requester->email_notifications_enabled)) {
            Mail::to($requester->email)->send(new MatchFoundMail($learningRequest, $user, $requester));
        }

        return redirect()->route('learning-requests.show', $learningRequest)
            ->with('success', 'You\'ve accepted this request! The requester has been notified. You can now message them to start a session.');
    }
}
