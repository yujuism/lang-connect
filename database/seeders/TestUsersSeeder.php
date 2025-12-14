<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserProgress;
use App\Models\UserLanguage;
use App\Models\Language;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Test User 1: Beginner Spanish Learner
        $learner1 = User::create([
            'name' => 'Alice Johnson',
            'email' => 'alice@test.com',
            'password' => Hash::make('password'),
        ]);

        UserProgress::create([
            'user_id' => $learner1->id,
            'contribution_hours' => 0,
            'level' => 1,
            'karma_points' => 0,
            'total_sessions' => 0,
            'members_helped' => 0,
            'sessions_received' => 0,
        ]);

        // Alice is native English, learning Spanish
        UserLanguage::create([
            'user_id' => $learner1->id,
            'language_id' => Language::where('code', 'en')->first()->id,
            'proficiency_level' => 'native',
            'is_native' => true,
            'is_learning' => false,
        ]);

        UserLanguage::create([
            'user_id' => $learner1->id,
            'language_id' => Language::where('code', 'es')->first()->id,
            'proficiency_level' => 'A2',
            'is_native' => false,
            'is_learning' => true,
        ]);

        // Test User 2: Experienced Spanish Speaker (Good Helper)
        $helper1 = User::create([
            'name' => 'Carlos Garcia',
            'email' => 'carlos@test.com',
            'password' => Hash::make('password'),
        ]);

        UserProgress::create([
            'user_id' => $helper1->id,
            'contribution_hours' => 45.5,
            'level' => 4, // Level 4 (30+ hours)
            'karma_points' => 250,
            'total_sessions' => 30,
            'members_helped' => 15,
            'sessions_received' => 12,
        ]);

        // Carlos is native Spanish, fluent English
        UserLanguage::create([
            'user_id' => $helper1->id,
            'language_id' => Language::where('code', 'es')->first()->id,
            'proficiency_level' => 'native',
            'is_native' => true,
            'is_learning' => false,
        ]);

        UserLanguage::create([
            'user_id' => $helper1->id,
            'language_id' => Language::where('code', 'en')->first()->id,
            'proficiency_level' => 'C2',
            'is_native' => false,
            'is_learning' => false,
        ]);

        // Test User 3: French Learner
        $learner2 = User::create([
            'name' => 'Bob Smith',
            'email' => 'bob@test.com',
            'password' => Hash::make('password'),
        ]);

        UserProgress::create([
            'user_id' => $learner2->id,
            'contribution_hours' => 8,
            'level' => 2,
            'karma_points' => 50,
            'total_sessions' => 5,
            'members_helped' => 2,
            'sessions_received' => 3,
        ]);

        // Bob is native English, learning French
        UserLanguage::create([
            'user_id' => $learner2->id,
            'language_id' => Language::where('code', 'en')->first()->id,
            'proficiency_level' => 'native',
            'is_native' => true,
            'is_learning' => false,
        ]);

        UserLanguage::create([
            'user_id' => $learner2->id,
            'language_id' => Language::where('code', 'fr')->first()->id,
            'proficiency_level' => 'B1',
            'is_native' => false,
            'is_learning' => true,
        ]);

        // Test User 4: High-Level French Helper
        $helper2 = User::create([
            'name' => 'Marie Dubois',
            'email' => 'marie@test.com',
            'password' => Hash::make('password'),
        ]);

        UserProgress::create([
            'user_id' => $helper2->id,
            'contribution_hours' => 120,
            'level' => 7, // Level 7 (100+ hours)
            'karma_points' => 850,
            'total_sessions' => 80,
            'members_helped' => 45,
            'sessions_received' => 35,
        ]);

        // Marie is native French, fluent English
        UserLanguage::create([
            'user_id' => $helper2->id,
            'language_id' => Language::where('code', 'fr')->first()->id,
            'proficiency_level' => 'native',
            'is_native' => true,
            'is_learning' => false,
        ]);

        UserLanguage::create([
            'user_id' => $helper2->id,
            'language_id' => Language::where('code', 'en')->first()->id,
            'proficiency_level' => 'C1',
            'is_native' => false,
            'is_learning' => false,
        ]);

        // Test User 5: New User (No sessions yet)
        $newUser = User::create([
            'name' => 'Tom Wilson',
            'email' => 'tom@test.com',
            'password' => Hash::make('password'),
        ]);

        UserProgress::create([
            'user_id' => $newUser->id,
            'contribution_hours' => 0,
            'level' => 1,
            'karma_points' => 0,
            'total_sessions' => 0,
            'members_helped' => 0,
            'sessions_received' => 0,
        ]);

        // Tom is native English, learning Japanese
        UserLanguage::create([
            'user_id' => $newUser->id,
            'language_id' => Language::where('code', 'en')->first()->id,
            'proficiency_level' => 'native',
            'is_native' => true,
            'is_learning' => false,
        ]);

        UserLanguage::create([
            'user_id' => $newUser->id,
            'language_id' => Language::where('code', 'ja')->first()->id,
            'proficiency_level' => 'A1',
            'is_native' => false,
            'is_learning' => true,
        ]);

        // Test User 6: Admin/Super Helper (Multi-lingual)
        $admin = User::create([
            'name' => 'Sofia Rodriguez',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
        ]);

        UserProgress::create([
            'user_id' => $admin->id,
            'contribution_hours' => 250,
            'level' => 10, // Max level
            'karma_points' => 2500,
            'total_sessions' => 150,
            'members_helped' => 100,
            'sessions_received' => 50,
        ]);

        // Sofia speaks Spanish, English, French
        UserLanguage::create([
            'user_id' => $admin->id,
            'language_id' => Language::where('code', 'es')->first()->id,
            'proficiency_level' => 'native',
            'is_native' => true,
            'is_learning' => false,
        ]);

        UserLanguage::create([
            'user_id' => $admin->id,
            'language_id' => Language::where('code', 'en')->first()->id,
            'proficiency_level' => 'C2',
            'is_native' => false,
            'is_learning' => false,
        ]);

        UserLanguage::create([
            'user_id' => $admin->id,
            'language_id' => Language::where('code', 'fr')->first()->id,
            'proficiency_level' => 'C1',
            'is_native' => false,
            'is_learning' => false,
        ]);

        $this->command->info('✅ Created 6 test users successfully!');
    }
}
