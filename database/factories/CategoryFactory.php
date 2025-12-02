<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state using extensive Faker features.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Use Faker to generate realistic product names
        $adjectives = [
            $this->faker->randomElement(['Premium', 'Professional', 'Advanced', 'Smart', 'Ultra', 'Pro', 'Elite', 'Digital', 'Modern', 'Classic']),
            $this->faker->randomElement(['High-Quality', 'Durable', 'Lightweight', 'Portable', 'Wireless', 'Multi-Function', 'Energy-Efficient'])
        ];

        $brands = [
            'Electronics' => $this->faker->randomElement(['Samsung', 'Apple', 'Sony', 'LG', 'Xiaomi', 'Huawei', 'ASUS', 'Acer', 'HP', 'Dell']),
            'Fashion' => $this->faker->randomElement(['Nike', 'Adidas', 'Uniqlo', 'Zara', 'H&M', 'Local Brand', 'Premium', 'Trendy']),
            'Home' => $this->faker->randomElement(['IKEA', 'Philips', 'Sharp', 'Panasonic', 'Electrolux', 'Miyako', 'Cosmos', 'Oxone']),
            'Sports' => $this->faker->randomElement(['Nike', 'Adidas', 'Puma', 'Yonex', 'Wilson', 'Spalding', 'Mikasa']),
            'Books' => $this->faker->randomElement(['Gramedia', 'Erlangga', 'Mizan', 'Bentang', 'Noura', 'Tiga Serangkai'])
        ];

        $category = Category::inRandomOrder()->first();
        $categoryType = $this->getCategoryType($category);
        $brand = $brands[$categoryType] ?? $this->faker->company();

        // Generate product name using Faker
        $productName = $this->generateProductName($categoryType, $brand, $adjectives);

        // Generate realistic Indonesian pricing using Faker
        $basePrice = $this->generateRealisticPrice($categoryType);

        // Use Faker to create compelling descriptions
        $description = $this->generateProductDescription($productName, $categoryType);

        // Generate realistic SKU using Faker
        $sku = $this->generateSKU($categoryType);

        return [
            'name' => $productName,
            'description' => $description,
            'price' => $basePrice,
            'category_id' => $category ? $category->id : Category::factory(),
            'stock' => $this->faker->biasedNumberBetween(0, 200, function($x) {
                // Most products have reasonable stock, few are out of stock
                return 1 - sqrt($x / 200);
            }),
            'rating' => $this->faker->biasedNumberBetween(30, 50, function($x) {
                // Most products have good ratings (3.0-5.0, biased towards higher)
                return pow($x / 50, 2);
            }) / 10,
            'sku' => $sku,
            'image' => null,
        ];
    }

    /**
     * Generate product name based on category
     */
    private function generateProductName(string $categoryType, string $brand, array $adjectives): string
    {
        $productTypes = [
            'Electronics' => [
                'Smartphone', 'Laptop', 'Tablet', 'Smart TV', 'Camera', 'Headphone',
                'Speaker', 'Power Bank', 'Charger', 'Mouse', 'Keyboard', 'Monitor'
            ],
            'Fashion' => [
                'Kemeja', 'Jaket', 'Sepatu', 'Tas', 'Celana', 'Dress', 'Kaos',
                'Hoodie', 'Blazer', 'Sandal', 'Topi', 'Jam Tangan', 'Kacamata'
            ],
            'Home' => [
                'Rice Cooker', 'Blender', 'Vacuum Cleaner', 'Air Purifier', 'Microwave',
                'Sofa', 'Meja', 'Kursi', 'Lemari', 'Lampu', 'Dispenser', 'Kulkas'
            ],
            'Sports' => [
                'Sepatu Lari', 'Raket', 'Bola', 'Dumbbell', 'Yoga Mat', 'Treadmill',
                'Sepeda', 'Helm', 'Tas Olahraga', 'Jersey', 'Celana Training'
            ],
            'Books' => [
                'Novel', 'Buku Pelajaran', 'Komik', 'Ensiklopedia', 'Kamus',
                'Buku Motivasi', 'Buku Resep', 'Atlas', 'Buku Programming'
            ]
        ];

        $types = $productTypes[$categoryType] ?? $productTypes['Electronics'];
        $type = $this->faker->randomElement($types);
        $adjective = $this->faker->randomElement($adjectives);

        // Sometimes add model/series using Faker
        $model = '';
        if ($this->faker->boolean(60)) {
            $model = ' ' . $this->faker->randomElement([
                $this->faker->lexify('???') . ' ' . $this->faker->numberBetween(1, 999),
                'Series ' . $this->faker->numberBetween(1, 10),
                $this->faker->year() . ' Edition',
                'Gen ' . $this->faker->numberBetween(1, 5),
                $this->faker->randomLetter() . $this->faker->numberBetween(10, 99)
            ]);
        }

        return trim("$type $brand $adjective$model");
    }

    /**
     * Generate realistic Indonesian pricing
     */
    private function generateRealisticPrice(string $categoryType): float
    {
        $priceRanges = [
            'Electronics' => [$this->faker->numberBetween(100000, 15000000)],
            'Fashion' => [$this->faker->numberBetween(25000, 2000000)],
            'Home' => [$this->faker->numberBetween(50000, 8000000)],
            'Sports' => [$this->faker->numberBetween(30000, 3000000)],
            'Books' => [$this->faker->numberBetween(15000, 500000)]
        ];

        $basePrice = $priceRanges[$categoryType][0] ?? $this->faker->numberBetween(50000, 1000000);

        // Add some price variation using Faker
        $variation = $this->faker->randomFloat(2, 0.8, 1.3);
        $finalPrice = $basePrice * $variation;

        // Round to nearest 1000 for Indonesian pricing convention
        return round($finalPrice / 1000) * 1000;
    }

    /**
     * Generate compelling product description using Faker
     */
    private function generateProductDescription(string $productName, string $categoryType): string
    {
        $features = [
            $this->faker->catchPhrase(),
            'Kualitas ' . $this->faker->randomElement(['premium', 'terbaik', 'tinggi', 'unggul', 'superior']),
            $this->faker->randomElement(['Tahan lama', 'Awet', 'Bergaransi', 'Original', 'Berkualitas tinggi']),
            'Cocok untuk ' . $this->faker->randomElement(['penggunaan sehari-hari', 'profesional', 'rumah tangga', 'kantor', 'pribadi']),
            $this->faker->randomElement(['Mudah digunakan', 'User-friendly', 'Praktis', 'Efisien', 'Multifungsi'])
        ];

        // Add category-specific features
        $specificFeatures = [
            'Electronics' => ['Teknologi terdepan', 'Hemat energi', 'Konektivitas lengkap', 'Performa tinggi'],
            'Fashion' => ['Bahan berkualitas', 'Desain trendy', 'Nyaman digunakan', 'Fashion terkini'],
            'Home' => ['Ramah lingkungan', 'Mudah dibersihkan', 'Desain modern', 'Fungsional'],
            'Sports' => ['Performa optimal', 'Ergonomis', 'Anti-slip', 'Professional grade'],
            'Books' => ['Informasi terkini', 'Mudah dipahami', 'Referensi lengkap', 'Konten berkualitas']
        ];

        if (isset($specificFeatures[$categoryType])) {
            $features = array_merge($features, $specificFeatures[$categoryType]);
        }

        $selectedFeatures = $this->faker->randomElements($features, $this->faker->numberBetween(3, 5));
        $description = implode('. ', $selectedFeatures) . '. ';

        // Add a Faker-generated sentence
        $description .= $this->faker->sentence($this->faker->numberBetween(10, 20));

        return $description;
    }

    /**
     * Generate realistic SKU
     */
    private function generateSKU(string $categoryType): string
    {
        $prefixes = [
            'Electronics' => 'ELC',
            'Fashion' => 'FSH',
            'Home' => 'HOM',
            'Sports' => 'SPT',
            'Books' => 'BKS'
        ];

        $prefix = $prefixes[$categoryType] ?? 'PRD';
        $number = $this->faker->unique()->numberBetween(1000, 9999);
        $suffix = $this->faker->randomLetter() . $this->faker->randomLetter();

        return strtoupper("$prefix-$number-$suffix");
    }

    /**
     * Get category type from category
     */
    private function getCategoryType(?Category $category): string
    {
        if (!$category) return 'Electronics';

        $slug = $category->slug ?? $category->name ?? '';
        $slug = strtolower($slug);

        if (str_contains($slug, 'electronic') || str_contains($slug, 'tech')) return 'Electronics';
        if (str_contains($slug, 'fashion') || str_contains($slug, 'cloth')) return 'Fashion';
        if (str_contains($slug, 'home') || str_contains($slug, 'garden') || str_contains($slug, 'furniture')) return 'Home';
        if (str_contains($slug, 'sport') || str_contains($slug, 'fitness') || str_contains($slug, 'outdoor')) return 'Sports';
        if (str_contains($slug, 'book') || str_contains($slug, 'education')) return 'Books';

        return 'Electronics'; // Default
    }

    /**
     * Create expensive product
     */
    public function expensive()
    {
        return $this->state(fn (array $attributes) => [
            'price' => $this->faker->numberBetween(5000000, 50000000),
        ]);
    }

    /**
     * Create budget product
     */
    public function budget()
    {
        return $this->state(fn (array $attributes) => [
            'price' => $this->faker->numberBetween(10000, 200000),
        ]);
    }

    /**
     * Create bestseller product
     */
    public function bestseller()
    {
        return $this->state(fn (array $attributes) => [
            'rating' => $this->faker->randomFloat(1, 4.5, 5.0),
            'stock' => $this->faker->numberBetween(50, 200),
        ]);
    }

    /**
     * Create limited edition product
     */
    public function limitedEdition()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => $attributes['name'] . ' Limited Edition',
                'stock' => $this->faker->numberBetween(1, 10),
                'price' => $attributes['price'] * $this->faker->randomFloat(2, 1.2, 2.0),
            ];
        });
    }
}
