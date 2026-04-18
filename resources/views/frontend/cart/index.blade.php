@extends('frontend.layouts.app')

@section('content')
<section class="py-5">
<div class="container px-4 px-lg-5 mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Your Cart</h3>
        <a href="{{ route('frontend.products.index') }}" class="btn btn-outline-secondary">Continue Shopping</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body" id="cart-items-wrapper">
                    @include('frontend.cart._items')
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white"><strong>Apply Coupon</strong></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('cart.coupon.apply') }}" class="d-flex gap-2">
                        @csrf
                        <input type="text" name="code" class="form-control" placeholder="Coupon code" required>
                        <button class="btn btn-dark">Apply</button>
                    </form>
                    @if(session('coupon.code'))
                        <div class="alert alert-success mt-3 mb-0 d-flex justify-content-between align-items-center">
                            <span>Applied: <strong>{{ session('coupon.code') }}</strong></span>
                            <form method="POST" action="{{ route('cart.coupon.remove') }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Remove</button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white"><strong>Cart Summary</strong></div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><strong>${{ number_format($pricing['subtotal'], 2) }}</strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Discount</span><strong class="text-success">- ${{ number_format($pricing['discount'], 2) }}</strong></div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3"><span>Total</span><strong>${{ number_format($pricing['total'], 2) }}</strong></div>
                    <a href="{{ route('checkout') }}" class="btn btn-success w-100 {{ $items->isEmpty() ? 'disabled' : '' }}">Proceed to Checkout</a>
                </div>
            </div>
        </div>
    </div>
</div>
</section>
@endsection
