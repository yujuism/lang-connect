<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;

class HomeController extends Controller
{
    /**
     * Display the home page
     */
    public function index()
    {
        // Get featured products (high rated and in stock)
        $featuredProducts = Product::with('category')
            ->where('rating', '>=', 4.5)
            ->where('stock', '>', 0)
            ->orderBy('rating', 'desc')
            ->take(6)
            ->get();

        // Get latest products
        $latestProducts = Product::with('category')
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        // Get categories with product count
        $categories = Category::withCount('products')
            ->orderBy('products_count', 'desc')
            ->get();

        // Get statistics
        $stats = [
            'total_products' => Product::count(),
            'total_categories' => Category::count(),
            'average_rating' => number_format(Product::avg('rating'), 1),
            'products_in_stock' => Product::where('stock', '>', 0)->count()
        ];

        // Get top categories by product count
        $topCategories = Category::withCount('products')
            ->orderBy('products_count', 'desc')
            ->take(4)
            ->get();

        // Get price ranges
        $priceRanges = [
            ['label' => 'Under Rp 100k', 'min' => 0, 'max' => 100000, 'count' => Product::where('price', '<', 100000)->count()],
            ['label' => 'Rp 100k - 500k', 'min' => 100000, 'max' => 500000, 'count' => Product::whereBetween('price', [100000, 500000])->count()],
            ['label' => 'Rp 500k - 1M', 'min' => 500000, 'max' => 1000000, 'count' => Product::whereBetween('price', [500000, 1000000])->count()],
            ['label' => 'Over Rp 1M', 'min' => 1000000, 'max' => 999999999, 'count' => Product::where('price', '>', 1000000)->count()],
        ];

        return view('home', compact(
            'featuredProducts',
            'latestProducts',
            'categories',
            'stats',
            'topCategories',
            'priceRanges'
        ));
    }

    /**
     * Quick search from home page
     */
    public function quickSearch(Request $request)
    {
        $query = $request->get('q');

        if (empty($query)) {
            return redirect()->route('products');
        }

        return redirect()->route('products', ['search' => $query]);
    }
}
