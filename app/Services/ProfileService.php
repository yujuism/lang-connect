<?php

namespace App\Services;

use App\Models\User;
use App\Models\VocabularyEntry;
use App\Models\Flashcard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Profile Service - Handles all profile-related business logic
 *
 * Responsibilities:
 * - User profile data aggregation
 * - Avatar management
 * - Language management
 * - Statistics calculations
 */
class ProfileService
{
    /**
     * Get complete profile data for display
     *
     * @param User $user
     * @return array
     */
    public function getProfileData(User $user): array
    {
        // Eager load relationships
        $user->load([
            'progress',
            'languages',
            'reviewsReceived' => function ($query) {
                $query->where('is_public', true)
                    ->with(['reviewer', 'session.language'])
                    ->latest()
                    ->limit(10);
            }
        ]);

        return [
            'user' => $user,
            'avgRatings' => $this->calculateAverageRatings($user),
            'achievements' => $this->getUserAchievements($user),
            'recentSessionsCount' => $this->getRecentSessionsCount($user),
            'vocabularyStats' => $this->getVocabularyStats($user),
            'flashcardStats' => $this->getFlashcardStats($user),
        ];
    }

    /**
     * Calculate average ratings across all reviews
     *
     * @param User $user
     * @return object|null
     */
    public function calculateAverageRatings(User $user): ?object
    {
        return $user->reviewsReceived()
            ->where('is_public', true)
            ->selectRaw('
                AVG(overall_rating) as avg_overall,
                AVG(helpfulness_rating) as avg_helpfulness,
                AVG(patience_rating) as avg_patience,
                AVG(clarity_rating) as avg_clarity,
                AVG(engagement_rating) as avg_engagement,
                COUNT(*) as total_reviews
            ')
            ->first();
    }

    /**
     * Get user's achievements with details
     *
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserAchievements(User $user)
    {
        return $user->achievements()
            ->with('achievement')
            ->latest()
            ->get();
    }

    /**
     * Get count of recent sessions (last 30 days)
     *
     * @param User $user
     * @return int
     */
    public function getRecentSessionsCount(User $user): int
    {
        $thirtyDaysAgo = now()->subDays(30);

        $asUser1 = $user->sessionsAsUser1()
            ->where('status', 'completed')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->count();

        $asUser2 = $user->sessionsAsUser2()
            ->where('status', 'completed')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->count();

        return $asUser1 + $asUser2;
    }

    /**
     * Update user profile with avatar handling
     *
     * @param User $user
     * @param array $data
     * @return User
     */
    public function updateProfile(User $user, array $data): User
    {
        // Handle avatar upload if present
        if (isset($data['avatar'])) {
            $data['avatar_path'] = $this->handleAvatarUpload($user, $data['avatar']);
            unset($data['avatar']); // Remove the file object, keep only the path
        }

        $user->update($data);

        return $user->fresh();
    }

    /**
     * Handle avatar upload and delete old avatar
     *
     * @param User $user
     * @param \Illuminate\Http\UploadedFile $avatar
     * @return string
     */
    private function handleAvatarUpload(User $user, $avatar): string
    {
        // Delete old avatar if exists
        if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        // Store new avatar
        return $avatar->store('avatars', 'public');
    }

    /**
     * Update user's languages (replaces all existing)
     *
     * Uses transaction to ensure data consistency
     *
     * @param User $user
     * @param array $languages
     * @return void
     */
    public function updateLanguages(User $user, array $languages): void
    {
        DB::transaction(function () use ($user, $languages) {
            // Delete existing language entries
            $user->userLanguages()->delete();

            // Create new ones
            foreach ($languages as $languageData) {
                $user->userLanguages()->create([
                    'language_id' => $languageData['language_id'],
                    'proficiency_level' => $languageData['proficiency_level'],
                    'can_help' => $languageData['can_help'] ?? false,
                ]);
            }
        });
    }

    /**
     * Get vocabulary statistics for a user
     *
     * @param User $user
     * @return array
     */
    public function getVocabularyStats(User $user): array
    {
        $totalWords = VocabularyEntry::where('user_id', $user->id)->count();

        $byLanguage = VocabularyEntry::where('user_id', $user->id)
            ->selectRaw('language, COUNT(*) as count')
            ->groupBy('language')
            ->pluck('count', 'language')
            ->toArray();

        $recentWords = VocabularyEntry::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $frequentWords = VocabularyEntry::where('user_id', $user->id)
            ->where('times_used', '>', 1)
            ->count();

        return [
            'total' => $totalWords,
            'by_language' => $byLanguage,
            'recent_week' => $recentWords,
            'frequent' => $frequentWords,
        ];
    }

    /**
     * Get flashcard statistics for a user
     *
     * @param User $user
     * @return array
     */
    public function getFlashcardStats(User $user): array
    {
        $stats = Flashcard::where('user_id', $user->id)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN mastery_level = 0 THEN 1 ELSE 0 END) as new_count')
            ->selectRaw('SUM(CASE WHEN mastery_level BETWEEN 1 AND 2 THEN 1 ELSE 0 END) as learning_count')
            ->selectRaw('SUM(CASE WHEN mastery_level >= 3 THEN 1 ELSE 0 END) as mastered_count')
            ->selectRaw('SUM(CASE WHEN next_review_at <= NOW() THEN 1 ELSE 0 END) as due_count')
            ->first();

        return [
            'total' => $stats->total ?? 0,
            'new' => $stats->new_count ?? 0,
            'learning' => $stats->learning_count ?? 0,
            'mastered' => $stats->mastered_count ?? 0,
            'due' => $stats->due_count ?? 0,
        ];
    }

    /**
     * Delete user account and cleanup resources
     *
     * @param User $user
     * @return void
     */
    public function deleteAccount(User $user): void
    {
        DB::transaction(function () use ($user) {
            // Delete avatar if exists
            if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            // Delete user (cascade deletes will handle related records)
            $user->delete();
        });
    }
}
