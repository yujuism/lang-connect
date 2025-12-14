<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            LanguageSeeder::class,
            AchievementSeeder::class,
        ]);

        // Ask if user wants demo data
        if ($this->command->confirm('Do you want to seed demo data (users, sessions, requests)?', false)) {
            $this->call(DemoDataSeeder::class);
        }

        $this->command->info('');
        $this->command->info('🎉 Database seeding completed successfully!');
        $this->command->info('📊 Summary:');
        $this->command->info('- Languages: ' . \App\Models\Language::count());
        $this->command->info('- Achievements: ' . \App\Models\Achievement::count());
        $this->command->info('- Users: ' . \App\Models\User::count());
        $this->command->info('- Learning Requests: ' . \App\Models\LearningRequest::count());
        $this->command->info('- Practice Sessions: ' . \App\Models\PracticeSession::count());
    }
}
