@extends('frontend.layouts.app')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h2 class="mb-1">Order #{{ $order->id }}</h2>
            <p class="text-muted mb-0">Placed on {{ $order->created_at->format('d M, Y h:i A') }}</p>
        </div>
        <a href="{{ route('store.orders') }}" class="btn btn-outline-secondary">Back to Orders</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card marketplace-surface-card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Store Items In This Order</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php($storeItems = $order->items->where('store_id', $storeId))

                                @foreach($storeItems as $item)
                                    <tr>
                                        <td>{{ $item->product_name ?: ($item->product->name ?? 'Product #'.$item->product_id) }}</td>
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
        </div>

        <div class="col-lg-4">
            <div class="card marketplace-surface-card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Customer</h5>
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
                    <p class="mb-2"><strong>Name:</strong> {{ $order->delivery_name ?? ($order->user->name ?? $order->user->email) }}</p>
                    <p class="mb-2"><strong>Email:</strong> {{ $order->delivery_email ?? $order->user->email }}</p>
                    <p class="mb-2"><strong>Phone:</strong> {{ $order->delivery_phone ?? 'Not Provided' }}</p>
                    <p class="mb-0"><strong>Address:</strong> {{ $order->delivery_address ?? 'Not Provided' }}</p>
                </div>
            </div>

            <div class="card marketplace-surface-card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Update Order Status</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('store.orders.update', $order) }}">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}" @selected($order->status === $status)>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="small text-muted mb-3">
                            Payment: {{ $order->payment_method ? ucwords(str_replace('_', ' ', $order->payment_method)) : 'N/A' }}
                            ({{ ucfirst($order->payment_status ?? 'unpaid') }})
                        </div>

                        @if($order->payment_method === 'demo_card')
                            <div class="alert alert-info small py-2">
                                This order used the sandbox demo gateway and is marked paid automatically.
                            </div>
                        @endif

                        <button class="btn btn-primary w-100">Save Status</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
