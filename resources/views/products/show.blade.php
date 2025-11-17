@extends('layout')

@section('title', 'Product Details - ' . $product->name)

@section('content')
<div class="row">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('products') }}">Products</a></li>
                <li class="breadcrumb-item active">{{ $product->name }}</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header bg-info text-white">
                <h4 class="mb-0">
                    <i class="bi bi-box"></i> Product Details
                </h4>
            </div>
            <div class="card-body">
                <h1 class="h3 mb-3">{{ $product->name }}</h1>

                <div class="row mb-4">
                    <div class="col-sm-3">
                        <strong>Product ID:</strong>
                    </div>
                    <div class="col-sm-9">
                        <span class="badge bg-secondary">{{ $product->id }}</span>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-sm-3">
                        <strong>Price:</strong>
                    </div>
                    <div class="col-sm-9">
                        <h4 class="text-primary mb-0">
                            Rp {{ number_format($product->price, 0, ',', '.') }}
                        </h4>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-sm-3">
                        <strong>Description:</strong>
                    </div>
                    <div class="col-sm-9">
                        <p class="text-muted">{{ $product->description }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-gear"></i> Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('products.edit', $product->id) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Edit Product
                    </a>
                    <a href="{{ route('products') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <div class="card shadow mt-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-info-circle"></i> Product Information
                </h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-12 mb-3">
                        <div class="border rounded p-3">
                            <i class="bi bi-calendar-check text-success" style="font-size: 2rem;"></i>
                            <div class="mt-2">
                                <small class="text-muted">Created</small>
                                <div class="fw-bold">Today</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-graph-up"></i> Related Information
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-lightbulb"></i>
                    <strong>Note:</strong> This is a demonstration view. In a real application, you might display related products, reviews, or inventory information here.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
