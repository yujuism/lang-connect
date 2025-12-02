<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Product Management System')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    @yield('extra-css')
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="bi bi-shop"></i> Product Store
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                            <i class="bi bi-house"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('products') ? 'active' : '' }}" href="{{ route('products') }}">
                            <i class="bi bi-list"></i> Products
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-grid"></i> Categories
                        </a>
                        <ul class="dropdown-menu">
                            @if(class_exists('App\Models\Category'))
                                @php $categories = App\Models\Category::orderBy('name')->get() @endphp
                                @foreach($categories as $category)
                                    <li>
                                        <a class="dropdown-item" href="{{ route('products', ['category' => $category->id]) }}">
                                            {{ $category->name }}
                                        </a>
                                    </li>
                                @endforeach
                                <li><hr class="dropdown-divider"></li>
                            @endif
                            <li><a class="dropdown-item" href="{{ route('products') }}">All Categories</a></li>
                        </ul>
                    </li>
                </ul>

                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('products.create') ? 'active' : '' }}" href="{{ route('products.create') }}">
                            <i class="bi bi-plus-circle"></i> Add Product
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-funnel"></i> Quick Filter
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('products', ['min_rating' => 4.5]) }}">
                                <i class="bi bi-star-fill text-warning"></i> Top Rated (4.5+)
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('products', ['max_price' => 100000]) }}">
                                <i class="bi bi-currency-dollar text-success"></i> Under Rp 100k
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('products', ['stock_status' => 'in_stock']) }}">
                                <i class="bi bi-check-circle text-success"></i> In Stock Only
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('products', ['sort_by' => 'created_at', 'sort_order' => 'desc']) }}">
                                <i class="bi bi-clock text-info"></i> Newest First
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="@if(!request()->routeIs('home')) container my-4 @endif">
        @if(session('success'))
            <div class="@if(request()->routeIs('home')) container @endif">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="@if(request()->routeIs('home')) container @endif">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="@if(request()->routeIs('home')) container @endif">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Please fix the following errors:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="fw-bold">Product Management System</h6>
                    <p class="text-muted mb-0">
                        Your one-stop shop for managing and browsing products across multiple categories.
                    </p>
                </div>
                <div class="col-md-3">
                    <h6 class="fw-bold">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('home') }}" class="text-muted text-decoration-none">Home</a></li>
                        <li><a href="{{ route('products') }}" class="text-muted text-decoration-none">All Products</a></li>
                        <li><a href="{{ route('products.create') }}" class="text-muted text-decoration-none">Add Product</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6 class="fw-bold">Statistics</h6>
                    <ul class="list-unstyled">
                        @if(class_exists('App\Models\Product') && class_exists('App\Models\Category'))
                            <li class="text-muted">{{ App\Models\Product::count() }} Products</li>
                            <li class="text-muted">{{ App\Models\Category::count() }} Categories</li>
                            <li class="text-muted">{{ App\Models\Product::where('stock', '>', 0)->count() }} In Stock</li>
                        @endif
                    </ul>
                </div>
            </div>
            <hr>
            <div class="text-center text-muted">
                <p class="mb-0">&copy; 2024 Product Management System. Built with Laravel & Bootstrap.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @yield('extra-js')
</body>
</html>
