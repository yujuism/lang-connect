<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PracticeSession>
 */
class PracticeSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sessionTypes = ['random', 'scheduled', 'workshop'];
        $statuses = ['scheduled', 'in_progress', 'completed', 'cancelled'];
        $topics = ['Past Tense Practice', 'Pronunciation Training', 'Business Conversation', 'Daily Life Chat', 'Grammar Review'];

        $startedAt = fake()->dateTimeBetween('-30 days', 'now');
        $completedAt = fake()->dateTimeBetween($startedAt, 'now');
        $duration = rand(15, 120);

        return [
            'request_id' => null,
            'user1_id' => \App\Models\User::factory(),
            'user2_id' => \App\Models\User::factory(),
            'language_id' => fake()->numberBetween(1, 20),
            'topic' => fake()->randomElement($topics),
            'session_type' => fake()->randomElement($sessionTypes),
            'scheduled_at' => fake()->dateTimeBetween('-7 days', '+7 days'),
            'duration_minutes' => $duration,
            'status' => fake()->randomElement($statuses),
            'started_at' => $startedAt,
            'completed_at' => $completedAt,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
