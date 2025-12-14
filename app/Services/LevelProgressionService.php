<?php

namespace App\Services;

use App\Models\UserProgress;

/**
 * LevelProgressionService - Handles level calculations and progression
 */
class LevelProgressionService
{
    /**
     * Level thresholds (hours required for each level)
     */
    private const LEVEL_THRESHOLDS = [
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

    /**
     * Get hours required for a specific level
     *
     * @param int $level
     * @return int
     */
    public function getHoursForLevel(int $level): int
    {
        return self::LEVEL_THRESHOLDS[$level] ?? 250;
    }

    /**
     * Get progress towards next level
     *
     * @param UserProgress $progress
     * @return array
     */
    public function getProgressToNextLevel(UserProgress $progress): array
    {
        $currentLevel = $progress->level;
        $currentHours = $progress->contribution_hours;

        // If max level, return completed
        if ($currentLevel >= 10) {
            return [
                'current_level' => $currentLevel,
                'next_level' => null,
                'current_hours' => $currentHours,
                'hours_for_current' => $this->getHoursForLevel(10),
                'hours_for_next' => null,
                'hours_remaining' => 0,
                'progress_percentage' => 100,
                'is_max_level' => true,
            ];
        }

        $nextLevel = $currentLevel + 1;
        $hoursForCurrent = $this->getHoursForLevel($currentLevel);
        $hoursForNext = $this->getHoursForLevel($nextLevel);
        $hoursInCurrentLevel = max(0, $currentHours - $hoursForCurrent);
        $hoursNeededForLevel = $hoursForNext - $hoursForCurrent;
        $progressPercentage = $hoursNeededForLevel > 0
            ? max(0, min(100, round(($hoursInCurrentLevel / $hoursNeededForLevel) * 100)))
            : 0;

        return [
            'current_level' => $currentLevel,
            'next_level' => $nextLevel,
            'current_hours' => $currentHours,
            'hours_for_current' => $hoursForCurrent,
            'hours_for_next' => $hoursForNext,
            'hours_remaining' => max(0, $hoursForNext - $currentHours),
            'hours_in_current_level' => $hoursInCurrentLevel,
            'hours_needed_for_level' => $hoursNeededForLevel,
            'progress_percentage' => $progressPercentage,
            'is_max_level' => false,
        ];
    }

    /**
     * Get all level milestones with benefits
     *
     * @return array
     */
    public function getLevelMilestones(): array
    {
        return [
            1 => [
                'title' => 'Beginner',
                'hours' => 0,
                'benefits' => ['Access to basic features', 'Can request help'],
                'icon' => '🌱',
                'color' => '#6b7280',
            ],
            2 => [
                'title' => 'Learner',
                'hours' => 5,
                'benefits' => ['Unlock profile customization', 'Can help others'],
                'icon' => '📚',
                'color' => '#10b981',
            ],
            3 => [
                'title' => 'Contributor',
                'hours' => 15,
                'benefits' => ['Priority matching', 'Featured helper badge'],
                'icon' => '⭐',
                'color' => '#3b82f6',
            ],
            4 => [
                'title' => 'Helper',
                'hours' => 30,
                'benefits' => ['Advanced search filters', 'Can create study groups'],
                'icon' => '🤝',
                'color' => '#8b5cf6',
            ],
            5 => [
                'title' => 'Mentor',
                'hours' => 50,
                'benefits' => ['Mentor badge', 'Can host workshops', '10% karma bonus'],
                'icon' => '🎓',
                'color' => '#f59e0b',
            ],
            6 => [
                'title' => 'Expert',
                'hours' => 75,
                'benefits' => ['Expert badge', 'Profile highlights', '15% karma bonus'],
                'icon' => '💎',
                'color' => '#06b6d4',
            ],
            7 => [
                'title' => 'Master',
                'hours' => 100,
                'benefits' => ['Master badge', 'Priority support', '20% karma bonus'],
                'icon' => '👑',
                'color' => '#ec4899',
            ],
            8 => [
                'title' => 'Champion',
                'hours' => 150,
                'benefits' => ['Champion badge', 'Beta features access', '25% karma bonus'],
                'icon' => '🏆',
                'color' => '#f59e0b',
            ],
            9 => [
                'title' => 'Legend',
                'hours' => 200,
                'benefits' => ['Legend badge', 'Custom profile themes', '30% karma bonus'],
                'icon' => '⚡',
                'color' => '#8b5cf6',
            ],
            10 => [
                'title' => 'Grand Master',
                'hours' => 250,
                'benefits' => ['Grand Master badge', 'Hall of Fame', '50% karma bonus', 'Community leadership'],
                'icon' => '🌟',
                'color' => '#dc2626',
            ],
        ];
    }

    /**
     * Get milestone info for a specific level
     *
     * @param int $level
     * @return array|null
     */
    public function getMilestoneForLevel(int $level): ?array
    {
        $milestones = $this->getLevelMilestones();
        return $milestones[$level] ?? null;
    }

    /**
     * Get visual representation of level
     *
     * @param int $level
     * @return string
     */
    public function getLevelBadgeClass(int $level): string
    {
        return match(true) {
            $level >= 10 => 'level-grand-master',
            $level >= 8 => 'level-champion',
            $level >= 6 => 'level-expert',
            $level >= 4 => 'level-helper',
            $level >= 2 => 'level-learner',
            default => 'level-beginner',
        };
    }
}
