@extends('frontend.layouts.app')

@section('content')
<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h2 class="mb-1">My Account</h2>
            <p class="text-muted mb-0">Manage your profile and track your orders.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap justify-content-end">
            <span class="badge bg-light text-dark border">{{ $orders->total() }} orders</span>
            <span class="badge bg-light text-dark border">{{ $wishlistProducts->total() }} wishlist items</span>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-lg-5">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Profile Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <strong>Name:</strong>
                            <p class="mb-0">{{ auth()->user()->name }}</p>
                        </div>

                        <div class="col-12 mb-3">
                            <strong>Email:</strong>
                            <p class="mb-0">{{ auth()->user()->email }}</p>
                        </div>

                        <div class="col-12">
                            <strong>Address:</strong>
                            <p class="mb-0">{{ auth()->user()->address ?? 'Not Provided' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Edit Profile</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('PATCH')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Name</label>
                                <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" class="form-control @error('name') is-invalid @enderror">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" class="form-control @error('email') is-invalid @enderror">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea name="address" rows="3" class="form-control @error('address') is-invalid @enderror">{{ old('address', auth()->user()->address) }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 d-flex justify-content-end">
                                <button class="btn btn-primary">Save Changes</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <h4 class="mb-0">Saved Products</h4>
        <a href="{{ route('wishlist.index') }}" class="btn btn-sm btn-outline-secondary">Open Wishlist</a>
    </div>

    <div class="row g-4 mb-4">
        @forelse($wishlistProducts as $product)
            <div class="col-md-6 col-xl-4">
                <div class="card shadow-sm h-100">
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name ?? 'Product image' }}" style="height: 220px; object-fit: cover;">
                    @else
                        <div class="bg-light d-flex align-items-center justify-content-center text-muted" style="height: 220px;">No image</div>
                    @endif
                    <div class="card-body d-flex flex-column">
                        <div class="small text-muted text-uppercase fw-semibold mb-2">{{ $product->store->name ?? 'Store #'.$product->store_id }}</div>
                        <h5 class="mb-2">{{ $product->name ?? 'Product #'.$product->id }}</h5>
                        <p class="text-muted small mb-3">{{ $product->category->translation->category ?? 'General' }}</p>
                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">{{ number_format((float) ($product->offer_price ?: $product->price), 2) }} QAR</span>
                            <a href="{{ route('frontend.products.show', $product) }}" class="btn btn-sm btn-outline-primary">View</a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body text-center py-4 text-muted">
                        No saved products yet. Browse the marketplace and save products you want to revisit.
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    @if($wishlistProducts->hasPages())
        <div class="mb-5">
            {{ $wishlistProducts->links() }}
        </div>
    @endif

    <h4 class="mb-3">My Orders</h4>

    <div class="card shadow-sm">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">Order History</h5>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th># Order ID</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($orders as $order)
                            <tr>
                                <td>{{ $order->id }}</td>
                                <td>{{ $order->created_at->format('d M, Y') }}</td>
                                <td>${{ number_format($order->total_amount, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $order->statusBadgeClass() }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('profile.orders.show', $order) }}" class="btn btn-sm btn-primary">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">No orders found.</td>
                            </tr>
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
