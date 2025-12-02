@extends('layout')

@section('title', 'Home - Product Management System')

@section('extra-css')
<style>
.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 80px 0;
}

.category-card:hover {
    transform: translateY(-5px);
    transition: transform 0.3s ease;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.stats-card {
    background: linear-gradient(45deg, #f093fb 0%, #f5576c 100%);
    color: white;
    border: none;
}

.featured-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #ff6b6b;
    color: white;
    border-radius: 20px;
    padding: 5px 12px;
    font-size: 0.8rem;
    font-weight: bold;
}

.price-range-card:hover {
    background: #f8f9fa;
    cursor: pointer;
}
</style>
@endsection

@section('content')
<!-- Hero Section -->
<section class="hero-section text-center">
    <div class="container">
        <h1 class="display-4 fw-bold mb-4">Welcome to Our Product Store</h1>
        <p class="lead mb-5">Discover amazing products across {{ $stats['total_categories'] }} categories with over {{ $stats['total_products'] }} items</p>

        <!-- Quick Search -->
        <div class="row justify-content-center">
            <div class="col-md-6">
                <form action="{{ route('home.quick-search') }}" method="POST" class="d-flex gap-2">
                    @csrf
                    <input type="text" name="q" class="form-control form-control-lg"
                           placeholder="Search for products..." required>
                    <button type="submit" class="btn btn-warning btn-lg px-4">
                        <i class="bi bi-search"></i> Search
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="card stats-card text-center h-100">
                    <div class="card-body">
                        <i class="bi bi-box-seam display-4 mb-3"></i>
                        <h3 class="fw-bold">{{ $stats['total_products'] }}</h3>
                        <p class="mb-0">Total Products</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card text-center h-100">
                    <div class="card-body">
                        <i class="bi bi-grid display-4 mb-3"></i>
                        <h3 class="fw-bold">{{ $stats['total_categories'] }}</h3>
                        <p class="mb-0">Categories</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card text-center h-100">
                    <div class="card-body">
                        <i class="bi bi-star-fill display-4 mb-3"></i>
                        <h3 class="fw-bold">{{ $stats['average_rating'] }}</h3>
                        <p class="mb-0">Average Rating</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stats-card text-center h-100">
                    <div class="card-body">
                        <i class="bi bi-check-circle display-4 mb-3"></i>
                        <h3 class="fw-bold">{{ $stats['products_in_stock'] }}</h3>
                        <p class="mb-0">In Stock</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">⭐ Featured Products</h2>
            <p class="text-muted">Our top-rated products that customers love</p>
        </div>

        <div class="row">
            @forelse($featuredProducts as $product)
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 shadow-sm position-relative">
                    <div class="featured-badge">
                        <i class="bi bi-star-fill"></i> Featured
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">{{ $product->name }}</h5>
                        <p class="text-muted small">{{ $product->category->name }}</p>
                        <p class="card-text">{{ Str::limit($product->description, 80) }}</p>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="h5 text-primary mb-0">{{ $product->formatted_price }}</span>
                            <div class="text-warning">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $product->rating)
                                        <i class="bi bi-star-fill"></i>
                                    @else
                                        <i class="bi bi-star"></i>
                                    @endif
                                @endfor
                                <small class="text-muted">({{ $product->rating }})</small>
                            </div>
                        </div>

                        <span class="badge {{ $product->stock_badge_class }} mb-3">{{ $product->stock_status }}</span>

                        <div class="d-grid">
                            <a href="{{ route('products.show', $product->id) }}" class="btn btn-primary">
                                <i class="bi bi-eye"></i> View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center">
                <p class="text-muted">No featured products available at the moment.</p>
            </div>
            @endforelse
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">🛍️ Shop by Category</h2>
            <p class="text-muted">Browse our wide range of product categories</p>
        </div>

        <div class="row">
            @foreach($topCategories as $category)
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card category-card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            @switch($category->slug)
                                @case('electronics')
                                    <i class="bi bi-laptop display-3 text-primary"></i>
                                    @break
                                @case('fashion')
                                    <i class="bi bi-bag display-3 text-danger"></i>
                                    @break
                                @case('home-garden')
                                    <i class="bi bi-house display-3 text-success"></i>
                                    @break
                                @case('sports-outdoors')
                                    <i class="bi bi-bicycle display-3 text-warning"></i>
                                    @break
                                @case('books-education')
                                    <i class="bi bi-book display-3 text-info"></i>
                                    @break
                                @case('health-beauty')
                                    <i class="bi bi-heart display-3 text-pink"></i>
                                    @break
                                @default
                                    <i class="bi bi-grid display-3 text-secondary"></i>
                            @endswitch
                        </div>
                        <h5 class="card-title">{{ $category->name }}</h5>
                        <p class="card-text text-muted">{{ $category->description }}</p>
                        <span class="badge bg-primary">{{ $category->products_count }} Products</span>
                        <div class="mt-3">
                            <a href="{{ route('products', ['category' => $category->id]) }}" class="btn btn-outline-primary">
                                Browse Products
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('products') }}" class="btn btn-primary btn-lg">
                <i class="bi bi-grid"></i> View All Products
            </a>
        </div>
    </div>
</section>

<!-- Price Ranges -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">💰 Shop by Price Range</h2>
            <p class="text-muted">Find products that fit your budget</p>
        </div>

        <div class="row">
            @foreach($priceRanges as $range)
            <div class="col-md-3 mb-3">
                <a href="{{ route('products', ['min_price' => $range['min'], 'max_price' => $range['max']]) }}"
                   class="text-decoration-none">
                    <div class="card price-range-card h-100">
                        <div class="card-body text-center">
                            <h5 class="card-title">{{ $range['label'] }}</h5>
                            <div class="display-6 text-primary">{{ $range['count'] }}</div>
                            <small class="text-muted">Products Available</small>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Latest Products -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">🆕 Latest Products</h2>
            <p class="text-muted">Check out our newest additions</p>
        </div>

        <div class="row">
            @foreach($latestProducts->take(4) as $product)
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title">{{ Str::limit($product->name, 40) }}</h6>
                        <p class="text-muted small">{{ $product->category->name }}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-primary">{{ $product->formatted_price }}</span>
                            <a href="{{ route('products.show', $product->id) }}" class="btn btn-sm btn-outline-primary">
                                View
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="text-center">
            <a href="{{ route('products', ['sort_by' => 'created_at', 'sort_order' => 'desc']) }}"
               class="btn btn-outline-primary">
                View All Latest Products <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5 bg-primary text-white">
    <div class="container text-center">
        <h2 class="display-5 fw-bold mb-4">Ready to Start Shopping?</h2>
        <p class="lead mb-4">Explore our complete catalog of products and find exactly what you need</p>
        <a href="{{ route('products') }}" class="btn btn-warning btn-lg px-5">
            <i class="bi bi-shop"></i> Start Shopping Now
        </a>
    </div>
</section>
@endsection
