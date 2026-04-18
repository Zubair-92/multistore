@extends('frontend.layouts.app')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Order #{{ $order->id }}</h2>
            <p class="text-muted mb-0">Placed on {{ $order->created_at->format('d M, Y h:i A') }}</p>
        </div>
        <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary">Back to Profile</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card marketplace-surface-card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Items</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
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
            <div class="card marketplace-surface-card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="marketplace-metric-tile">
                                <div class="small text-uppercase text-muted">Status</div>
                                <div class="fw-semibold">{{ ucfirst($order->status) }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="marketplace-metric-tile">
                                <div class="small text-uppercase text-muted">Payment</div>
                                <div class="fw-semibold">{{ ucfirst($order->payment_status ?? 'unpaid') }}</div>
                            </div>
                        </div>
                    </div>
                    <p class="mb-2"><strong>Status:</strong> <span class="badge bg-{{ $order->statusBadgeClass() }}">{{ ucfirst($order->status) }}</span></p>
                    <p class="mb-2"><strong>Payment Method:</strong> {{ $order->payment_method ? ucwords(str_replace('_', ' ', $order->payment_method)) : 'N/A' }}</p>
                    <p class="mb-2"><strong>Payment Status:</strong> <span class="badge bg-{{ $order->paymentStatusBadgeClass() }}">{{ ucfirst($order->payment_status ?? 'unpaid') }}</span></p>
                    @if($order->transaction_id)
                        <p class="mb-2"><strong>Reference:</strong> {{ $order->transaction_id }}</p>
                    @endif
                    @if($order->payment_method === 'demo_card')
                        <div class="alert alert-info small py-2">
                            This order was paid with the sandbox demo gateway for presentation purposes.
                        </div>
                    @endif
                    <p class="mb-0"><strong>Total:</strong> ${{ number_format($order->total_amount, 2) }}</p>
                </div>
            </div>

            <div class="card marketplace-surface-card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Delivery Address</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Name:</strong> {{ $order->delivery_name ?? ($order->user->name ?? $order->user->email) }}</p>
                    <p class="mb-2"><strong>Email:</strong> {{ $order->delivery_email ?? $order->user->email }}</p>
                    <p class="mb-2"><strong>Phone:</strong> {{ $order->delivery_phone ?? 'Not Provided' }}</p>
                    <p class="mb-0"><strong>Address:</strong> {{ $order->delivery_address ?? ($order->user->address ?? 'Not Provided') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
