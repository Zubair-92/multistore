@extends('frontend.layouts.app')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">My Products</h2>
            <p class="text-muted mb-0">Review your store catalog and inventory at a glance.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('store.products.create') }}" class="btn btn-primary">Add Product</a>
            <a href="{{ route('store.profile') }}" class="btn btn-outline-secondary">Back to Dashboard</a>
        </div>
    </div>

    <div class="card marketplace-surface-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('store.products') }}" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Product ID or name">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="active" @selected(($filters['status'] ?? null) === 'active')>Active</option>
                        <option value="inactive" @selected(($filters['status'] ?? null) === 'inactive')>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Stock</label>
                    <select name="stock" class="form-select">
                        <option value="">All</option>
                        <option value="low" @selected(($filters['stock'] ?? null) === 'low')>Low stock</option>
                        <option value="out" @selected(($filters['stock'] ?? null) === 'out')>Out of stock</option>
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button class="btn btn-dark">Apply</button>
                </div>
                <div class="col-12 d-flex justify-content-end">
                    <a href="{{ route('store.products') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card marketplace-surface-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Subcategory</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Last Movement</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>{{ $product->id }}</td>
                            <td>
                                @if($product->image)
                                    <img src="{{ asset('storage/'.$product->image) }}" alt="Product" width="54" class="rounded">
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $product->name ?? 'Product #'.$product->id }}</td>
                            <td>{{ $product->category->translation->category ?? '-' }}</td>
                            <td>{{ $product->subcategory->translation->sub_category ?? '-' }}</td>
                            <td>
                                ${{ number_format($product->price, 2) }}
                                @if($product->offer_price)
                                    <div><small class="text-danger">Offer: ${{ number_format($product->offer_price, 2) }}</small></div>
                                @endif
                            </td>
                            <td>
                                <span class="fw-semibold {{ $product->stock <= 5 ? 'text-danger' : '' }}">{{ $product->stock }}</span>
                                @if($product->stock <= 5)
                                    <div><small class="text-warning">Low stock</small></div>
                                @endif
                            </td>
                            <td>
                                @if($product->stockMovements->first())
                                    <small class="text-muted">
                                        {{ strtoupper($product->stockMovements->first()->direction) }}
                                        {{ $product->stockMovements->first()->quantity }}
                                        <br>
                                        {{ $product->stockMovements->first()->created_at->format('d M Y') }}
                                    </small>
                                @else
                                    <small class="text-muted">No history</small>
                                @endif
                            </td>
                            <td>
                                @if($product->status)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-2 justify-content-end mb-2">
                                    <a href="{{ route('store.products.edit', $product) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form action="{{ route('store.products.destroy', $product) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this product?')">Delete</button>
                                    </form>
                                </div>
                                <form action="{{ route('store.products.adjust-stock', $product) }}" method="POST" class="d-flex gap-2 justify-content-end">
                                    @csrf
                                    <select name="direction" class="form-select form-select-sm" style="max-width: 90px;">
                                        <option value="in">Add</option>
                                        <option value="out">Reduce</option>
                                    </select>
                                    <input type="number" name="quantity" min="1" class="form-control form-control-sm" placeholder="Qty" style="max-width: 90px;" required>
                                    <button type="submit" class="btn btn-sm btn-dark">Update</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">No products found for this store.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
            <div class="card-body border-top">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
