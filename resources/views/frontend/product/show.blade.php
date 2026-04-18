@extends('frontend.layouts.app')

@section('content')
@php($averageRating = round((float) $product->reviews->avg('rating'), 1))

<div class="container py-5">
    <div class="mb-4 d-flex flex-wrap gap-2">
        <a href="{{ route('frontend.products.index') }}" class="btn btn-outline-secondary btn-sm">Back to Products</a>
        <a href="{{ route('frontend.stores.show', $product->store) }}" class="btn btn-outline-secondary btn-sm">Visit Store</a>
    </div>

    <div class="row g-4 align-items-start">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm overflow-hidden marketplace-surface-card">
                @if($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name ?? 'Product image' }}" style="width: 100%; height: 420px; object-fit: cover;">
                @else
                    <div class="bg-light d-flex align-items-center justify-content-center text-muted" style="height: 420px;">No image available</div>
                @endif
            </div>
        </div>

        <div class="col-lg-6">
            <div class="mb-3 text-uppercase small fw-semibold text-muted">{{ $product->category->translation->category ?? 'General' }}</div>
            <h1 class="display-6 fw-bold mb-3">{{ $product->name ?? 'Product #'.$product->id }}</h1>
            <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                <div class="fw-semibold">
                    @if($product->reviews->isNotEmpty())
                        {{ number_format($averageRating, 1) }}/5 rating
                    @else
                        No ratings yet
                    @endif
                </div>
                <div class="text-warning">
                    @for($star = 1; $star <= 5; $star++)
                        {!! $star <= round($averageRating) ? '&#9733;' : '&#9734;' !!}
                    @endfor
                </div>
                <div class="text-muted small">{{ $product->reviews->count() }} reviews</div>
            </div>
            <p class="text-muted mb-4">{{ $product->translations->firstWhere('locale', app()->getLocale())?->description ?? $product->translations->first()?->description ?? 'No description available yet.' }}</p>

            <div class="card border-0 shadow-sm mb-4 marketplace-surface-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            @if($product->offer_price)
                                <div class="fs-3 fw-bold text-danger">{{ number_format((float) $product->offer_price, 2) }} QAR</div>
                                <div class="text-muted text-decoration-line-through">{{ number_format((float) $product->price, 2) }} QAR</div>
                            @else
                                <div class="fs-3 fw-bold">{{ number_format((float) $product->price, 2) }} QAR</div>
                            @endif
                        </div>
                        <span class="badge bg-{{ $product->stock > 0 ? ($product->stock <= 5 ? 'warning text-dark' : 'success') : 'danger' }}">
                            @if($product->stock > 0)
                                {{ $product->stock }} in stock
                            @else
                                Out of stock
                            @endif
                        </span>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-sm-4">
                            <div class="marketplace-metric-tile">
                                <div class="small text-uppercase text-muted">Store</div>
                                <div class="fw-semibold">{{ $product->store->name ?? 'Store' }}</div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="marketplace-metric-tile">
                                <div class="small text-uppercase text-muted">Category</div>
                                <div class="fw-semibold">{{ $product->category->translation->category ?? 'General' }}</div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="marketplace-metric-tile">
                                <div class="small text-uppercase text-muted">Availability</div>
                                <div class="fw-semibold">{{ $product->stock > 0 ? 'Ready to order' : 'Currently unavailable' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-dark add-to-cart-btn" data-product-id="{{ $product->id }}" @disabled($product->stock <= 0)>
                            {{ $product->stock > 0 ? 'Add To Cart' : 'Unavailable' }}
                        </button>
                        @auth('web')
                            @php($wishlisted = auth('web')->user()->wishlistItems()->where('product_id', $product->id)->exists())
                            <form action="{{ $wishlisted ? route('wishlist.destroy', $product) : route('wishlist.store', $product) }}" method="POST">
                                @csrf
                                @if($wishlisted)
                                    @method('DELETE')
                                @endif
                                <button class="btn btn-outline-danger w-100">
                                    {{ $wishlisted ? 'Remove From Wishlist' : 'Save To Wishlist' }}
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-outline-danger">Login to Save Wishlist</a>
                        @endauth
                        <a href="{{ route('frontend.stores.show', $product->store) }}" class="btn btn-outline-primary">Visit {{ $product->store->name ?? 'Store' }}</a>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm marketplace-surface-card">
                <div class="card-body">
                    <h5 class="mb-3">Sold By</h5>
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <div class="fw-semibold">{{ $product->store->name ?? 'Store #'.$product->store_id }}</div>
                            <div class="text-muted small">{{ $product->store->storeCategory->translation->store_category ?? 'General Store' }}</div>
                            <div class="text-muted small mt-2">{{ $product->store->description ?? 'This store is part of the marketplace catalog.' }}</div>
                        </div>
                        <a href="{{ route('frontend.stores.show', $product->store) }}" class="btn btn-sm btn-outline-dark">Open Store</a>
                    </div>
                </div>
            </div>

            @if($activeCoupons->isNotEmpty())
                <div class="card border-0 shadow-sm mt-4 bg-warning-subtle marketplace-surface-card">
                    <div class="card-body">
                        <h5 class="mb-3">Available Coupons</h5>
                        <div class="row g-3">
                            @foreach($activeCoupons as $coupon)
                                <div class="col-md-6">
                                    <div class="border rounded p-3 h-100 bg-white">
                                        <div class="fw-bold">{{ $coupon->code }}</div>
                                        <div class="small text-muted">
                                            {{ $coupon->type === 'percent' ? rtrim(rtrim(number_format((float) $coupon->value, 2), '0'), '.') . '% off' : number_format((float) $coupon->value, 2) . ' QAR off' }}
                                            on orders above {{ number_format((float) $coupon->minimum_amount, 2) }} QAR
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="mt-5">
        <div class="row g-4 mb-5">
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm h-100 marketplace-surface-card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Customer Reviews</h5>
                    </div>
                    <div class="card-body">
                        <div class="display-6 fw-bold">{{ $product->reviews->isNotEmpty() ? number_format($averageRating, 1) : '0.0' }}</div>
                        <div class="text-warning fs-5 mb-2">
                            @for($star = 1; $star <= 5; $star++)
                                {!! $star <= round($averageRating) ? '&#9733;' : '&#9734;' !!}
                            @endfor
                        </div>
                        <div class="text-muted">{{ $product->reviews->count() }} verified customer reviews</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card border-0 shadow-sm h-100 marketplace-surface-card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Write a Review</h5>
                    </div>
                    <div class="card-body">
                        @auth('web')
                            @if($canReview)
                                <form method="POST" action="{{ route('products.reviews.store', $product) }}">
                                    @csrf
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Rating</label>
                                            <select name="rating" class="form-select @error('rating') is-invalid @enderror" required>
                                                <option value="">Choose</option>
                                                @for($rating = 5; $rating >= 1; $rating--)
                                                    <option value="{{ $rating }}" @selected(old('rating', $userReview?->rating) == $rating)>{{ $rating }} Star{{ $rating > 1 ? 's' : '' }}</option>
                                                @endfor
                                            </select>
                                            @error('rating')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-md-8">
                                            <label class="form-label">Title</label>
                                            <input type="text" name="title" value="{{ old('title', $userReview?->title) }}" class="form-control @error('title') is-invalid @enderror" placeholder="Short headline">
                                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Review</label>
                                            <textarea name="review" rows="4" class="form-control @error('review') is-invalid @enderror" placeholder="Share your experience">{{ old('review', $userReview?->review) }}</textarea>
                                            @error('review')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="col-12 d-flex justify-content-end">
                                            <button class="btn btn-dark">{{ $userReview ? 'Update Review' : 'Submit Review' }}</button>
                                        </div>
                                    </div>
                                </form>
                            @else
                                <p class="text-muted mb-0">You can review this product after purchasing it.</p>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="btn btn-outline-primary">Login to Review</a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-5 marketplace-surface-card">
            <div class="card-header bg-white">
                <h5 class="mb-0">What Customers Say</h5>
            </div>
            <div class="list-group list-group-flush">
                @forelse($product->reviews->sortByDesc('created_at') as $review)
                    <div class="list-group-item py-4">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <div class="fw-semibold">{{ $review->user->name ?? 'Customer' }}</div>
                                <div class="text-warning small">
                                    @for($star = 1; $star <= 5; $star++)
                                        {!! $star <= $review->rating ? '&#9733;' : '&#9734;' !!}
                                    @endfor
                                </div>
                            </div>
                            <small class="text-muted">{{ $review->created_at->format('d M Y') }}</small>
                        </div>
                        @if($review->title)
                            <div class="fw-semibold mt-2">{{ $review->title }}</div>
                        @endif
                        @if($review->review)
                            <p class="text-muted mb-0 mt-2">{{ $review->review }}</p>
                        @endif
                    </div>
                @empty
                    <div class="list-group-item text-muted py-4">No reviews yet. Be the first customer to share feedback.</div>
                @endforelse
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h4 mb-0">Related Products</h2>
            <a href="{{ route('frontend.products.index', ['category' => $product->category_id]) }}" class="btn btn-sm btn-outline-secondary">More In Category</a>
        </div>

        <div class="row g-4 row-cols-1 row-cols-md-2 row-cols-xl-4">
            @forelse($relatedProducts as $relatedProduct)
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm marketplace-product-card">
                        @if($relatedProduct->image)
                            <img src="{{ asset('storage/' . $relatedProduct->image) }}" alt="{{ $relatedProduct->name ?? 'Product image' }}" style="height: 220px; object-fit: cover;">
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center text-muted" style="height: 220px;">No image</div>
                        @endif
                        <div class="card-body d-flex flex-column">
                            <div class="small text-uppercase text-muted fw-semibold mb-2">{{ $relatedProduct->store->name ?? 'Store #'.$relatedProduct->store_id }}</div>
                            <h3 class="h6 fw-bold">{{ $relatedProduct->name ?? 'Product #'.$relatedProduct->id }}</h3>
                            <div class="mt-auto d-flex justify-content-between align-items-center pt-3">
                                <span class="fw-semibold">{{ number_format((float) ($relatedProduct->offer_price ?: $relatedProduct->price), 2) }} QAR</span>
                                <a href="{{ route('frontend.products.show', $relatedProduct) }}" class="btn btn-sm btn-outline-dark">View</a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-muted">No related products found yet.</div>
                </div>
            @endforelse
        </div>

        @if($recentlyViewedProducts->isNotEmpty())
            <div class="d-flex justify-content-between align-items-center mt-5 mb-3">
                <h2 class="h4 mb-0">Recently Viewed</h2>
                <a href="{{ route('frontend.products.index') }}" class="btn btn-sm btn-outline-secondary">Browse More</a>
            </div>

            <div class="row g-4 row-cols-1 row-cols-md-2 row-cols-xl-4">
                @foreach($recentlyViewedProducts as $recentProduct)
                    <div class="col">
                        <div class="card h-100 border-0 shadow-sm marketplace-product-card">
                            @if($recentProduct->image)
                                <img src="{{ asset('storage/' . $recentProduct->image) }}" alt="{{ $recentProduct->name ?? 'Product image' }}" style="height: 220px; object-fit: cover;">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center text-muted" style="height: 220px;">No image</div>
                            @endif
                            <div class="card-body d-flex flex-column">
                                <div class="small text-uppercase text-muted fw-semibold mb-2">{{ $recentProduct->store->name ?? 'Store #'.$recentProduct->store_id }}</div>
                                <h3 class="h6 fw-bold">{{ $recentProduct->name ?? 'Product #'.$recentProduct->id }}</h3>
                                <div class="mt-auto d-flex justify-content-between align-items-center pt-3">
                                    <span class="fw-semibold">{{ number_format((float) ($recentProduct->offer_price ?: $recentProduct->price), 2) }} QAR</span>
                                    <a href="{{ route('frontend.products.show', $recentProduct) }}" class="btn btn-sm btn-outline-dark">View</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
