<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use Faker\Factory as FakerFactory;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $faker = FakerFactory::create('id_ID');

        $this->command->info('🚀 Starting Enhanced Product Seeding with Faker...');

        if (Category::count() == 0) {
            $this->call(CategorySeeder::class);
        }

        $categories = Category::all();
        $this->command->info("📁 Found {$categories->count()} categories");

        // Create realistic product distribution per category
        foreach ($categories as $category) {
            $productCount = $faker->numberBetween(4, 8); // Random products per category

            $this->command->info("🏷️  Creating {$productCount} products for category: {$category->name}");

            // Create products with different characteristics
            Product::factory()
                ->count($productCount)
                ->forCategory($category->id)
                ->create();
        }

        // Create special product collections using Faker probabilities
        $this->createSpecialProducts($faker);

        // Add some variety with different product states
        $this->createVarietyProducts($faker);

        $totalProducts = Product::count();
        $this->command->info("✅ Successfully created {$totalProducts} products!");

        // Display statistics
        $this->displayStatistics();
    }

    /**
     * Create special products using Faker
     */
    private function createSpecialProducts($faker): void
    {
        $this->command->info('⭐ Creating special product collections...');

        // Bestsellers (high rating, good stock)
        Product::factory()
            ->count($faker->numberBetween(5, 8))
            ->bestseller()
            ->create();

        // Limited Edition products (low stock, higher price)
        Product::factory()
            ->count($faker->numberBetween(2, 4))
            ->limitedEdition()
            ->create();

        // Budget-friendly products
        Product::factory()
            ->count($faker->numberBetween(8, 12))
            ->budget()
            ->create();

        // Premium/Expensive products
        Product::factory()
            ->count($faker->numberBetween(3, 6))
            ->expensive()
            ->create();

        $this->command->info('✨ Special collections created!');
    }

    /**
     * Create variety in products using Faker
     */
    private function createVarietyProducts($faker): void
    {
        $this->command->info('🎲 Adding product variety...');

        // Products with specific stock situations
        Product::factory()
            ->count($faker->numberBetween(3, 5))
            ->outOfStock()
            ->create();

        // High-rated products
        Product::factory()
            ->count($faker->numberBetween(6, 10))
            ->highRated()
            ->create();

        // Add more products to reach target if needed
        $currentCount = Product::count();
        $targetCount = $faker->numberBetween(45, 60); // More than 30, with variety

        if ($currentCount < $targetCount) {
            $needed = $targetCount - $currentCount;
            $this->command->info("📦 Adding {$needed} more products to reach target...");

            Product::factory()
                ->count($needed)
                ->create();
        }
    }

    /**
     * Display statistics using data from the database
     */
    private function displayStatistics(): void
    {
        $this->command->info('📊 Database Statistics:');
        $this->command->line('═══════════════════════════');

        // Total counts
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $this->command->info("📦 Total Products: {$totalProducts}");
        $this->command->info("📁 Total Categories: {$totalCategories}");

        // Products per category
        $this->command->info("\n🏷️  Products per Category:");
        foreach (Category::withCount('products')->get() as $category) {
            $this->command->line("   • {$category->name}: {$category->products_count} products");
        }

        // Price statistics
        $avgPrice = number_format(Product::avg('price'), 0, ',', '.');
        $minPrice = number_format(Product::min('price'), 0, ',', '.');
        $maxPrice = number_format(Product::max('price'), 0, ',', '.');

        $this->command->info("\n💰 Price Statistics:");
        $this->command->line("   • Average Price: Rp {$avgPrice}");
        $this->command->line("   • Lowest Price: Rp {$minPrice}");
        $this->command->line("   • Highest Price: Rp {$maxPrice}");

        // Stock statistics
        $inStock = Product::where('stock', '>', 0)->count();
        $lowStock = Product::whereBetween('stock', [1, 5])->count();
        $outOfStock = Product::where('stock', 0)->count();

        $this->command->info("\n📦 Stock Statistics:");
        $this->command->line("   • In Stock: {$inStock} products");
        $this->command->line("   • Low Stock (1-5): {$lowStock} products");
        $this->command->line("   • Out of Stock: {$outOfStock} products");

        // Rating statistics
        $avgRating = number_format(Product::avg('rating'), 1);
        $highRated = Product::where('rating', '>=', 4.5)->count();

        $this->command->info("\n⭐ Rating Statistics:");
        $this->command->line("   • Average Rating: {$avgRating}/5.0");
        $this->command->line("   • High Rated (4.5+): {$highRated} products");

        $this->command->line('═══════════════════════════');
        $this->command->info('🎉 Enhanced Product Seeding Completed Successfully!');
    }
}
