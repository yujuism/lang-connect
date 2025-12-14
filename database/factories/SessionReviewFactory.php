<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SessionReview>
 */
class SessionReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $overallRating = fake()->numberBetween(3, 5);

        $comments = [
            'Very helpful and patient partner!',
            'Great session, learned a lot about grammar.',
            'Patient and clear explanations.',
            'Really enjoyed practicing pronunciation together.',
            'Wonderful conversation practice!',
            'Very knowledgeable and encouraging.',
        ];

        return [
            'session_id' => \App\Models\PracticeSession::factory(),
            'reviewer_id' => \App\Models\User::factory(),
            'reviewed_user_id' => \App\Models\User::factory(),
            'overall_rating' => $overallRating,
            'helpfulness_rating' => fake()->numberBetween($overallRating - 1, 5),
            'patience_rating' => fake()->numberBetween($overallRating - 1, 5),
            'clarity_rating' => fake()->numberBetween($overallRating - 1, 5),
            'engagement_rating' => fake()->numberBetween($overallRating - 1, 5),
            'comment' => fake()->randomElement($comments),
            'is_public' => fake()->boolean(80),
            'topics_rated_well' => fake()->randomElements(['grammar', 'pronunciation', 'vocabulary', 'conversation'], rand(1, 3)),
        ];
    }
}
