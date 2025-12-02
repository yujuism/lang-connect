<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create specific categories
        $categories = [
            [
                'name' => 'Electronics',
                'slug' => 'electronics',
                'description' => 'Electronic devices, smartphones, laptops, and digital accessories for modern lifestyle'
            ],
            [
                'name' => 'Fashion',
                'slug' => 'fashion',
                'description' => 'Trendy clothing, shoes, bags, and fashion accessories for men and women'
            ],
            [
                'name' => 'Home & Garden',
                'slug' => 'home-garden',
                'description' => 'Home appliances, furniture, decorations, and garden tools for comfortable living'
            ],
            [
                'name' => 'Sports & Outdoors',
                'slug' => 'sports-outdoors',
                'description' => 'Sports equipment, outdoor gear, and fitness accessories for active lifestyle'
            ],
            [
                'name' => 'Books & Education',
                'slug' => 'books-education',
                'description' => 'Books, educational materials, and learning resources for knowledge enhancement'
            ],
            [
                'name' => 'Health & Beauty',
                'slug' => 'health-beauty',
                'description' => 'Health products, cosmetics, skincare, and personal care items'
            ],
            [
                'name' => 'Automotive',
                'slug' => 'automotive',
                'description' => 'Car accessories, automotive tools, and vehicle maintenance products'
            ]
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        $this->command->info('Categories seeded successfully!');
    }
}
