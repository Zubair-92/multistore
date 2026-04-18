@extends('frontend.layouts.app')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h2 class="mb-1">Store Orders</h2>
            <p class="text-muted mb-0">Review order performance, filter results, and export store-specific order data.</p>
        </div>
        <a href="{{ route('store.orders.export', request()->query()) }}" class="btn btn-outline-dark">Export CSV</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3"><div class="card marketplace-product-card h-100 text-white" style="background: linear-gradient(135deg, #1d4ed8 0%, #4f46e5 100%);"><div class="card-body"><p class="text-white-50 mb-2">Orders</p><h3 class="mb-0">{{ $summary['orders_count'] }}</h3></div></div></div>
        <div class="col-md-6 col-xl-3"><div class="card marketplace-product-card h-100 text-white" style="background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);"><div class="card-body"><p class="text-white-50 mb-2">Revenue</p><h3 class="mb-0">${{ number_format($summary['revenue_total'], 2) }}</h3></div></div></div>
        <div class="col-md-6 col-xl-3"><div class="card marketplace-product-card h-100 text-white" style="background: linear-gradient(135deg, #172033 0%, #364152 100%);"><div class="card-body"><p class="text-white-50 mb-2">Paid Orders</p><h3 class="mb-0">{{ $summary['paid_orders'] }}</h3></div></div></div>
        <div class="col-md-6 col-xl-3"><div class="card marketplace-product-card h-100 text-white" style="background: linear-gradient(135deg, #b45309 0%, #f59e0b 100%);"><div class="card-body"><p class="text-white-50 mb-2">Pending Orders</p><h3 class="mb-0">{{ $summary['pending_orders'] }}</h3></div></div></div>
    </div>

    <div class="card marketplace-surface-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('store.orders') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Order ID, customer name, email">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        @foreach(\App\Models\Order::STATUSES as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? null) === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Payment</label>
                    <select name="payment_status" class="form-select">
                        <option value="">All payment states</option>
                        @foreach(\App\Models\Order::PAYMENT_STATUSES as $paymentStatus)
                            <option value="{{ $paymentStatus }}" @selected(($filters['payment_status'] ?? null) === $paymentStatus)>{{ ucfirst($paymentStatus) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">From</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control">
                </div>
                <div class="col-12 d-flex justify-content-end gap-2 flex-wrap">
                    <a href="{{ route('store.orders') }}" class="btn btn-outline-secondary">Reset</a>
                    <button class="btn btn-dark">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card marketplace-surface-card">
        <div class="card-header bg-info text-white"><h5 class="mb-0">Orders Received</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            @php($storeTotal = $order->items->where('store_id', auth()->user()->store_id)->sum(fn ($item) => $item->price * $item->quantity))
                            <tr>
                                <td>#{{ $order->id }}</td>
                                <td>{{ $order->created_at->format('d M, Y') }}</td>
                                <td>{{ $order->user->name ?? $order->user->email }}</td>
                                <td>${{ number_format($storeTotal, 2) }}</td>
                                <td><small>{{ ucfirst($order->payment_status ?? 'unpaid') }}</small></td>
                                <td><span class="badge bg-{{ $order->statusBadgeClass() }}">{{ ucfirst($order->status) }}</span></td>
                                <td><a href="{{ route('store.orders.show', $order) }}" class="btn btn-sm btn-primary">View Order</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-4">No orders found for this store.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($orders->hasPages())
        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection
