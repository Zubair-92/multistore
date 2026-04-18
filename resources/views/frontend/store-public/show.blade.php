@extends('frontend.layouts.app')

@section('content')
<header class="marketplace-hero py-5">
    <div class="container px-4 px-lg-5 my-4">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8 text-white">
                <div class="small text-uppercase text-warning fw-semibold mb-2">{{ $store->storeCategory->translation->store_category ?? 'Marketplace Store' }}</div>
                <h1 class="display-5 fw-bold mb-3">{{ $store->name ?? 'Store #'.$store->id }}</h1>
                <p class="lead text-white-50 mb-3">{{ $store->description ?? 'Explore this store\'s active marketplace products.' }}</p>
                <div class="text-white-50">{{ $store->address ?? 'Address not provided' }}</div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm marketplace-surface-card">
                    <div class="card-body p-4">
                        <div class="small text-uppercase text-muted fw-semibold mb-2">Store Catalog</div>
                        <div class="fs-3 fw-bold">{{ $products->total() }}</div>
                        <div class="text-muted">active products currently available</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<section class="py-5">
    <div class="container px-4 px-lg-5">
        <div class="row g-4 mb-5">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100 marketplace-surface-card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Store Details</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>Email:</strong> {{ $store->email }}</p>
                        <p class="mb-2"><strong>Phone:</strong> {{ $store->phone }}</p>
                        <p class="mb-0"><strong>Category:</strong> {{ $store->storeCategory->translation->store_category ?? 'General' }}</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100 marketplace-surface-card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Featured Products</h5>
                        <a href="{{ route('frontend.products.index', ['store' => $store->id]) }}" class="btn btn-sm btn-outline-dark">Browse In Catalog</a>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 row-cols-1 row-cols-md-2">
                            @forelse($featuredProducts as $product)
                                <div class="col">
                                    <div class="border rounded p-3 h-100">
                                        <div class="fw-semibold">{{ $product->name ?? 'Product #'.$product->id }}</div>
                                        <div class="text-muted small mb-2">{{ number_format((float) ($product->offer_price ?: $product->price), 2) }} QAR</div>
                                        <a href="{{ route('frontend.products.show', $product) }}" class="btn btn-sm btn-outline-primary">View Product</a>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-muted">No featured products available right now.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 mb-1">Products From This Store</h2>
                <p class="text-muted mb-0">Only active products from this approved store are shown.</p>
            </div>
            <a href="{{ route('frontend.home') }}" class="btn btn-outline-secondary">Back to Marketplace</a>
        </div>

        <div class="row g-4 row-cols-1 row-cols-md-2 row-cols-xl-4">
            @forelse($products as $product)
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm marketplace-product-card">
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name ?? 'Product image' }}" style="height: 220px; object-fit: cover;">
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center text-muted" style="height: 220px;">No image</div>
                        @endif
                        <div class="card-body d-flex flex-column">
                            <div class="small text-uppercase text-muted fw-semibold mb-2">{{ $product->category->translation->category ?? 'General' }}</div>
                            <h3 class="h5 fw-bold mb-2">{{ $product->name ?? 'Product #'.$product->id }}</h3>
                            <p class="text-muted small mb-3">
                                @if($product->stock > 0)
                                    {{ $product->stock }} available
                                @else
                                    Out of stock
                                @endif
                            </p>
                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                <span class="fw-semibold">{{ number_format((float) ($product->offer_price ?: $product->price), 2) }} QAR</span>
                                <a href="{{ route('frontend.products.show', $product) }}" class="btn btn-sm btn-outline-dark">View</a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card border-0 shadow-sm marketplace-surface-card">
                        <div class="card-body text-center py-5 text-muted">This store has no active products yet.</div>
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
</section>
@endsection
