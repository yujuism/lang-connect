<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserProgress;
use App\Models\UserLanguage;
use App\Models\LearningRequest;
use App\Models\PracticeSession;
use App\Models\Language;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating demo users...');

        // Create 20 demo users with progress
        $users = User::factory(20)->create()->each(function ($user) {
            // Create user progress
            UserProgress::factory()->create([
                'user_id' => $user->id,
            ]);

            // Assign 1-3 languages to each user
            $numLanguages = rand(1, 3);
            $languageIds = Language::inRandomOrder()->take($numLanguages)->pluck('id');

            foreach ($languageIds as $languageId) {
                $proficiencyLevels = ['native', 'C2', 'C1', 'B2', 'B1', 'A2', 'A1'];
                $isNative = rand(0, 1) === 1;

                UserLanguage::create([
                    'user_id' => $user->id,
                    'language_id' => $languageId,
                    'proficiency_level' => $isNative ? 'native' : fake()->randomElement(['A1', 'A2', 'B1', 'B2', 'C1', 'C2']),
                    'is_native' => $isNative,
                    'is_learning' => !$isNative,
                ]);
            }
        });

        $this->command->info('Created ' . $users->count() . ' demo users');

        // Create learning requests
        $this->command->info('Creating learning requests...');
        $existingUsers = User::all();

        foreach ($existingUsers->random(15) as $user) {
            // Get languages the user is learning
            $learningLanguages = $user->userLanguages()->where('is_learning', true)->get();

            if ($learningLanguages->count() > 0) {
                $language = $learningLanguages->random();

                LearningRequest::factory()->create([
                    'user_id' => $user->id,
                    'language_id' => $language->language_id,
                    'status' => 'pending',
                ]);
            }
        }

        $this->command->info('Created ' . LearningRequest::count() . ' learning requests');

        // Create practice sessions
        $this->command->info('Creating practice sessions...');

        for ($i = 0; $i < 30; $i++) {
            $user1 = $existingUsers->random();
            $user2 = $existingUsers->where('id', '!=', $user1->id)->random();

            // Find common languages they could practice
            $user1Languages = $user1->userLanguages()->pluck('language_id');
            $user2Languages = $user2->userLanguages()->pluck('language_id');
            $commonLanguages = $user1Languages->intersect($user2Languages);

            if ($commonLanguages->count() > 0) {
                PracticeSession::factory()->create([
                    'user1_id' => $user1->id,
                    'user2_id' => $user2->id,
                    'language_id' => $commonLanguages->random(),
                ]);
            }
        }

        $this->command->info('Created ' . PracticeSession::count() . ' practice sessions');

        $this->command->info('');
        $this->command->info('Demo data seeding completed!');
        $this->command->info('Summary:');
        $this->command->info('- Users: ' . User::count());
        $this->command->info('- Learning Requests: ' . LearningRequest::count());
        $this->command->info('- Practice Sessions: ' . PracticeSession::count());
    }
}
