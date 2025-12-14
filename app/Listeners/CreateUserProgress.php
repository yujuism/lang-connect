<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use App\Models\UserProgress;

class CreateUserProgress
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        // Create initial user progress record with default values
        UserProgress::create([
            'user_id' => $event->user->id,
            'contribution_hours' => 0,
            'level' => 1,
            'karma_points' => 0,
            'total_sessions' => 0,
            'members_helped' => 0,
            'sessions_received' => 0,
        ]);
    }
}
