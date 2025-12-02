<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Define the model's default state using extensive Faker features.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Use Faker to generate dynamic category names
        $categoryPrefixes = [
            $this->faker->randomElement(['Modern', 'Premium', 'Professional', 'Smart', 'Digital', 'Eco', 'Luxury', 'Advanced']),
            $this->faker->randomElement(['Top', 'Best', 'Popular', 'Trending', 'Latest', 'New', 'Featured', 'Special'])
        ];

        $categoryTypes = [
            // Electronics categories with Faker variations
            $this->faker->randomElement(['Electronics', 'Technology', 'Digital Devices', 'Gadgets', 'Tech Solutions']),
            $this->faker->randomElement(['Mobile & Tablets', 'Smartphones & Accessories', 'Communication Devices']),
            $this->faker->randomElement(['Computers & Laptops', 'Computing Solutions', 'PC & Accessories']),
            $this->faker->randomElement(['Audio & Video', 'Entertainment Systems', 'Sound & Vision']),

            // Fashion categories
            $this->faker->randomElement(['Fashion & Style', 'Clothing & Apparel', 'Fashion Trends', 'Style & Beauty']),
            $this->faker->randomElement(['Men\'s Fashion', 'Pria Collection', 'Gentleman Style']),
            $this->faker->randomElement(['Women\'s Fashion', 'Wanita Collection', 'Ladies Style']),
            $this->faker->randomElement(['Footwear', 'Shoes & Sandals', 'Alas Kaki']),
            $this->faker->randomElement(['Bags & Accessories', 'Tas & Aksesoris', 'Fashion Accessories']),

            // Home categories
            $this->faker->randomElement(['Home & Living', 'Rumah & Taman', 'Home Solutions', 'Living Space']),
            $this->faker->randomElement(['Kitchen & Dining', 'Dapur & Makan', 'Culinary Tools']),
            $this->faker->randomElement(['Furniture', 'Perabotan', 'Home Furniture', 'Interior Design']),
            $this->faker->randomElement(['Home Appliances', 'Elektronik Rumah', 'Smart Home']),

            // Sports categories
            $this->faker->randomElement(['Sports & Outdoors', 'Olahraga & Rekreasi', 'Fitness & Sports']),
            $this->faker->randomElement(['Fitness Equipment', 'Alat Fitness', 'Gym & Training']),
            $this->faker->randomElement(['Outdoor Activities', 'Aktivitas Luar', 'Adventure Gear']),

            // Books & Education
            $this->faker->randomElement(['Books & Education', 'Buku & Pendidikan', 'Learning Resources']),
            $this->faker->randomElement(['Children\'s Books', 'Buku Anak', 'Kids Education']),
            $this->faker->randomElement(['Professional Books', 'Buku Profesional', 'Career Development']),

            // Health & Beauty
            $this->faker->randomElement(['Health & Beauty', 'Kesehatan & Kecantikan', 'Wellness & Care']),
            $this->faker->randomElement(['Personal Care', 'Perawatan Pribadi', 'Beauty Products']),

            // Automotive
            $this->faker->randomElement(['Automotive', 'Otomotif', 'Car Accessories', 'Vehicle Parts']),

            // Food & Beverage
            $this->faker->randomElement(['Food & Beverage', 'Makanan & Minuman', 'Culinary Delights']),
            $this->faker->randomElement(['Snacks & Treats', 'Camilan & Makanan Ringan', 'Gourmet Food']),

            // Baby & Kids
            $this->faker->randomElement(['Baby & Kids', 'Bayi & Anak', 'Children Products']),

            // Pet Supplies
            $this->faker->randomElement(['Pet Supplies', 'Perlengkapan Hewan', 'Pet Care']),

            // Art & Crafts
            $this->faker->randomElement(['Art & Crafts', 'Seni & Kerajinan', 'Creative Supplies']),

            // Travel & Luggage
            $this->faker->randomElement(['Travel & Luggage', 'Travel & Koper', 'Travel Essentials'])
        ];

        $baseCategory = $this->faker->randomElement($categoryTypes);

        // Sometimes add prefix for variety
        $categoryName = $this->faker->boolean(30) ?
            $this->faker->randomElement($categoryPrefixes) . ' ' . $baseCategory :
            $baseCategory;

        // Generate compelling descriptions using Faker
        $descriptions = $this->generateCategoryDescription($categoryName);

        return [
            'name' => $categoryName,
            'slug' => Str::slug($categoryName) . '-' . $this->faker->unique()->randomNumber(3),
            'description' => $descriptions,
        ];
    }

    /**
     * Generate category description using Faker
     */
    private function generateCategoryDescription(string $categoryName): string
    {
        $descriptors = [
            // Quality descriptors
            $this->faker->randomElement([
                'Premium quality products',
                'High-grade merchandise',
                'Top-tier items',
                'Excellence in every product',
                'Superior quality guaranteed'
            ]),

            // Target audience
            $this->faker->randomElement([
                'perfect for modern lifestyle',
                'ideal for professionals',
                'designed for everyday use',
                'crafted for discerning customers',
                'suitable for all ages'
            ]),

            // Benefits
            $this->faker->randomElement([
                'Competitive prices with best value',
                'Wide selection to choose from',
                'Latest trends and innovations',
                'Trusted brands and reliable quality',
                'Customer satisfaction guaranteed'
            ]),

            // Call to action
            $this->faker->randomElement([
                'Shop now for the best deals',
                'Discover amazing products today',
                'Find exactly what you need',
                'Explore our comprehensive collection',
                'Get the best products at great prices'
            ])
        ];

        // Additional category-specific descriptions
        $specificDescriptors = $this->getCategorySpecificDescriptors($categoryName);
        $allDescriptors = array_merge($descriptors, $specificDescriptors);

        $selectedDescriptors = $this->faker->randomElements($allDescriptors, $this->faker->numberBetween(2, 4));

        return implode('. ', $selectedDescriptors) . '. ' .
               $this->faker->sentence($this->faker->numberBetween(8, 15));
    }

    /**
     * Get category-specific descriptors
     */
    private function getCategorySpecificDescriptors(string $categoryName): array
    {
        $categoryLower = strtolower($categoryName);

        if (str_contains($categoryLower, 'electronic') || str_contains($categoryLower, 'tech')) {
            return [
                'Latest technology and innovations',
                'Smart devices for modern living',
                'High-performance electronics',
                'Cutting-edge digital solutions'
            ];
        }

        if (str_contains($categoryLower, 'fashion') || str_contains($categoryLower, 'cloth')) {
            return [
                'Trendy styles and latest fashion',
                'Comfortable and stylish designs',
                'Fashion-forward clothing and accessories',
                'Express your unique style'
            ];
        }

        if (str_contains($categoryLower, 'home') || str_contains($categoryLower, 'furniture')) {
            return [
                'Transform your living space',
                'Functional and beautiful home solutions',
                'Create the perfect home environment',
                'Quality furniture and home essentials'
            ];
        }

        if (str_contains($categoryLower, 'sport') || str_contains($categoryLower, 'fitness')) {
            return [
                'Achieve your fitness goals',
                'Professional sports equipment',
                'Active lifestyle essentials',
                'Performance-driven sports gear'
            ];
        }

        if (str_contains($categoryLower, 'book') || str_contains($categoryLower, 'education')) {
            return [
                'Expand your knowledge and skills',
                'Educational resources for all levels',
                'Learning made easy and enjoyable',
                'Comprehensive study materials'
            ];
        }

        return [
            $this->faker->catchPhrase(),
            'Quality products at affordable prices',
            'Carefully curated selection'
        ];
    }

    /**
     * Electronics category state
     */
    public function electronics()
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->randomElement([
                'Electronics & Technology',
                'Digital Devices',
                'Smart Electronics',
                'Tech Solutions',
                'Electronic Gadgets'
            ]),
            'slug' => 'electronics-' . $this->faker->randomNumber(3),
            'description' => 'Cutting-edge electronic devices and digital solutions. ' . $this->faker->sentence(12),
        ]);
    }

    /**
     * Fashion category state
     */
    public function fashion()
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->randomElement([
                'Fashion & Style',
                'Trendy Fashion',
                'Style Collection',
                'Fashion Trends',
                'Modern Fashion'
            ]),
            'slug' => 'fashion-' . $this->faker->randomNumber(3),
            'description' => 'Latest fashion trends and stylish clothing. ' . $this->faker->sentence(12),
        ]);
    }

    /**
     * Home category state
     */
    public function homeGarden()
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->randomElement([
                'Home & Living',
                'Home Solutions',
                'Living Space',
                'Home Essentials',
                'House & Garden'
            ]),
            'slug' => 'home-garden-' . $this->faker->randomNumber(3),
            'description' => 'Everything you need for a beautiful and functional home. ' . $this->faker->sentence(12),
        ]);
    }

    /**
     * Sports category state
     */
    public function sports()
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->randomElement([
                'Sports & Outdoors',
                'Fitness & Sports',
                'Active Lifestyle',
                'Sports Equipment',
                'Outdoor Activities'
            ]),
            'slug' => 'sports-outdoors-' . $this->faker->randomNumber(3),
            'description' => 'Professional sports equipment and outdoor gear. ' . $this->faker->sentence(12),
        ]);
    }

    /**
     * Books category state
     */
    public function books()
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->randomElement([
                'Books & Education',
                'Learning Resources',
                'Educational Materials',
                'Books & Knowledge',
                'Study Resources'
            ]),
            'slug' => 'books-education-' . $this->faker->randomNumber(3),
            'description' => 'Educational books and learning materials for all ages. ' . $this->faker->sentence(12),
        ]);
    }
}
