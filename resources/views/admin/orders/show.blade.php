@extends('admin.layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">Order #{{ $order->id }}</h1>
            <p class="text-muted mb-0">Placed on {{ $order->created_at->format('d M Y, h:i A') }}</p>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">Back to Orders</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card admin-shell-card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Items</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Store</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td>{{ $item->product_name ?: ($item->product->name ?? 'Product #'.$item->product_id) }}</td>
                                    <td>{{ $item->store->name ?? 'Store #'.$item->store_id }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>${{ number_format($item->price, 2) }}</td>
                                    <td>${{ number_format($item->price * $item->quantity, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card admin-shell-card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="admin-kpi-card admin-kpi-primary">
                                <div class="card-body py-3">
                                    <div class="small text-white-50">Order Status</div>
                                    <div class="fw-semibold">{{ ucfirst($order->status) }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="admin-kpi-card admin-kpi-success">
                                <div class="card-body py-3">
                                    <div class="small text-white-50">Payment</div>
                                    <div class="fw-semibold">{{ ucfirst($order->payment_status ?? 'unpaid') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="mb-2"><strong>Customer:</strong> {{ $order->user->name ?? $order->user->email }}</p>
                    <p class="mb-2"><strong>Email:</strong> {{ $order->user->email }}</p>
                    <p class="mb-2"><strong>Payment Method:</strong> {{ $order->payment_method ? ucwords(str_replace('_', ' ', $order->payment_method)) : 'N/A' }}</p>
                    <p class="mb-2"><strong>Payment Status:</strong> <span class="badge bg-{{ $order->paymentStatusBadgeClass() }}">{{ ucfirst($order->payment_status ?? 'unpaid') }}</span></p>
                    <p class="mb-2"><strong>Delivery Name:</strong> {{ $order->delivery_name ?? ($order->user->name ?? '-') }}</p>
                    <p class="mb-2"><strong>Delivery Phone:</strong> {{ $order->delivery_phone ?? 'N/A' }}</p>
                    <p class="mb-2"><strong>Delivery Address:</strong> {{ $order->delivery_address ?? 'N/A' }}</p>
                    @if($order->customer_note)
                        <p class="mb-2"><strong>Customer Note:</strong> {{ $order->customer_note }}</p>
                    @endif
                    @if($order->transaction_id)
                        <p class="mb-2"><strong>Transaction Reference:</strong> {{ $order->transaction_id }}</p>
                    @endif
                    @if($order->payment_method === 'demo_card')
                        <div class="alert alert-info small py-2">
                            Sandbox demo payment. No real money was processed for this order.
                        </div>
                    @endif
                    <p class="mb-0"><strong>Total:</strong> ${{ number_format($order->total_amount, 2) }}</p>
                </div>
            </div>

            <div class="card admin-shell-card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Update Status</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.orders.update', $order) }}">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label class="form-label">Current status</label>
                            <select name="status" class="form-select">
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}" @selected($order->status === $status)>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Payment status</label>
                            <select name="payment_status" class="form-select">
                                @foreach(\App\Models\Order::PAYMENT_STATUSES as $paymentStatus)
                                    <option value="{{ $paymentStatus }}" @selected(($order->payment_status ?? 'unpaid') === $paymentStatus)>{{ ucfirst($paymentStatus) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Transaction reference</label>
                            <input type="text" name="transaction_id" value="{{ old('transaction_id', $order->transaction_id) }}" class="form-control">
                        </div>

                        <button class="btn btn-dark w-100">Save Status</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
