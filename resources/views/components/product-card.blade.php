@props(['product'])

<div class="col-md-6 col-lg-4 mb-4">
    <div class="card h-100 shadow-sm">
        <div class="card-body d-flex flex-column">
            <h5 class="card-title">{{ $product->name }}</h5>
            <p class="card-text text-muted flex-grow-1">{{ Str::limit($product->description, 80) }}</p>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="h5 text-primary mb-0">
                    Rp {{ number_format($product->price, 0, ',', '.') }}
                </span>
                <span class="badge bg-secondary">ID: {{ $product->id }}</span>
            </div>
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
