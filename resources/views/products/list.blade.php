@extends('layout')

@section('title', 'Product List')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2">
                <i class="bi bi-bag"></i> Products
                <span class="badge bg-primary">{{ $products->count() }}</span>
            </h1>
            <a href="{{ route('products.create') }}" class="btn btn-success">
                <i class="bi bi-plus-lg"></i> Add new product
            </a>
        </div>
    </div>
</div>

<div class="row">
    @forelse($products as $product)
        <x-product-card :product="$product" />
    @empty
        <div class="col-12">
            <div class="text-center py-5">
                <i class="bi bi-box text-muted" style="font-size: 4rem;"></i>
                <h3 class="text-muted mt-3">No products found</h3>
                <p class="text-muted">Start by adding your first product.</p>
                <a href="{{ route('products.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Add new product
                </a>
            </div>
        </div>
    @endforelse
</div>

@if($products->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="border-end">
                                <h4 class="text-primary">{{ $products->count() }}</h4>
                                <small class="text-muted">Total Products</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <h4 class="text-success">Rp {{ number_format($products->avg('price'), 0, ',', '.') }}</h4>
                                <small class="text-muted">Average Price</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <h4 class="text-warning">Rp {{ number_format($products->max('price'), 0, ',', '.') }}</h4>
                                <small class="text-muted">Highest Price</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-info">Rp {{ number_format($products->min('price'), 0, ',', '.') }}</h4>
                            <small class="text-muted">Lowest Price</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection
