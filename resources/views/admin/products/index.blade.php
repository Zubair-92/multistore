@extends('admin.layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Products</h2>
            <p class="text-muted mb-0">Manage catalog inventory, monitor low stock, and adjust product quantities with history.</p>
        </div>
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">Add Product</a>
    </div>

    <div class="card admin-shell-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.products.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Product ID or name">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Store</label>
                    <select name="store_id" class="form-select">
                        <option value="">All stores</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}" @selected((string) ($filters['store_id'] ?? '') === (string) $store->id)>
                                {{ $store->name ?? 'Store #'.$store->id }}
                            </option>
                        @endforeach
                    </select>
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
                <div class="col-md-1 d-grid">
                    <button class="btn btn-dark">Apply</button>
                </div>
                <div class="col-12 d-flex justify-content-end">
                    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card admin-shell-card">
        <div class="table-responsive">
            <table class="table align-middle text-center mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Store</th>
                        <th>Category</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Last Movement</th>
                        <th>Status</th>
                        <th width="220">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        @php($en = $product->translations->where('locale','en')->first())
                        <tr>
                            <td>{{ $product->id }}</td>
                            <td>
                                @if ($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" width="60" class="rounded">
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $product->store->translation->name ?? '-' }}</td>
                            <td>{{ $product->category->translation->category ?? '-' }}</td>
                            <td>{{ $en->name ?? ($product->name ?? '-') }}</td>
                            <td>
                                {{ number_format($product->price, 2) }}
                                @if ($product->offer_price)
                                    <br><small class="text-danger">Offer: {{ number_format($product->offer_price, 2) }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="fw-semibold {{ $product->stock <= 5 ? 'text-danger' : '' }}">{{ $product->stock }}</span>
                                @if($product->stock <= 5)
                                    <br><small class="text-warning">Low stock</small>
                                @endif
                            </td>
                            <td>
                                @if($product->stockMovements->first())
                                    <small class="text-muted">
                                        {{ strtoupper($product->stockMovements->first()->direction) }} {{ $product->stockMovements->first()->quantity }}
                                        <br>{{ $product->stockMovements->first()->created_at->format('d M Y') }}
                                    </small>
                                @else
                                    <small class="text-muted">No history</small>
                                @endif
                            </td>
                            <td>
                                @if ($product->status == 1)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-2 justify-content-center mb-2">
                                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this product?')">Delete</button>
                                    </form>
                                </div>
                                <form action="{{ route('admin.products.adjust-stock', $product) }}" method="POST" class="d-flex gap-2 justify-content-center">
                                    @csrf
                                    <select name="direction" class="form-select form-select-sm" style="max-width: 80px;">
                                        <option value="in">Add</option>
                                        <option value="out">Reduce</option>
                                    </select>
                                    <input type="number" name="quantity" min="1" class="form-control form-control-sm" placeholder="Qty" style="max-width: 80px;" required>
                                    <button type="submit" class="btn btn-sm btn-dark">Update</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">No products found.</td>
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
