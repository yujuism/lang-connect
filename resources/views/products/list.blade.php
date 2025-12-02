@extends('layout')

@section('title', 'Products - Advanced Search & Filter')

@section('extra-css')
<style>
.search-filters {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 30px;
}

.filter-section {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
}

.price-input {
    width: 120px;
}

.sort-dropdown {
    min-width: 200px;
}

.product-card:hover {
    transform: translateY(-3px);
    transition: transform 0.2s ease;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.rating-stars {
    color: #ffc107;
}

.search-info {
    background: #e3f2fd;
    border-left: 4px solid #2196f3;
    padding: 10px 15px;
    margin-bottom: 20px;
}

.filter-active {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 5px;
    padding: 5px 10px;
    margin: 2px;
    display: inline-block;
    font-size: 0.85rem;
}
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2">
                <i class="bi bi-bag"></i> Products
                <span class="badge bg-primary">{{ $products->total() }}</span>
            </h1>
            <a href="{{ route('products.create') }}" class="btn btn-success">
                <i class="bi bi-plus-lg"></i> Add New Product
            </a>
        </div>
    </div>
</div>

<!-- Advanced Search & Filter Section -->
<div class="search-filters">
    <form method="GET" action="{{ route('products') }}" id="filterForm">
        <div class="row">
            <!-- Search Box -->
            <div class="col-md-4 mb-3">
                <div class="filter-section">
                    <label class="form-label fw-bold">
                        <i class="bi bi-search"></i> Search Products
                    </label>
                    <input type="text"
                           name="search"
                           class="form-control"
                           placeholder="Search by name, description, or SKU..."
                           value="{{ request('search') }}">
                </div>
            </div>

            <!-- Category Filter -->
            <div class="col-md-2 mb-3">
                <div class="filter-section">
                    <label class="form-label fw-bold">
                        <i class="bi bi-grid"></i> Category
                    </label>
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}"
                                    {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Price Range -->
            <div class="col-md-3 mb-3">
                <div class="filter-section">
                    <label class="form-label fw-bold">
                        <i class="bi bi-currency-dollar"></i> Price Range
                    </label>
                    <div class="d-flex gap-2 align-items-center">
                        <input type="number"
                               name="min_price"
                               class="form-control price-input"
                               placeholder="Min"
                               value="{{ request('min_price') }}">
                        <span>-</span>
                        <input type="number"
                               name="max_price"
                               class="form-control price-input"
                               placeholder="Max"
                               value="{{ request('max_price') }}">
                    </div>
                    <small class="text-muted">Range: Rp {{ number_format($priceRange->min_price) }} - Rp {{ number_format($priceRange->max_price) }}</small>
                </div>
            </div>

            <!-- Additional Filters -->
            <div class="col-md-3 mb-3">
                <div class="filter-section">
                    <label class="form-label fw-bold">
                        <i class="bi bi-funnel"></i> Additional Filters
                    </label>

                    <!-- Stock Status -->
                    <select name="stock_status" class="form-select mb-2">
                        <option value="">All Stock Status</option>
                        <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>
                            In Stock
                        </option>
                        <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>
                            Low Stock
                        </option>
                        <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>
                            Out of Stock
                        </option>
                    </select>

                    <!-- Minimum Rating -->
                    <select name="min_rating" class="form-select">
                        <option value="">Any Rating</option>
                        <option value="4.5" {{ request('min_rating') == '4.5' ? 'selected' : '' }}>4.5+ Stars</option>
                        <option value="4.0" {{ request('min_rating') == '4.0' ? 'selected' : '' }}>4.0+ Stars</option>
                        <option value="3.5" {{ request('min_rating') == '3.5' ? 'selected' : '' }}>3.5+ Stars</option>
                        <option value="3.0" {{ request('min_rating') == '3.0' ? 'selected' : '' }}>3.0+ Stars</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row">
            <div class="col-12">
                <div class="d-flex gap-2 justify-content-between align-items-center">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Search & Filter
                        </button>
                        <a href="{{ route('products') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Clear All
                        </a>
                    </div>

                    <!-- Sorting -->
                    <div class="d-flex gap-2 align-items-center">
                        <label class="form-label mb-0">Sort by:</label>
                        <select name="sort_by" class="form-select sort-dropdown" onchange="this.form.submit()">
                            <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Name</option>
                            <option value="price" {{ request('sort_by') == 'price' ? 'selected' : '' }}>Price</option>
                            <option value="rating" {{ request('sort_by') == 'rating' ? 'selected' : '' }}>Rating</option>
                            <option value="stock" {{ request('sort_by') == 'stock' ? 'selected' : '' }}>Stock</option>
                            <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Newest</option>
                        </select>
                        <select name="sort_order" class="form-select" style="width: 100px;" onchange="this.form.submit()">
                            <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>ASC</option>
                            <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>DESC</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Active Filters Display -->
@if(request()->hasAny(['search', 'category', 'min_price', 'max_price', 'stock_status', 'min_rating']))
<div class="search-info">
    <strong><i class="bi bi-info-circle"></i> Active Filters:</strong>

    @if(request('search'))
        <span class="filter-active">Search: "{{ request('search') }}"
            <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="text-danger">×</a>
        </span>
    @endif

    @if(request('category'))
        @php $selectedCategory = $categories->find(request('category')) @endphp
        <span class="filter-active">Category: {{ $selectedCategory->name ?? 'Unknown' }}
            <a href="{{ request()->fullUrlWithQuery(['category' => null]) }}" class="text-danger">×</a>
        </span>
    @endif

    @if(request('min_price') || request('max_price'))
        <span class="filter-active">Price:
            {{ request('min_price') ? 'Rp '.number_format(request('min_price')) : 'Min' }} -
            {{ request('max_price') ? 'Rp '.number_format(request('max_price')) : 'Max' }}
            <a href="{{ request()->fullUrlWithQuery(['min_price' => null, 'max_price' => null]) }}" class="text-danger">×</a>
        </span>
    @endif

    @if(request('stock_status'))
        <span class="filter-active">Stock: {{ ucwords(str_replace('_', ' ', request('stock_status'))) }}
            <a href="{{ request()->fullUrlWithQuery(['stock_status' => null]) }}" class="text-danger">×</a>
        </span>
    @endif

    @if(request('min_rating'))
        <span class="filter-active">Rating: {{ request('min_rating') }}+ Stars
            <a href="{{ request()->fullUrlWithQuery(['min_rating' => null]) }}" class="text-danger">×</a>
        </span>
    @endif

    <a href="{{ route('products') }}" class="btn btn-sm btn-outline-danger ms-2">Clear All Filters</a>
</div>
@endif

<!-- Results Info -->
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted">
                Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }}
                of {{ $products->total() }} results
                @if(request('search'))
                    for "<strong>{{ request('search') }}</strong>"
                @endif
            </div>
            <div class="text-muted">
                Page {{ $products->currentPage() }} of {{ $products->lastPage() }}
            </div>
        </div>
    </div>
</div>

<!-- Products Grid -->
<div class="row">
    @forelse($products as $product)
        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
            <div class="card product-card h-100 shadow-sm">
                <div class="card-body d-flex flex-column">
                    <!-- Product Name & Category -->
                    <h6 class="card-title mb-2">{{ Str::limit($product->name, 60) }}</h6>
                    <div class="mb-2">
                        <span class="badge bg-secondary">{{ $product->category->name }}</span>
                        <small class="text-muted">SKU: {{ $product->sku }}</small>
                    </div>

                    <!-- Description -->
                    <p class="card-text text-muted small flex-grow-1">
                        {{ Str::limit($product->description, 80) }}
                    </p>

                    <!-- Rating -->
                    <div class="mb-2">
                        <span class="rating-stars">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $product->rating)
                                    <i class="bi bi-star-fill"></i>
                                @else
                                    <i class="bi bi-star"></i>
                                @endif
                            @endfor
                        </span>
                        <small class="text-muted">({{ $product->rating }})</small>
                    </div>

                    <!-- Price & Stock -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="h6 text-primary mb-0">{{ $product->formatted_price }}</span>
                        <span class="badge {{ $product->stock_badge_class }}">
                            {{ $product->stock_status }}
                        </span>
                    </div>

                    <!-- Stock Info -->
                    <div class="mb-3">
                        <small class="text-muted">Stock: {{ $product->stock }} units</small>
                    </div>

                    <!-- Action Buttons -->
                    <div class="btn-group w-100" role="group">
                        <a href="{{ route('products.show', $product->id) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-eye"></i> View
                        </a>
                        <a href="{{ route('products.edit', $product->id) }}" class="btn btn-outline-warning btn-sm">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="text-center py-5">
                <i class="bi bi-search display-1 text-muted"></i>
                <h3 class="text-muted mt-3">No products found</h3>
                <p class="text-muted">
                    @if(request()->hasAny(['search', 'category', 'min_price', 'max_price', 'stock_status', 'min_rating']))
                        Try adjusting your search criteria or <a href="{{ route('products') }}">browse all products</a>
                    @else
                        Start by adding your first product.
                    @endif
                </p>
                @if(!request()->hasAny(['search', 'category', 'min_price', 'max_price', 'stock_status', 'min_rating']))
                    <a href="{{ route('products.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> Add New Product
                    </a>
                @endif
            </div>
        </div>
    @endforelse
</div>

<!-- Pagination -->
@if($products->hasPages())
    <div class="row mt-4">
        <div class="col-12 d-flex justify-content-center">
            {{ $products->links() }}
        </div>
    </div>
@endif

<!-- Statistics -->
@if($products->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-graph-up"></i> Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="border-end">
                                <h4 class="text-primary">{{ $totalProducts }}</h4>
                                <small class="text-muted">Total Products</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <h4 class="text-success">{{ $totalCategories }}</h4>
                                <small class="text-muted">Categories</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <h4 class="text-warning">Rp {{ number_format($averagePrice, 0, ',', '.') }}</h4>
                                <small class="text-muted">Average Price</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-info">{{ number_format($averageRating, 1) }}</h4>
                            <small class="text-muted">Average Rating</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

@section('extra-js')
<script>
// Auto-submit form on sort change
document.querySelectorAll('select[name="sort_by"], select[name="sort_order"]').forEach(function(select) {
    select.addEventListener('change', function() {
        document.getElementById('filterForm').submit();
    });
});

// Search suggestions (optional enhancement)
const searchInput = document.querySelector('input[name="search"]');
if (searchInput) {
    let timeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            // You can implement AJAX search suggestions here
            console.log('Searching for:', this.value);
        }, 300);
    });
}
</script>
@endsection
