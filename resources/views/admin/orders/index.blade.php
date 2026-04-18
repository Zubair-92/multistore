@extends('admin.layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">Orders</h1>
            <p class="text-muted mb-0">Track, review, export, and manage all store orders.</p>
        </div>
        <a href="{{ route('admin.orders.export', request()->query()) }}" class="btn btn-outline-dark">Export CSV</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card admin-kpi-card admin-kpi-primary h-100"><div class="card-body"><p class="text-white-50 mb-2">Orders</p><h3 class="mb-0">{{ $summary['orders_count'] }}</h3></div></div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card admin-kpi-card admin-kpi-success h-100"><div class="card-body"><p class="text-white-50 mb-2">Revenue</p><h3 class="mb-0">${{ number_format($summary['revenue_total'], 2) }}</h3></div></div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card admin-kpi-card admin-kpi-dark h-100"><div class="card-body"><p class="text-white-50 mb-2">Paid Orders</p><h3 class="mb-0">{{ $summary['paid_orders'] }}</h3></div></div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card admin-kpi-card admin-kpi-warm h-100"><div class="card-body"><p class="text-white-50 mb-2">Cancelled</p><h3 class="mb-0">{{ $summary['cancelled_orders'] }}</h3></div></div>
        </div>
    </div>

    <div class="card admin-shell-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.orders.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Order ID, customer name, email">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? null) === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Payment</label>
                    <select name="payment_status" class="form-select">
                        <option value="">All payment states</option>
                        @foreach($paymentStatuses as $paymentStatus)
                            <option value="{{ $paymentStatus }}" @selected(($filters['payment_status'] ?? null) === $paymentStatus)>{{ ucfirst($paymentStatus) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Store</label>
                    <select name="store_id" class="form-select">
                        <option value="">All stores</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}" @selected((string) ($filters['store_id'] ?? '') === (string) $store->id)>{{ $store->name ?? 'Store #'.$store->id }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label">From</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control">
                </div>
                <div class="col-md-1">
                    <label class="form-label">To</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button class="btn btn-dark w-100">Filter</button>
                </div>
                <div class="col-md-12 d-flex justify-content-end">
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card admin-shell-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Stores</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>#{{ $order->id }}</td>
                            <td>
                                <div>{{ $order->user->name ?? $order->user->email }}</div>
                                <small class="text-muted">{{ $order->user->email }}</small>
                            </td>
                            <td>{{ $order->items->pluck('store.name')->filter()->unique()->join(', ') ?: '-' }}</td>
                            <td>${{ number_format($order->total_amount, 2) }}</td>
                            <td>
                                <div>{{ $order->payment_method ? ucwords(str_replace('_', ' ', $order->payment_method)) : 'N/A' }}</div>
                                <small class="text-muted">{{ ucfirst($order->payment_status ?? 'unpaid') }}</small>
                            </td>
                            <td><span class="badge bg-{{ $order->statusBadgeClass() }}">{{ ucfirst($order->status) }}</span></td>
                            <td>{{ $order->created_at->format('d M Y, h:i A') }}</td>
                            <td class="text-end"><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-4 text-muted">No orders found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
            <div class="card-body border-top">{{ $orders->links() }}</div>
        @endif
    </div>
</div>
@endsection
