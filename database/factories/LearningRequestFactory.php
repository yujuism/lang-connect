<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LearningRequest>
 */
class LearningRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $topicCategories = ['grammar', 'vocabulary', 'pronunciation', 'expression', 'conversation', 'other'];
        $proficiencyLevels = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];
        $statuses = ['pending', 'matched', 'completed', 'cancelled'];

        $grammarTopics = ['past_tense', 'present_perfect', 'future_tense', 'conditionals', 'subjunctive'];
        $pronunciationTopics = ['r_sound', 'th_sound', 'intonation', 'stress_patterns', 'vowel_sounds'];
        $vocabularyTopics = ['ordering_food', 'business_terms', 'daily_conversation', 'travel_phrases'];

        $category = fake()->randomElement($topicCategories);
        $topicName = null;

        if ($category === 'grammar') {
            $topicName = fake()->randomElement($grammarTopics);
        } elseif ($category === 'pronunciation') {
            $topicName = fake()->randomElement($pronunciationTopics);
        } elseif ($category === 'vocabulary') {
            $topicName = fake()->randomElement($vocabularyTopics);
        }

        $questions = [
            'How do I use the past tense correctly?',
            'What is the difference between ser and estar?',
            'Can you help me with pronunciation?',
            'I need help with ordering food in restaurants',
            'How do I pronounce the R sound correctly?',
            'What are common business phrases I should know?',
        ];

        return [
            'user_id' => \App\Models\User::factory(),
            'language_id' => fake()->numberBetween(1, 20),
            'topic_category' => $category,
            'topic_name' => $topicName,
            'specific_question' => fake()->randomElement($questions),
            'keywords' => fake()->words(3),
            'proficiency_level' => fake()->randomElement($proficiencyLevels),
            'status' => fake()->randomElement($statuses),
            'matched_with_user_id' => null,
            'matched_at' => null,
        ];
    }
}
