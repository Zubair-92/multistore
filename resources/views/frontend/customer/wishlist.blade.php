@extends('frontend.layouts.app')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h2 class="mb-1">My Wishlist</h2>
            <p class="text-muted mb-0">Keep track of products you want to revisit or purchase later.</p>
        </div>
        <a href="{{ route('frontend.products.index') }}" class="btn btn-outline-secondary">Browse Products</a>
    </div>

    <div class="row g-4 row-cols-1 row-cols-md-2 row-cols-xl-3">
        @forelse($products as $product)
            <div class="col">
                <div class="card h-100 border-0 shadow-sm">
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name ?? 'Product image' }}" style="height: 240px; object-fit: cover;">
                    @else
                        <div class="bg-light d-flex align-items-center justify-content-center text-muted" style="height: 240px;">No image</div>
                    @endif
                    <div class="card-body d-flex flex-column">
                        <div class="small text-uppercase text-muted fw-semibold mb-2">{{ $product->store->name ?? 'Store #'.$product->store_id }}</div>
                        <h3 class="h5 fw-bold mb-2">{{ $product->name ?? 'Product #'.$product->id }}</h3>
                        <p class="text-muted small mb-3">{{ $product->category->translation->category ?? 'General' }}</p>
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center mb-3 gap-2 flex-wrap">
                                <span class="fw-semibold">{{ number_format((float) ($product->offer_price ?: $product->price), 2) }} QAR</span>
                                <span class="badge bg-{{ $product->stock > 0 ? 'success' : 'danger' }}">
                                    {{ $product->stock > 0 ? 'In Stock' : 'Out of Stock' }}
                                </span>
                            </div>
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="{{ route('frontend.products.show', $product) }}" class="btn btn-dark flex-fill">View</a>
                                <form action="{{ route('wishlist.destroy', $product) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-outline-danger">Remove</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5 text-muted">
                        Your wishlist is empty right now.
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    @if($products->hasPages())
        <div class="mt-4">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection
