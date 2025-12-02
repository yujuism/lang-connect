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
            CategorySeeder::class,
            ProductSeeder::class,
        ]);

        $this->command->info('🎉 Database seeding completed successfully!');
        $this->command->info('📊 Summary:');
        $this->command->info('- Categories: ' . \App\Models\Category::count());
        $this->command->info('- Products: ' . \App\Models\Product::count());
    }
}
