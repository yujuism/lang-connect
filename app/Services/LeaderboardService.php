<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserProgress;
use Illuminate\Support\Collection;

/**
 * LeaderboardService - Handles leaderboard rankings and statistics
 *
 * Provides multiple leaderboard types:
 * - Karma Points (overall contribution)
 * - Level Rankings (experience-based)
 * - Helper Rankings (most sessions helped)
 * - Monthly Active Users (current month sessions)
 */
class LeaderboardService
{
    /**
     * Get top users by karma points
     *
     * @param int $limit
     * @return Collection
     */
    public function getTopByKarma(int $limit = 50): Collection
    {
        return UserProgress::with('user')
            ->orderBy('karma_points', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($progress, $index) {
                return [
                    'rank' => $index + 1,
                    'user' => $progress->user,
                    'value' => $progress->karma_points,
                    'level' => $progress->level,
                    'badge' => $this->getRankBadge($index + 1),
                ];
            });
    }

    /**
     * Get top users by level
     *
     * @param int $limit
     * @return Collection
     */
    public function getTopByLevel(int $limit = 50): Collection
    {
        return UserProgress::with('user')
            ->orderBy('level', 'desc')
            ->orderBy('karma_points', 'desc') // Tiebreaker
            ->limit($limit)
            ->get()
            ->map(function ($progress, $index) {
                return [
                    'rank' => $index + 1,
                    'user' => $progress->user,
                    'value' => $progress->level,
                    'karma' => $progress->karma_points,
                    'badge' => $this->getRankBadge($index + 1),
                ];
            });
    }

    /**
     * Get top helpers by number of sessions helped
     *
     * @param int $limit
     * @return Collection
     */
    public function getTopHelpers(int $limit = 50): Collection
    {
        return UserProgress::with('user')
            ->get()
            ->map(function ($progress) {
                // Calculate sessions given (total - received)
                $progress->sessions_given = max(0, $progress->total_sessions - $progress->sessions_received);
                return $progress;
            })
            ->sortByDesc(function ($progress) {
                return $progress->sessions_given * 1000000 + $progress->karma_points;
            })
            ->take($limit)
            ->values()
            ->map(function ($progress, $index) {
                return [
                    'rank' => $index + 1,
                    'user' => $progress->user,
                    'value' => $progress->sessions_given,
                    'hours' => round($progress->contribution_hours, 1),
                    'members_helped' => $progress->members_helped,
                    'badge' => $this->getRankBadge($index + 1),
                ];
            });
    }

    /**
     * Get most active users this month
     *
     * @param int $limit
     * @return Collection
     */
    public function getMostActiveThisMonth(int $limit = 50): Collection
    {
        $startOfMonth = now()->startOfMonth();

        return User::withCount([
            'sessionsAsUser1 as sessions_this_month' => function ($query) use ($startOfMonth) {
                $query->where('created_at', '>=', $startOfMonth)
                      ->where('status', 'completed');
            }
        ])
        ->withCount([
            'sessionsAsUser2 as sessions_as_learner' => function ($query) use ($startOfMonth) {
                $query->where('created_at', '>=', $startOfMonth)
                      ->where('status', 'completed');
            }
        ])
        ->with('progress')
        ->get()
        ->map(function ($user) {
            $user->total_sessions_this_month = $user->sessions_this_month + $user->sessions_as_learner;
            return $user;
        })
        ->filter(function ($user) {
            return $user->total_sessions_this_month > 0;
        })
        ->sortByDesc('total_sessions_this_month')
        ->take($limit)
        ->values()
        ->map(function ($user, $index) {
            return [
                'rank' => $index + 1,
                'user' => $user,
                'value' => $user->total_sessions_this_month,
                'level' => $user->progress?->level ?? 1,
                'badge' => $this->getRankBadge($index + 1),
            ];
        });
    }

    /**
     * Get user's rank in specific leaderboard
     *
     * @param User $user
     * @param string $type (karma, level, helper, monthly)
     * @return array|null
     */
    public function getUserRank(User $user, string $type): ?array
    {
        $leaderboard = match ($type) {
            'karma' => $this->getTopByKarma(1000),
            'level' => $this->getTopByLevel(1000),
            'helper' => $this->getTopHelpers(1000),
            'monthly' => $this->getMostActiveThisMonth(1000),
            default => collect([]),
        };

        $userRank = $leaderboard->firstWhere('user.id', $user->id);

        return $userRank ? [
            'rank' => $userRank['rank'],
            'value' => $userRank['value'],
            'total_users' => $leaderboard->count(),
        ] : null;
    }

    /**
     * Get rank badge/medal for top positions
     *
     * @param int $rank
     * @return string|null
     */
    private function getRankBadge(int $rank): ?string
    {
        return match ($rank) {
            1 => '🥇',
            2 => '🥈',
            3 => '🥉',
            default => null,
        };
    }

    /**
     * Get comprehensive leaderboard statistics
     *
     * @return array
     */
    public function getLeaderboardStats(): array
    {
        return [
            'total_users' => User::count(),
            'total_karma_distributed' => UserProgress::sum('karma_points'),
            'total_sessions' => UserProgress::sum('total_sessions'),
            'total_hours' => round(UserProgress::sum('contribution_hours'), 1),
            'average_level' => round(UserProgress::avg('level'), 1),
            'highest_level' => UserProgress::max('level'),
        ];
    }
}
