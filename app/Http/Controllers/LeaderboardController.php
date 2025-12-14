<?php

namespace App\Http\Controllers;

use App\Services\LeaderboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * LeaderboardController - Displays community leaderboards
 *
 * Shows rankings for:
 * - Karma Points (overall contribution)
 * - Level Rankings (experience)
 * - Top Helpers (most sessions given)
 * - Monthly Active (current month activity)
 */
class LeaderboardController extends Controller
{
    public function __construct(
        private LeaderboardService $leaderboardService
    ) {}

    /**
     * Display the leaderboards
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $type = $request->query('type', 'karma');

        // Get leaderboard data based on type
        $leaderboard = match ($type) {
            'karma' => $this->leaderboardService->getTopByKarma(50),
            'level' => $this->leaderboardService->getTopByLevel(50),
            'helper' => $this->leaderboardService->getTopHelpers(50),
            'monthly' => $this->leaderboardService->getMostActiveThisMonth(50),
            default => $this->leaderboardService->getTopByKarma(50),
        };

        // Get overall stats
        $stats = $this->leaderboardService->getLeaderboardStats();

        // Get current user's rank if authenticated
        $userRank = null;
        if (Auth::check()) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $userRank = $this->leaderboardService->getUserRank($user, $type);
        }

        return view('leaderboard.index', compact('leaderboard', 'type', 'stats', 'userRank'));
    }
}
