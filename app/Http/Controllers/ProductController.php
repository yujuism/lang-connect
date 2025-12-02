<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource with search, filter, and sort
     */
    public function index(Request $request)
    {
        $query = Product::with('category');

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('sku', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Price range filter
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Stock filter
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'in_stock':
                    $query->where('stock', '>', 0);
                    break;
                case 'low_stock':
                    $query->whereBetween('stock', [1, 5]);
                    break;
                case 'out_of_stock':
                    $query->where('stock', 0);
                    break;
            }
        }

        // Rating filter
        if ($request->filled('min_rating')) {
            $query->where('rating', '>=', $request->min_rating);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');

        $allowedSorts = ['name', 'price', 'rating', 'stock', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('name', 'asc');
        }

        // Pagination
        $products = $query->paginate(12)->appends($request->query());

        // Get categories for filter dropdown
        $categories = Category::orderBy('name')->get();

        // Get price range for filter
        $priceRange = Product::selectRaw('MIN(price) as min_price, MAX(price) as max_price')->first();

        // Statistics
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $averagePrice = Product::avg('price');
        $averageRating = Product::avg('rating');

        return view('products.list', compact(
            'products',
            'categories',
            'priceRange',
            'totalProducts',
            'totalCategories',
            'averagePrice',
            'averageRating'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('products.form', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'stock' => 'required|integer|min:0',
            'sku' => 'required|string|max:50|unique:products,sku'
        ]);

        Product::create($request->all());

        return redirect()->route('products')
            ->with('success', 'Product created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $product = Product::with('category')->findOrFail($id);

        // Get related products from same category
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(4)
            ->get();

        return view('products.show', compact('product', 'relatedProducts'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::orderBy('name')->get();

        return view('products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'stock' => 'required|integer|min:0',
            'sku' => 'required|string|max:50|unique:products,sku,' . $product->id
        ]);

        $product->update($request->all());

        return redirect()->route('products')
            ->with('success', 'Product updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('products')
            ->with('success', 'Product deleted successfully!');
    }

    /**
     * API endpoint for search suggestions
     */
    public function searchSuggestions(Request $request)
    {
        if (!$request->filled('q')) {
            return response()->json([]);
        }

        $query = $request->q;
        $suggestions = Product::select('name', 'id')
            ->where('name', 'LIKE', "%{$query}%")
            ->limit(10)
            ->get()
            ->map(function($product) {
                return [
                    'id' => $product->id,
                    'text' => $product->name,
                    'url' => route('products.show', $product->id)
                ];
            });

        return response()->json($suggestions);
    }
}
