@extends('frontend.layouts.app')

@section('content')
<header class="marketplace-hero py-5">
    <div class="container px-4 px-lg-5 my-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-8 text-white">
                <span class="badge bg-warning text-dark mb-3">Approved Marketplace Stores</span>
                <h1 class="display-5 fw-bold mb-3">Browse stores and discover what each vendor offers.</h1>
                <p class="lead text-white-50 mb-0">Explore approved storefronts, compare product counts, and jump directly into each store catalog.</p>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm marketplace-surface-card">
                    <div class="card-body p-4">
                        <div class="small text-uppercase text-muted fw-semibold mb-2">Live Stores</div>
                        <div class="fs-3 fw-bold">{{ $stores->total() }}</div>
                        <div class="text-muted">approved stores matching your filters</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<section class="py-5">
    <div class="container px-4 px-lg-5">
        <div class="card shadow-sm border-0 mb-4 marketplace-surface-card">
            <div class="card-body">
                <form method="GET" action="{{ route('frontend.stores.index') }}" class="row g-3 align-items-end">
                    <div class="col-lg-5">
                        <label for="search" class="form-label">Search Stores</label>
                        <input id="search" type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Store name, description, or address">
                    </div>
                    <div class="col-lg-5">
                        <label for="category" class="form-label">Store Category</label>
                        <select id="category" name="category" class="form-select">
                            <option value="">All categories</option>
                            @foreach($storeCategories as $category)
                                <option value="{{ $category->id }}" @selected(($filters['category'] ?? null) == $category->id)>
                                    {{ $category->translation->store_category ?? 'Category #'.$category->id }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 d-grid">
                        <button type="submit" class="btn btn-dark">Apply</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-4 row-cols-1 row-cols-md-2 row-cols-xl-3">
            @forelse($stores as $store)
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm marketplace-surface-card">
                        <div class="card-body d-flex flex-column">
                            <div class="small text-uppercase text-muted fw-semibold mb-2">{{ $store->storeCategory->translation->store_category ?? 'General' }}</div>
                            <h2 class="h4 fw-bold mb-2">{{ $store->name ?? 'Store #'.$store->id }}</h2>
                            <p class="text-muted small mb-3">{{ $store->description ?? 'No store description yet.' }}</p>
                            <div class="small mb-2"><strong>Address:</strong> {{ $store->address ?? 'Not provided' }}</div>
                            <div class="small mb-4"><strong>Active Products:</strong> {{ $store->active_products_count }}</div>
                            <div class="mt-auto d-flex gap-2">
                                <a href="{{ route('frontend.stores.show', $store) }}" class="btn btn-dark flex-fill">Visit Store</a>
                                <a href="{{ route('frontend.products.index', ['store' => $store->id]) }}" class="btn btn-outline-secondary">Products</a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body py-5 text-center text-muted">No stores matched your filters.</div>
                    </div>
                </div>
            @endforelse
        </div>

        @if($stores->hasPages())
            <div class="mt-4">
                {{ $stores->links() }}
            </div>
        @endif
    </div>
</section>
@endsection
