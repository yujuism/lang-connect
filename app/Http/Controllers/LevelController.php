<?php

namespace App\Http\Controllers;

use App\Services\LevelProgressionService;
use Illuminate\Support\Facades\Auth;

/**
 * LevelController - Displays level information and milestones
 */
class LevelController extends Controller
{
    public function __construct(
        private LevelProgressionService $levelService
    ) {}

    /**
     * Display all level milestones
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $milestones = $this->levelService->getLevelMilestones();

        $userProgress = null;
        $progressData = null;

        if (Auth::check()) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            if ($user->progress) {
                $userProgress = $user->progress;
                $progressData = $this->levelService->getProgressToNextLevel($userProgress);
            }
        }

        return view('levels.index', compact('milestones', 'userProgress', 'progressData'));
    }
}
