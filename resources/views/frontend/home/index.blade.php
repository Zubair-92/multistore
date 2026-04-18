@extends('frontend.layouts.app')

@section('content')
@php($meta = $pageMeta ?? [])

<header class="marketplace-hero py-5">
    <div class="container px-4 px-lg-5 my-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-7 text-white">
                <span class="badge marketplace-badge mb-3">{{ $meta['heroBadge'] ?? 'Marketplace' }}</span>
                <h1 class="display-5 fw-bold mb-3">{{ $meta['heroTitle'] ?? 'Marketplace Catalog' }}</h1>
                <p class="lead text-white-50 mb-4">{{ $meta['heroDescription'] ?? 'Browse the live marketplace catalog.' }}</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('frontend.products.index') }}" class="btn btn-light">Browse Products</a>
                    <a href="{{ route('frontend.stores.index') }}" class="btn btn-outline-light">Explore Stores</a>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm marketplace-surface-card">
                    <div class="card-body p-4">
                        <div class="small text-uppercase text-muted fw-semibold mb-2">{{ $meta['catalogLabel'] ?? 'Live Catalog' }}</div>
                        <div class="fs-3 fw-bold">{{ $products->total() }}</div>
                        <div class="text-muted">{{ $meta['catalogHelp'] ?? 'products matching your current filters' }}</div>
                        <div class="row g-3 mt-2">
                            <div class="col-6">
                                <div class="marketplace-metric-tile">
                                    <div class="small text-uppercase text-muted">Featured Deals</div>
                                    <div class="fw-bold fs-5">{{ $featuredDeals->count() }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="marketplace-metric-tile">
                                    <div class="small text-uppercase text-muted">Coupons Live</div>
                                    <div class="fw-bold fs-5">{{ $activeCoupons->count() }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<section class="py-5">
    <div class="container px-4 px-lg-5">
        @if($activeCoupons->isNotEmpty())
            <div class="card border-0 shadow-sm mb-4 bg-warning-subtle marketplace-surface-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h2 class="h4 mb-1">Marketplace Deals</h2>
                            <p class="text-muted mb-0">Apply these active coupon codes at checkout.</p>
                        </div>
                        <a href="{{ route('cart.index') }}" class="btn btn-dark btn-sm">Open Cart</a>
                    </div>

                    <div class="row g-3">
                        @foreach($activeCoupons as $coupon)
                            <div class="col-md-4">
                                <div class="card h-100 border-0 shadow-sm marketplace-surface-card">
                                    <div class="card-body">
                                        <div class="small text-uppercase text-muted fw-semibold mb-2">Coupon Code</div>
                                        <div class="h5 fw-bold mb-2">{{ $coupon->code }}</div>
                                        <div class="mb-2">
                                            {{ $coupon->type === 'percent' ? rtrim(rtrim(number_format((float) $coupon->value, 2), '0'), '.') . '% off' : number_format((float) $coupon->value, 2) . ' QAR off' }}
                                        </div>
                                        <div class="text-muted small">
                                            Minimum order {{ number_format((float) $coupon->minimum_amount, 2) }} QAR
                                        </div>
                                        @if($coupon->expires_at)
                                            <div class="text-muted small mt-1">Valid until {{ $coupon->expires_at->format('d M Y') }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if($featuredDeals->isNotEmpty())
            <div class="mb-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="h4 mb-1">Featured Deals</h2>
                        <p class="text-muted mb-0">Best live markdowns from approved stores.</p>
                    </div>
                    <a href="{{ route('frontend.products.index', ['sort' => 'price_low']) }}" class="btn btn-outline-secondary btn-sm">See All Products</a>
                </div>

                <div class="row gx-4 gy-4 row-cols-1 row-cols-md-2 row-cols-xl-4">
                    @foreach($featuredDeals as $product)
                        <div class="col">
                            <div class="card h-100 border-0 shadow-sm marketplace-product-card">
                                <div class="position-relative">
                                    @if($product->image)
                                        <img
                                            class="card-img-top"
                                            src="{{ asset('storage/' . $product->image) }}"
                                            alt="{{ $product->name ?? 'Product image' }}"
                                            style="height: 220px; object-fit: cover;"
                                        >
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center text-muted" style="height: 220px;">
                                            No image
                                        </div>
                                    @endif
                                    <span class="badge bg-danger position-absolute top-0 start-0 m-3">Deal</span>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <div class="small text-uppercase text-muted fw-semibold mb-2">
                                        {{ $product->store->name ?? 'Store #'.$product->store_id }}
                                    </div>
                                    <h3 class="h5 fw-bold mb-2">
                                        <a href="{{ route('frontend.products.show', $product) }}" class="text-dark text-decoration-none">
                                            {{ $product->name ?? 'Product #'.$product->id }}
                                        </a>
                                    </h3>
                                    <div class="mt-auto">
                                        <div class="d-flex align-items-center gap-2 mb-3">
                                            <span class="fs-5 fw-bold text-danger">{{ number_format((float) $product->offer_price, 2) }} QAR</span>
                                            <span class="text-muted text-decoration-line-through">{{ number_format((float) $product->price, 2) }} QAR</span>
                                        </div>
                                        <a href="{{ route('frontend.products.show', $product) }}" class="btn btn-outline-dark w-100">View Deal</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="card shadow-sm border-0 mb-4 marketplace-surface-card">
            <div class="card-body">
                <form method="GET" action="{{ $meta['filterAction'] ?? route('frontend.home') }}" class="row g-3 align-items-end">
                    <div class="col-lg-4">
                        <label for="search" class="form-label">Search</label>
                        <input
                            id="search"
                            type="text"
                            name="search"
                            value="{{ $filters['search'] ?? '' }}"
                            class="form-control"
                            placeholder="Product name or description"
                        >
                    </div>

                    <div class="col-md-4 col-lg-2">
                        <label for="category" class="form-label">Category</label>
                        <select id="category" name="category" class="form-select">
                            <option value="">All categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(($filters['category'] ?? null) == $category->id)>
                                    {{ $category->translation->category ?? 'Category #'.$category->id }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 col-lg-2">
                        <label for="store" class="form-label">Store</label>
                        <select id="store" name="store" class="form-select">
                            <option value="">All stores</option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}" @selected(($filters['store'] ?? null) == $store->id)>
                                    {{ $store->name ?? 'Store #'.$store->id }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 col-lg-2">
                        <label for="sort" class="form-label">Sort</label>
                        <select id="sort" name="sort" class="form-select">
                            <option value="latest" @selected(($filters['sort'] ?? 'latest') === 'latest')>Latest</option>
                            <option value="price_low" @selected(($filters['sort'] ?? null) === 'price_low')>Price Low to High</option>
                            <option value="price_high" @selected(($filters['sort'] ?? null) === 'price_high')>Price High to Low</option>
                            <option value="name" @selected(($filters['sort'] ?? null) === 'name')>Name</option>
                        </select>
                    </div>

                    <div class="col-md-6 col-lg-1">
                        <div class="form-check mt-md-4 pt-md-2">
                            <input
                                id="stock"
                                type="checkbox"
                                name="stock"
                                value="in_stock"
                                class="form-check-input"
                                @checked(($filters['stock'] ?? null) === 'in_stock')
                            >
                            <label for="stock" class="form-check-label">In stock</label>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-1 d-grid">
                        <button type="submit" class="btn btn-dark">Apply</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 mb-1">{{ $meta['catalogTitle'] ?? 'Catalog' }}</h2>
                <p class="text-muted mb-0">{{ $meta['catalogDescription'] ?? 'Only active products from approved stores are shown here.' }}</p>
            </div>
            @if(request()->query())
                <a href="{{ $meta['filterAction'] ?? route('frontend.home') }}" class="btn btn-outline-secondary">Clear Filters</a>
            @endif
        </div>

        <div class="row gx-4 gy-4 row-cols-1 row-cols-md-2 row-cols-xl-4">
            @forelse($products as $product)
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm marketplace-product-card">
                        <div class="position-relative">
                            @if($product->image)
                                <img
                                    class="card-img-top"
                                    src="{{ asset('storage/' . $product->image) }}"
                                    alt="{{ $product->name ?? 'Product image' }}"
                                    style="height: 220px; object-fit: cover;"
                                >
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center text-muted" style="height: 220px;">
                                    No image
                                </div>
                            @endif

                            @if($product->offer_price)
                                <span class="badge bg-danger position-absolute top-0 start-0 m-3">On Offer</span>
                            @endif
                        </div>

                        <div class="card-body d-flex flex-column">
                            <div class="small text-uppercase text-muted fw-semibold mb-2">
                                <a href="{{ route('frontend.stores.show', $product->store) }}" class="text-muted text-decoration-none">
                                    {{ $product->store->name ?? 'Store #'.$product->store_id }}
                                </a>
                            </div>
                            <h3 class="h5 fw-bold mb-2">
                                <a href="{{ route('frontend.products.show', $product) }}" class="text-dark text-decoration-none">
                                    {{ $product->name ?? 'Product #'.$product->id }}
                                </a>
                            </h3>
                            <p class="text-muted small mb-3">
                                {{ $product->category->translation->category ?? 'General' }}
                                @if($product->stock <= 0)
                                    <span class="text-danger d-block mt-1">Currently out of stock</span>
                                @elseif($product->stock <= 5)
                                    <span class="text-warning d-block mt-1">Low stock: {{ $product->stock }} left</span>
                                @endif
                            </p>

                            <div class="mt-auto">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    @if($product->offer_price)
                                        <span class="fs-5 fw-bold text-danger">{{ number_format((float) $product->offer_price, 2) }} QAR</span>
                                        <span class="text-muted text-decoration-line-through">{{ number_format((float) $product->price, 2) }} QAR</span>
                                    @else
                                        <span class="fs-5 fw-bold">{{ number_format((float) $product->price, 2) }} QAR</span>
                                    @endif
                                </div>
                                <div class="small text-muted mb-3">
                                    {{ $product->reviews->count() }} review{{ $product->reviews->count() === 1 ? '' : 's' }}
                                    @if($product->reviews->count() > 0)
                                        • {{ number_format((float) $product->reviews->avg('rating'), 1) }}/5
                                    @endif
                                </div>

                                <div class="d-flex gap-2">
                                    <button
                                        type="button"
                                        class="btn btn-dark flex-fill add-to-cart-btn"
                                        data-product-id="{{ $product->id }}"
                                        @disabled($product->stock <= 0)
                                    >
                                        {{ $product->stock > 0 ? 'Add To Cart' : 'Out of Stock' }}
                                    </button>
                                    <a href="{{ route('frontend.products.show', $product) }}" class="btn btn-outline-secondary">View</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card border-0 shadow-sm marketplace-surface-card">
                        <div class="card-body py-5 text-center">
                            <h3 class="h5 mb-2">No products matched your filters.</h3>
                            <p class="text-muted mb-0">Try clearing filters or searching with a different keyword.</p>
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

        @if($recentlyViewedProducts->isNotEmpty())
            <div class="mt-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h2 class="h4 mb-1">Recently Viewed</h2>
                        <p class="text-muted mb-0">Pick up where you left off.</p>
                    </div>
                </div>

                <div class="row gx-4 gy-4 row-cols-1 row-cols-md-2 row-cols-xl-4">
                    @foreach($recentlyViewedProducts as $product)
                        <div class="col">
                            <div class="card h-100 border-0 shadow-sm marketplace-product-card">
                                @if($product->image)
                                    <img
                                        class="card-img-top"
                                        src="{{ asset('storage/' . $product->image) }}"
                                        alt="{{ $product->name ?? 'Product image' }}"
                                        style="height: 220px; object-fit: cover;"
                                    >
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center text-muted" style="height: 220px;">
                                        No image
                                    </div>
                                @endif
                                <div class="card-body d-flex flex-column">
                                    <div class="small text-uppercase text-muted fw-semibold mb-2">
                                        {{ $product->store->name ?? 'Store #'.$product->store_id }}
                                    </div>
                                    <h3 class="h5 fw-bold mb-2">
                                        <a href="{{ route('frontend.products.show', $product) }}" class="text-dark text-decoration-none">
                                            {{ $product->name ?? 'Product #'.$product->id }}
                                        </a>
                                    </h3>
                                    <div class="mt-auto d-flex justify-content-between align-items-center gap-2">
                                        <span class="fw-semibold">{{ number_format((float) ($product->offer_price ?: $product->price), 2) }} QAR</span>
                                        <a href="{{ route('frontend.products.show', $product) }}" class="btn btn-sm btn-outline-secondary">View Again</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
@endsection
