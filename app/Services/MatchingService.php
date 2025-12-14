<?php

namespace App\Services;

use App\Models\LearningRequest;
use App\Models\User;
use App\Models\UserLanguage;
use App\Models\UserProgress;
use App\Models\UserExpertise;
use Illuminate\Support\Collection;

class MatchingService
{
    /**
     * Find the best match for a learning request
     *
     * @param LearningRequest $request
     * @return User|null
     */
    public function findMatch(LearningRequest $request): ?User
    {
        // Get potential helpers: users who are native or proficient in the requested language
        $potentialHelpers = UserLanguage::where('language_id', $request->language_id)
            ->where('user_id', '!=', $request->user_id)
            ->whereIn('proficiency_level', ['native', 'C2', 'C1'])
            ->with(['user.progress'])
            ->get()
            ->pluck('user')
            ->filter();

        if ($potentialHelpers->isEmpty()) {
            return null;
        }

        // Score each potential helper
        $scoredHelpers = $potentialHelpers->map(function ($helper) use ($request) {
            return [
                'user' => $helper,
                'score' => $this->calculateMatchScore($helper, $request),
            ];
        });

        // Sort by score (highest first)
        $bestMatch = $scoredHelpers->sortByDesc('score')->first();

        return $bestMatch['user'] ?? null;
    }

    /**
     * Calculate match score for a potential helper
     *
     * Scoring factors:
     * - Topic expertise: +50 points if helper has expertise in the specific topic
     * - Contribution balance: +30 points if helper has positive balance (helped more than received)
     * - Level: +5 points per level
     * - Karma: +0.1 point per karma point
     * - Recent activity: +20 points if helper has sessions in last 7 days
     *
     * @param User $helper
     * @param LearningRequest $request
     * @return float
     */
    protected function calculateMatchScore(User $helper, LearningRequest $request): float
    {
        $score = 0;

        // Topic expertise
        // TODO: Implement when user_expertises table is created
        // if ($request->topic_category && $request->topic_name) {
        //     $expertise = UserExpertise::where('user_id', $helper->id)
        //         ->where('category', $request->topic_category)
        //         ->where('topic_name', $request->topic_name)
        //         ->first();
        //
        //     if ($expertise) {
        //         $score += 50;
        //     }
        // }

        // Progress and contribution
        if ($helper->progress) {
            // Contribution balance (helped more than received)
            $balance = $helper->progress->total_sessions - $helper->progress->sessions_received;
            if ($balance > 0) {
                $score += 30;
            }

            // Level
            $score += $helper->progress->level * 5;

            // Karma
            $score += $helper->progress->karma_points * 0.1;
        }

        // Recent activity (sessions in last 7 days)
        $recentSessions = $helper->sessionsAsUser1()
            ->where('created_at', '>=', now()->subDays(7))
            ->count() + $helper->sessionsAsUser2()
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        if ($recentSessions > 0) {
            $score += 20;
        }

        return $score;
    }

    /**
     * Find multiple potential matches for a request
     *
     * @param LearningRequest $request
     * @param int $limit
     * @return Collection
     */
    public function findMultipleMatches(LearningRequest $request, int $limit = 5): Collection
    {
        $potentialHelpers = UserLanguage::where('language_id', $request->language_id)
            ->where('user_id', '!=', $request->user_id)
            ->whereIn('proficiency_level', ['native', 'C2', 'C1'])
            ->with(['user.progress'])
            ->get()
            ->pluck('user')
            ->filter();

        if ($potentialHelpers->isEmpty()) {
            return collect();
        }

        return $potentialHelpers
            ->map(fn($helper) => [
                'user' => $helper,
                'score' => $this->calculateMatchScore($helper, $request),
            ])
            ->sortByDesc('score')
            ->take($limit)
            ->values();
    }

    /**
     * Notify potential helpers about a new learning request
     * (Does NOT auto-match, just notifies helpers)
     *
     * @param LearningRequest $request
     * @param int $notifyCount Number of top helpers to notify
     * @return int Number of helpers notified
     */
    public function notifyPotentialHelpers(LearningRequest $request, int $notifyCount = 3): int
    {
        $potentialHelpers = $this->findMultipleMatches($request, $notifyCount);

        if ($potentialHelpers->isEmpty()) {
            return 0;
        }

        $notifiedCount = 0;
        foreach ($potentialHelpers as $match) {
            $helper = $match['user'];

            // Create notification for this helper
            \App\Models\Notification::createNotification(
                $helper->id,
                'learning_request',
                'New Learning Request Available',
                $request->user->name . ' needs help with ' . $request->language->name . ': ' . $request->topic_category,
                [
                    'request_id' => $request->id,
                    'user_id' => $request->user_id,
                    'language' => $request->language->name,
                    'topic' => $request->topic_category,
                ]
            );

            $notifiedCount++;
        }

        return $notifiedCount;
    }

    /**
     * DEPRECATED: Old auto-match function - keeping for backwards compatibility
     * Use notifyPotentialHelpers() instead for better UX
     */
    public function autoMatch(LearningRequest $request): bool
    {
        // Instead of auto-matching, just notify potential helpers
        $notified = $this->notifyPotentialHelpers($request);
        return $notified > 0;
    }
}
