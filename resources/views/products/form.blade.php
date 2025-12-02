@extends('layout')

@section('title', 'Add New Product')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    <i class="bi bi-plus-circle"></i> Add New Product
                </h4>
            </div>
            <div class="card-body">
                <form action="{{ route('products.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="name" class="form-label">
                                <i class="bi bi-tag"></i> Product Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   value="{{ old('name') }}"
                                   placeholder="Enter product name"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="sku" class="form-label">
                                <i class="bi bi-upc-scan"></i> SKU <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('sku') is-invalid @enderror"
                                   id="sku"
                                   name="sku"
                                   value="{{ old('sku') }}"
                                   placeholder="e.g., PRD-001"
                                   required>
                            @error('sku')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label">
                                <i class="bi bi-grid"></i> Category <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('category_id') is-invalid @enderror"
                                    id="category_id"
                                    name="category_id"
                                    required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}"
                                            {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="price" class="form-label">
                                <i class="bi bi-currency-dollar"></i> Price <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number"
                                       class="form-control @error('price') is-invalid @enderror"
                                       id="price"
                                       name="price"
                                       value="{{ old('price') }}"
                                       min="0"
                                       step="1000"
                                       placeholder="0"
                                       required>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="stock" class="form-label">
                                <i class="bi bi-box"></i> Stock <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   class="form-control @error('stock') is-invalid @enderror"
                                   id="stock"
                                   name="stock"
                                   value="{{ old('stock', 0) }}"
                                   min="0"
                                   placeholder="0"
                                   required>
                            @error('stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">
                            <i class="bi bi-text-paragraph"></i> Description <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description"
                                  name="description"
                                  rows="4"
                                  placeholder="Enter detailed product description"
                                  required>{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Provide a detailed description to help customers understand the product features and benefits.</div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('products') }}" class="btn btn-secondary me-md-2">
                            <i class="bi bi-arrow-left"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save Product
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Help Card -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-lightbulb"></i> Tips for Adding Products</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="bi bi-check-circle text-success"></i> Best Practices:</h6>
                        <ul class="small mb-0">
                            <li>Use descriptive and unique product names</li>
                            <li>Choose the most appropriate category</li>
                            <li>Set competitive pricing based on market research</li>
                            <li>Ensure SKU is unique and follows your naming convention</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="bi bi-info-circle text-primary"></i> Description Guidelines:</h6>
                        <ul class="small mb-0">
                            <li>Include key features and specifications</li>
                            <li>Highlight unique selling points</li>
                            <li>Mention use cases and benefits</li>
                            <li>Keep it informative but concise</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Quick Add -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-grid"></i> Available Categories</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($categories->chunk(ceil($categories->count() / 3)) as $categoryChunk)
                        <div class="col-md-4">
                            @foreach($categoryChunk as $category)
                                <div class="mb-2">
                                    <strong>{{ $category->name }}</strong><br>
                                    <small class="text-muted">{{ $category->description }}</small>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-js')
<script>
// Auto-generate SKU based on product name
document.getElementById('name').addEventListener('input', function(e) {
    const name = e.target.value;
    const skuField = document.getElementById('sku');

    if (name && !skuField.value) {
        // Generate SKU from first 3 letters and random number
        const letters = name.replace(/[^a-zA-Z]/g, '').substring(0, 3).toUpperCase();
        const numbers = Math.floor(Math.random() * 999).toString().padStart(3, '0');
        skuField.value = letters + '-' + numbers;
    }
});

// Format price input
document.getElementById('price').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    e.target.value = value;
});

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const requiredFields = ['name', 'category_id', 'price', 'stock', 'sku', 'description'];
    let hasErrors = false;

    requiredFields.forEach(field => {
        const input = document.getElementById(field);
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            hasErrors = true;
        } else {
            input.classList.remove('is-invalid');
        }
    });

    if (hasErrors) {
        e.preventDefault();
        alert('Please fill in all required fields.');
    }
});
</script>
@endsection
