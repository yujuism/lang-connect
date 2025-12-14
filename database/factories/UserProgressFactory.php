<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserProgress>
 */
class UserProgressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $contributionHours = fake()->randomFloat(2, 0, 150);
        $level = min(10, max(1, floor($contributionHours / 15) + 1));

        return [
            'contribution_hours' => $contributionHours,
            'level' => $level,
            'karma_points' => fake()->numberBetween(0, 5000),
            'total_sessions' => fake()->numberBetween(0, 200),
            'members_helped' => fake()->numberBetween(0, 50),
            'sessions_received' => fake()->numberBetween(0, 150),
        ];
    }
}
