<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Services\AchievementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AchievementController extends Controller
{
    /**
     * Display all achievements with user progress
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $achievementService = new AchievementService();

        // Get all achievements grouped by category
        $achievements = Achievement::orderBy('category')
            ->orderByRaw("FIELD(rarity, 'common', 'uncommon', 'rare', 'epic', 'legendary', 'mythical')")
            ->get()
            ->groupBy('category');

        // Get user's unlocked achievements
        $unlockedIds = $user->achievements()->pluck('achievement_id')->toArray();

        // Calculate progress for each achievement
        $achievementsWithProgress = [];
        foreach ($achievements as $category => $categoryAchievements) {
            $achievementsWithProgress[$category] = $categoryAchievements->map(function ($achievement) use ($user, $unlockedIds, $achievementService) {
                $isUnlocked = in_array($achievement->id, $unlockedIds);
                $progress = $isUnlocked ? ['current' => 100, 'required' => 100, 'percentage' => 100]
                    : $achievementService->getAchievementProgress($user, $achievement);

                return [
                    'achievement' => $achievement,
                    'unlocked' => $isUnlocked,
                    'progress' => $progress,
                ];
            });
        }

        // Calculate total achievements stats
        $totalAchievements = Achievement::count();
        $unlockedCount = count($unlockedIds);
        $completionPercentage = $totalAchievements > 0 ? round(($unlockedCount / $totalAchievements) * 100, 1) : 0;

        return view('achievements.index', compact(
            'achievementsWithProgress',
            'totalAchievements',
            'unlockedCount',
            'completionPercentage'
        ));
    }
}
