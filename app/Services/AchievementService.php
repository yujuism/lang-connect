<?php

namespace App\Services;

use App\Models\User;
use App\Models\Achievement;
use App\Models\UserAchievement;
use Illuminate\Support\Facades\DB;

class AchievementService
{
    /**
     * Check and award all eligible achievements for a user
     */
    public function checkAndAwardAchievements(User $user): array
    {
        $newAchievements = [];

        // Get all achievements the user doesn't have yet
        $unlockedAchievementIds = $user->achievements()->pluck('achievement_id')->toArray();
        $achievements = Achievement::whereNotIn('id', $unlockedAchievementIds)->get();

        foreach ($achievements as $achievement) {
            if ($this->checkAchievementRequirement($user, $achievement)) {
                $this->awardAchievement($user, $achievement);
                $newAchievements[] = $achievement;
            }
        }

        return $newAchievements;
    }

    /**
     * Check if user meets achievement requirement
     */
    protected function checkAchievementRequirement(User $user, Achievement $achievement): bool
    {
        $progress = $user->progress;
        if (!$progress) {
            return false;
        }

        return match($achievement->requirement_type) {
            // Session-based achievements
            'sessions' => $progress->total_sessions >= (int)$achievement->requirement_value,

            // Members helped
            'members_helped' => $progress->members_helped >= (int)$achievement->requirement_value,

            // Hours contributed
            'hours' => $progress->contribution_hours >= (float)$achievement->requirement_value,

            // Level-based
            'level' => $progress->level >= (int)$achievement->requirement_value,

            // Rating-based (average rating across all reviews)
            'rating' => $this->checkRatingRequirement($user, (float)$achievement->requirement_value),

            // Streak-based
            'streak' => $this->checkStreakRequirement($user, (int)$achievement->requirement_value),

            // Language diversity
            'languages' => $this->checkLanguageDiversity($user, (int)$achievement->requirement_value),

            // Mastery-based
            'grammar_mastery' => $this->checkTopicMastery($user, 'grammar', (int)$achievement->requirement_value),
            'pronunciation_mastery' => $this->checkTopicMastery($user, 'pronunciation', (int)$achievement->requirement_value),
            'vocabulary_mastery' => $this->checkTopicMastery($user, 'vocabulary', (int)$achievement->requirement_value),

            // Special achievements
            'special' => $this->checkSpecialRequirement($user, $achievement->requirement_value),

            default => false,
        };
    }

    /**
     * Award achievement to user
     */
    protected function awardAchievement(User $user, Achievement $achievement): void
    {
        UserAchievement::create([
            'user_id' => $user->id,
            'achievement_id' => $achievement->id,
            'unlocked_at' => now(),
        ]);

        // Award karma bonus based on rarity
        $karmaBonus = match($achievement->rarity) {
            'common' => 10,
            'uncommon' => 25,
            'rare' => 50,
            'epic' => 100,
            'legendary' => 200,
            'mythical' => 500,
            default => 10,
        };

        $user->progress->increment('karma_points', $karmaBonus);
    }

    /**
     * Check rating requirement
     */
    protected function checkRatingRequirement(User $user, float $requiredRating): bool
    {
        $avgRating = $user->reviewsReceived()
            ->where('is_public', true)
            ->selectRaw('AVG(overall_rating) as avg_rating, COUNT(*) as count')
            ->first();

        // Need at least 20 reviews with perfect 5.0 average
        return $avgRating && $avgRating->count >= 20 && $avgRating->avg_rating >= $requiredRating;
    }

    /**
     * Check streak requirement
     */
    protected function checkStreakRequirement(User $user, int $requiredDays): bool
    {
        // Get all session dates in descending order
        $sessionDates = DB::table('practice_sessions')
            ->where(function($query) use ($user) {
                $query->where('user1_id', $user->id)
                      ->orWhere('user2_id', $user->id);
            })
            ->where('status', 'completed')
            ->select(DB::raw('DATE(completed_at) as session_date'))
            ->distinct()
            ->orderBy('session_date', 'desc')
            ->pluck('session_date')
            ->toArray();

        if (empty($sessionDates)) {
            return false;
        }

        // Check for consecutive days
        $streak = 1;
        $today = now()->startOfDay();

        for ($i = 0; $i < count($sessionDates) - 1; $i++) {
            $currentDate = \Carbon\Carbon::parse($sessionDates[$i]);
            $nextDate = \Carbon\Carbon::parse($sessionDates[$i + 1]);

            // Check if dates are consecutive
            if ($currentDate->diffInDays($nextDate) === 1) {
                $streak++;
                if ($streak >= $requiredDays) {
                    return true;
                }
            } else {
                // Streak broken, reset
                $streak = 1;
            }
        }

        return $streak >= $requiredDays;
    }

    /**
     * Check language diversity (helped in X different languages)
     */
    protected function checkLanguageDiversity(User $user, int $requiredLanguages): bool
    {
        $languageCount = DB::table('practice_sessions')
            ->where('user2_id', $user->id) // User as helper
            ->where('status', 'completed')
            ->distinct('language_id')
            ->count('language_id');

        return $languageCount >= $requiredLanguages;
    }

    /**
     * Check topic mastery
     */
    protected function checkTopicMastery(User $user, string $topic, int $requiredCount): bool
    {
        $masteryCount = $user->topicMasteries()
            ->where('category', $topic)
            ->where('mastery_level', '>=', 80) // 80% mastery considered "mastered"
            ->count();

        return $masteryCount >= $requiredCount;
    }

    /**
     * Check special requirements
     */
    protected function checkSpecialRequirement(User $user, string $requirement): bool
    {
        return match($requirement) {
            'beta' => $user->created_at < now()->subMonths(3), // Beta = first 3 months
            default => false,
        };
    }

    /**
     * Get user's achievement progress for a specific achievement
     */
    public function getAchievementProgress(User $user, Achievement $achievement): array
    {
        $progress = $user->progress;
        if (!$progress) {
            return ['current' => 0, 'required' => (int)$achievement->requirement_value, 'percentage' => 0];
        }

        $current = match($achievement->requirement_type) {
            'sessions' => $progress->total_sessions,
            'members_helped' => $progress->members_helped,
            'hours' => $progress->contribution_hours,
            'level' => $progress->level,
            'rating' => $this->getCurrentRating($user),
            'streak' => $this->getCurrentStreak($user),
            'languages' => $this->getCurrentLanguageDiversity($user),
            'grammar_mastery' => $this->getCurrentMasteryCount($user, 'grammar'),
            'pronunciation_mastery' => $this->getCurrentMasteryCount($user, 'pronunciation'),
            'vocabulary_mastery' => $this->getCurrentMasteryCount($user, 'vocabulary'),
            default => 0,
        };

        $required = is_numeric($achievement->requirement_value)
            ? (float)$achievement->requirement_value
            : 1;

        $percentage = $required > 0 ? min(100, ($current / $required) * 100) : 0;

        return [
            'current' => $current,
            'required' => $required,
            'percentage' => round($percentage, 1),
        ];
    }

    protected function getCurrentRating(User $user): float
    {
        $avgRating = $user->reviewsReceived()
            ->where('is_public', true)
            ->avg('overall_rating');

        return $avgRating ? round($avgRating, 2) : 0;
    }

    protected function getCurrentStreak(User $user): int
    {
        $sessionDates = DB::table('practice_sessions')
            ->where(function($query) use ($user) {
                $query->where('user1_id', $user->id)
                      ->orWhere('user2_id', $user->id);
            })
            ->where('status', 'completed')
            ->select(DB::raw('DATE(completed_at) as session_date'))
            ->distinct()
            ->orderBy('session_date', 'desc')
            ->pluck('session_date')
            ->toArray();

        if (empty($sessionDates)) {
            return 0;
        }

        $streak = 1;
        for ($i = 0; $i < count($sessionDates) - 1; $i++) {
            $currentDate = \Carbon\Carbon::parse($sessionDates[$i]);
            $nextDate = \Carbon\Carbon::parse($sessionDates[$i + 1]);

            if ($currentDate->diffInDays($nextDate) === 1) {
                $streak++;
            } else {
                break;
            }
        }

        return $streak;
    }

    protected function getCurrentLanguageDiversity(User $user): int
    {
        return DB::table('practice_sessions')
            ->where('user2_id', $user->id)
            ->where('status', 'completed')
            ->distinct('language_id')
            ->count('language_id');
    }

    protected function getCurrentMasteryCount(User $user, string $topic): int
    {
        return $user->topicMasteries()
            ->where('category', $topic)
            ->where('mastery_level', '>=', 80)
            ->count();
    }
}
