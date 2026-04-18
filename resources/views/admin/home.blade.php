@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">Dashboard</h1>
            <p class="text-muted mb-0">Operational snapshot of your multistore platform.</p>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-dark">Manage Orders</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card admin-kpi-card admin-kpi-primary h-100">
                <div class="card-body">
                    <p class="text-white-50 mb-2">Total Stores</p>
                    <h3 class="mb-1">{{ $metrics['stores_total'] }}</h3>
                    <small class="text-white-50">{{ $metrics['stores_approved'] }} approved</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card admin-kpi-card admin-kpi-warm h-100">
                <div class="card-body">
                    <p class="text-white-50 mb-2">Products</p>
                    <h3 class="mb-1">{{ $metrics['products_total'] }}</h3>
                    <small class="text-white-50">
                        {{ $metrics['products_low_stock'] }} at or below {{ $lowStockThreshold }} stock
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card admin-kpi-card admin-kpi-success h-100">
                <div class="card-body">
                    <p class="text-white-50 mb-2">Customers</p>
                    <h3 class="mb-1">{{ $metrics['customers_total'] }}</h3>
                    <small class="text-white-50">Registered buyers</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card admin-kpi-card admin-kpi-dark h-100">
                <div class="card-body">
                    <p class="text-white-50 mb-2">Pending Orders</p>
                    <h3 class="mb-1">{{ $metrics['orders_pending'] }}</h3>
                    <small class="text-white-50">{{ $metrics['orders_total'] }} total orders</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card admin-shell-card h-100">
                <div class="card-body">
                    <p class="text-muted mb-2">Total Revenue</p>
                    <h2 class="mb-1">${{ number_format($metrics['revenue_total'], 2) }}</h2>
                    <small class="text-muted">Excluding cancelled orders</small>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card admin-shell-card h-100">
                <div class="card-body">
                    <p class="text-muted mb-2">Revenue This Month</p>
                    <h2 class="mb-1">${{ number_format($metrics['revenue_this_month'], 2) }}</h2>
                    <small class="text-muted">Current calendar month</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card admin-shell-card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Recent Orders</h5>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-dark">View All</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Stores</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                        <tr>
                            <td>#{{ $order->id }}</td>
                            <td>{{ $order->user->name ?? $order->user->email }}</td>
                            <td>{{ $order->items->pluck('store.name')->filter()->unique()->join(', ') ?: '-' }}</td>
                            <td>${{ number_format($order->total_amount, 2) }}</td>
                            <td><span class="badge bg-secondary">{{ ucfirst($order->status) }}</span></td>
                            <td>{{ $order->created_at->format('d M Y, h:i A') }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No orders yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-lg-6">
            <div class="card admin-shell-card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Low Stock Watchlist</h5>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($lowStockProducts as $product)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold">{{ $product->name ?? 'Product #'.$product->id }}</div>
                                <small class="text-muted">{{ $product->store->name ?? 'Store #'.$product->store_id }}</small>
                            </div>
                            <span class="badge {{ $product->stock > 0 ? 'bg-warning text-dark' : 'bg-danger' }}">
                                {{ $product->stock }} left
                            </span>
                        </div>
                    @empty
                        <div class="list-group-item text-muted">No products need stock attention right now.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card admin-shell-card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Top Stores By Revenue</h5>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($topStores as $storePerformance)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold">{{ $storePerformance->store->name ?? 'Store #'.$storePerformance->store_id }}</div>
                                <small class="text-muted">{{ (int) $storePerformance->items_sold }} items sold</small>
                            </div>
                            <span class="fw-semibold">${{ number_format((float) $storePerformance->revenue_total, 2) }}</span>
                        </div>
                    @empty
                        <div class="list-group-item text-muted">Revenue rankings will appear after stores start receiving orders.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
