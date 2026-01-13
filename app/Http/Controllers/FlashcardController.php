<?php

namespace App\Http\Controllers;

use App\Models\Flashcard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FlashcardController extends Controller
{
    /**
     * Create a new flashcard.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'front' => 'required|string|max:500',
            'back' => 'required|string|max:1000',
            'language' => 'required|string|max:50',
            'practice_session_id' => 'nullable|integer|exists:practice_sessions,id',
        ]);

        $flashcard = Flashcard::create([
            'user_id' => Auth::id(),
            'front' => $validated['front'],
            'back' => $validated['back'],
            'language' => $validated['language'],
            'practice_session_id' => $validated['practice_session_id'] ?? null,
            'mastery_level' => 0,
            'repetitions' => 0,
            'ease_factor' => 2.5,
            'interval_days' => 0,
            'next_review_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'flashcard' => $flashcard,
            ]);
        }

        return redirect()->back()->with('success', 'Flashcard created!');
    }

    /**
     * Display user's flashcard decks.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get flashcard counts by language
        $languages = Flashcard::where('user_id', $user->id)
            ->selectRaw('language as target_language, COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN mastery_level = 0 THEN 1 ELSE 0 END) as new_count')
            ->selectRaw('SUM(CASE WHEN mastery_level BETWEEN 1 AND 2 THEN 1 ELSE 0 END) as learning_count')
            ->selectRaw('SUM(CASE WHEN mastery_level >= 3 THEN 1 ELSE 0 END) as mastered_count')
            ->selectRaw('SUM(CASE WHEN next_review_at <= NOW() THEN 1 ELSE 0 END) as due_count')
            ->groupBy('language')
            ->get();

        // Get cards due for review
        $dueCards = Flashcard::where('user_id', $user->id)
            ->where('next_review_at', '<=', now())
            ->count();

        return view('flashcards.index', [
            'languages' => $languages,
            'dueCards' => $dueCards,
        ]);
    }

    /**
     * Show flashcard review session.
     */
    public function review(Request $request)
    {
        $user = Auth::user();
        $language = $request->query('language');

        // Build query for cards to review
        $query = Flashcard::where('user_id', $user->id)
            ->where('next_review_at', '<=', now());

        if ($language) {
            $query->where('language', $language);
        }

        // Get cards ordered by next_review_at (oldest first)
        $cards = $query->orderBy('next_review_at')->limit(20)->get();

        if ($cards->isEmpty()) {
            return redirect()->route('flashcards.index')
                ->with('message', 'No cards due for review!');
        }

        return view('flashcards.review', [
            'cards' => $cards,
            'language' => $language,
            'totalDue' => $query->count(),
        ]);
    }

    /**
     * Get next card for AJAX review.
     */
    public function nextCard(Request $request)
    {
        $user = Auth::user();
        $language = $request->query('language');
        $excludeIds = $request->query('exclude', []);

        $query = Flashcard::where('user_id', $user->id)
            ->where('next_review_at', '<=', now());

        if ($language) {
            $query->where('language', $language);
        }

        if (!empty($excludeIds)) {
            $query->whereNotIn('id', $excludeIds);
        }

        $card = $query->orderBy('next_review_at')->first();

        if (!$card) {
            return response()->json(['done' => true]);
        }

        return response()->json([
            'done' => false,
            'card' => [
                'id' => $card->id,
                'front' => $card->front,
                'back' => $card->back,
                'target_language' => $card->language,
                'native_language' => 'en', // Default, can be expanded later
                'mastery_level' => $card->mastery_level,
                'repetitions' => $card->repetitions,
            ],
        ]);
    }

    /**
     * Submit answer for a flashcard (SM-2 algorithm).
     */
    public function answer(Request $request, Flashcard $flashcard)
    {
        // Verify ownership
        if ($flashcard->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'quality' => 'required|integer|min:0|max:5',
        ]);

        // Apply SM-2 algorithm
        $flashcard->applyReview($validated['quality']);
        $flashcard->save();

        return response()->json([
            'success' => true,
            'next_review_at' => $flashcard->next_review_at->toISOString(),
            'interval_days' => $flashcard->interval_days,
            'mastery_level' => $flashcard->mastery_level,
        ]);
    }

    /**
     * Show cards from a specific session.
     */
    public function fromSession(Request $request, int $sessionId)
    {
        $user = Auth::user();

        $cards = Flashcard::where('user_id', $user->id)
            ->where('practice_session_id', $sessionId)
            ->orderBy('created_at')
            ->get();

        return view('flashcards.session', [
            'cards' => $cards,
            'sessionId' => $sessionId,
        ]);
    }

    /**
     * Delete a flashcard.
     */
    public function destroy(Flashcard $flashcard)
    {
        if ($flashcard->user_id !== Auth::id()) {
            abort(403);
        }

        $flashcard->delete();

        return response()->json(['success' => true]);
    }
}
