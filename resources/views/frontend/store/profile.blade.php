@extends('frontend.layouts.app')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Store Dashboard</h2>
            <p class="text-muted mb-0">Manage your store profile, monitor activity, and keep your catalog healthy.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('store.products') }}" class="btn btn-outline-dark">View Products</a>
            <a href="{{ route('store.orders') }}" class="btn btn-primary">View Orders</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card marketplace-product-card h-100 text-white" style="background: linear-gradient(135deg, #1d4ed8 0%, #4f46e5 100%);">
                <div class="card-body">
                    <p class="text-white-50 mb-2">Products</p>
                    <h3 class="mb-1">{{ $metrics['products_total'] }}</h3>
                    <small class="text-white-50">
                        {{ $metrics['products_active'] }} active, {{ $metrics['products_low_stock'] }} low stock
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card marketplace-product-card h-100 text-white" style="background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);">
                <div class="card-body">
                    <p class="text-white-50 mb-2">Orders</p>
                    <h3 class="mb-1">{{ $metrics['orders_total'] }}</h3>
                    <small class="text-white-50">{{ $metrics['orders_pending'] }} pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card marketplace-product-card h-100 text-white" style="background: linear-gradient(135deg, #b45309 0%, #f59e0b 100%);">
                <div class="card-body">
                    <p class="text-white-50 mb-2">Revenue</p>
                    <h3 class="mb-1">${{ number_format($metrics['revenue_total'], 2) }}</h3>
                    <small class="text-white-50">${{ number_format($metrics['revenue_this_month'], 2) }} this month</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card marketplace-product-card h-100 text-white" style="background: linear-gradient(135deg, #172033 0%, #364152 100%);">
                <div class="card-body">
                    <p class="text-white-50 mb-2">Approval</p>
                    @if($store->isApproved())
                        <h3 class="mb-1 text-success">Approved</h3>
                        <small class="text-white-50">Store is live for customers</small>
                    @else
                        <h3 class="mb-1 text-warning">Pending</h3>
                        <small class="text-white-50">Waiting for admin approval</small>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="card marketplace-surface-card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Edit Store Profile</h5>
                </div>
                <div class="card-body">
                    @php
                        $en = $store->translations->firstWhere('locale', 'en');
                        $ar = $store->translations->firstWhere('locale', 'ar');
                    @endphp

                    <form method="POST" action="{{ route('store.profile.update') }}">
                        @csrf
                        @method('PATCH')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Owner Name</label>
                                <input type="text" name="owner_name" value="{{ old('owner_name', auth()->user()->name) }}" class="form-control @error('owner_name') is-invalid @enderror">
                                @error('owner_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Owner Email</label>
                                <input type="email" name="owner_email" value="{{ old('owner_email', auth()->user()->email) }}" class="form-control @error('owner_email') is-invalid @enderror">
                                @error('owner_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Store Email</label>
                                <input type="email" name="store_email" value="{{ old('store_email', $store->email) }}" class="form-control @error('store_email') is-invalid @enderror">
                                @error('store_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" value="{{ old('phone', $store->phone) }}" class="form-control @error('phone') is-invalid @enderror">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Store Name (EN)</label>
                                <input type="text" name="name_en" value="{{ old('name_en', $en?->name) }}" class="form-control @error('name_en') is-invalid @enderror">
                                @error('name_en')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Store Name (AR)</label>
                                <input type="text" name="name_ar" value="{{ old('name_ar', $ar?->name) }}" class="form-control @error('name_ar') is-invalid @enderror">
                                @error('name_ar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Address (EN)</label>
                                <textarea name="addr_en" rows="3" class="form-control @error('addr_en') is-invalid @enderror">{{ old('addr_en', $en?->address) }}</textarea>
                                @error('addr_en')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Address (AR)</label>
                                <textarea name="addr_ar" rows="3" class="form-control @error('addr_ar') is-invalid @enderror">{{ old('addr_ar', $ar?->address) }}</textarea>
                                @error('addr_ar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Description (EN)</label>
                                <textarea name="desc_en" rows="3" class="form-control @error('desc_en') is-invalid @enderror">{{ old('desc_en', $en?->description) }}</textarea>
                                @error('desc_en')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Description (AR)</label>
                                <textarea name="desc_ar" rows="3" class="form-control @error('desc_ar') is-invalid @enderror">{{ old('desc_ar', $ar?->description) }}</textarea>
                                @error('desc_ar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 d-flex justify-content-end">
                                <button class="btn btn-success">Save Store Profile</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card marketplace-surface-card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Store Snapshot</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Category:</strong> {{ $store->storeCategory->translation->store_category ?? 'Not Assigned' }}</p>
                    <p class="mb-2"><strong>Created:</strong> {{ $store->created_at->format('d M, Y') }}</p>
                    <p class="mb-2"><strong>Current Name:</strong> {{ $store->name ?? 'Not Set' }}</p>
                    <p class="mb-0"><strong>Current Address:</strong> {{ $store->address ?? 'Not Set' }}</p>
                </div>
            </div>

            <div class="card marketplace-surface-card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Orders</h5>
                    <a href="{{ route('store.orders') }}" class="btn btn-sm btn-outline-primary">All Orders</a>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($recentOrders as $order)
                        <a href="{{ route('store.orders.show', $order) }}" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between">
                                <strong>#{{ $order->id }}</strong>
                                <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
                            </div>
                            <small class="text-muted">{{ $order->user->name ?? $order->user->email }}</small>
                        </a>
                    @empty
                        <div class="list-group-item text-muted">No recent orders.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card marketplace-surface-card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Low Stock Watchlist</h5>
                    <a href="{{ route('store.products') }}" class="btn btn-sm btn-outline-warning">Restock Products</a>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($lowStockProducts as $product)
                        <a href="{{ route('store.products.edit', $product) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold">{{ $product->name ?? 'Product #'.$product->id }}</div>
                                <small class="text-muted">${{ number_format((float) $product->price, 2) }}</small>
                            </div>
                            <span class="badge {{ $product->stock > 0 ? 'bg-warning text-dark' : 'bg-danger' }}">
                                {{ $product->stock }} left
                            </span>
                        </a>
                    @empty
                        <div class="list-group-item text-muted">No products are below the {{ $lowStockThreshold }}-item threshold.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card marketplace-surface-card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Products</h5>
                    <a href="{{ route('store.products') }}" class="btn btn-sm btn-outline-dark">View All Products</a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentProducts as $product)
                                <tr>
                                    <td>{{ $product->name ?? 'Product #'.$product->id }}</td>
                                    <td>${{ number_format((float) $product->price, 2) }}</td>
                                    <td>{{ $product->stock }}</td>
                                    <td>
                                        @if($product->status)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No products yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
