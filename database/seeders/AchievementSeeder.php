<?php

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        $achievements = [
            // Helper Achievements
            [
                'name' => 'First Guide',
                'description' => 'Help someone for the first time',
                'icon' => '🌱',
                'category' => 'helper',
                'rarity' => 'common',
                'requirement_type' => 'sessions',
                'requirement_value' => '1',
            ],
            [
                'name' => 'Helpful Friend',
                'description' => 'Complete 10 helping sessions',
                'icon' => '🤝',
                'category' => 'helper',
                'rarity' => 'uncommon',
                'requirement_type' => 'sessions',
                'requirement_value' => '10',
            ],
            [
                'name' => 'Community Guide',
                'description' => 'Help 25 different members',
                'icon' => '⭐',
                'category' => 'helper',
                'rarity' => 'rare',
                'requirement_type' => 'members_helped',
                'requirement_value' => '25',
            ],
            [
                'name' => 'Language Mentor',
                'description' => 'Contribute 50 hours helping others',
                'icon' => '👨‍🏫',
                'category' => 'helper',
                'rarity' => 'epic',
                'requirement_type' => 'hours',
                'requirement_value' => '50',
            ],
            [
                'name' => 'Master Guide',
                'description' => 'Contribute 100 hours helping others',
                'icon' => '🏆',
                'category' => 'helper',
                'rarity' => 'legendary',
                'requirement_type' => 'hours',
                'requirement_value' => '100',
            ],

            // Streak Achievements
            [
                'name' => 'Week Warrior',
                'description' => 'Practice 7 days in a row',
                'icon' => '🔥',
                'category' => 'streak',
                'rarity' => 'uncommon',
                'requirement_type' => 'streak',
                'requirement_value' => '7',
            ],
            [
                'name' => 'Dedication Champion',
                'description' => 'Practice 30 days in a row',
                'icon' => '💪',
                'category' => 'streak',
                'rarity' => 'rare',
                'requirement_type' => 'streak',
                'requirement_value' => '30',
            ],
            [
                'name' => 'Unstoppable',
                'description' => 'Practice 100 days in a row',
                'icon' => '🚀',
                'category' => 'streak',
                'rarity' => 'legendary',
                'requirement_type' => 'streak',
                'requirement_value' => '100',
            ],

            // Mastery Achievements
            [
                'name' => 'Grammar Guru',
                'description' => 'Master 10 grammar topics',
                'icon' => '📚',
                'category' => 'mastery',
                'rarity' => 'rare',
                'requirement_type' => 'grammar_mastery',
                'requirement_value' => '10',
            ],
            [
                'name' => 'Pronunciation Pro',
                'description' => 'Master 5 pronunciation topics',
                'icon' => '🗣️',
                'category' => 'mastery',
                'rarity' => 'rare',
                'requirement_type' => 'pronunciation_mastery',
                'requirement_value' => '5',
            ],
            [
                'name' => 'Vocabulary Virtuoso',
                'description' => 'Master 15 vocabulary topics',
                'icon' => '💬',
                'category' => 'mastery',
                'rarity' => 'epic',
                'requirement_type' => 'vocabulary_mastery',
                'requirement_value' => '15',
            ],

            // Community Achievements
            [
                'name' => 'Five Star Partner',
                'description' => 'Maintain 5.0 average rating over 20 sessions',
                'icon' => '⭐⭐⭐⭐⭐',
                'category' => 'community',
                'rarity' => 'epic',
                'requirement_type' => 'rating',
                'requirement_value' => '5.0',
            ],
            [
                'name' => 'Workshop Host',
                'description' => 'Host your first group workshop',
                'icon' => '🎪',
                'category' => 'community',
                'rarity' => 'uncommon',
                'requirement_type' => 'workshops',
                'requirement_value' => '1',
            ],
            [
                'name' => 'Popular Host',
                'description' => 'Host 10 workshops with 10+ participants',
                'icon' => '🎭',
                'category' => 'community',
                'rarity' => 'rare',
                'requirement_type' => 'large_workshops',
                'requirement_value' => '10',
            ],

            // Special Achievements
            [
                'name' => 'Early Adopter',
                'description' => 'Join during platform beta',
                'icon' => '🌟',
                'category' => 'special',
                'rarity' => 'mythical',
                'requirement_type' => 'special',
                'requirement_value' => 'beta',
            ],
            [
                'name' => 'Polyglot',
                'description' => 'Help others in 5 different languages',
                'icon' => '🌍',
                'category' => 'special',
                'rarity' => 'legendary',
                'requirement_type' => 'languages',
                'requirement_value' => '5',
            ],
            [
                'name' => 'Community Pillar',
                'description' => 'Reach Level 10 contribution',
                'icon' => '🏛️',
                'category' => 'special',
                'rarity' => 'mythical',
                'requirement_type' => 'level',
                'requirement_value' => '10',
            ],
        ];

        foreach ($achievements as $achievement) {
            Achievement::create($achievement);
        }
    }
}
