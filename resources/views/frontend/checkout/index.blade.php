@extends('frontend.layouts.app')

@section('content')
<div class="container py-5">
    <h2 class="mb-4">Checkout</h2>

    @if($items->count() == 0)
        <div class="alert alert-warning">
            Your cart is empty. <a href="{{ route('frontend.home') }}">Continue Shopping</a>
        </div>
    @endif

    @if($items->count() > 0)
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card mb-4">
                    <div class="card-header bg-dark text-white">
                        <strong>Your Order</strong>
                    </div>
                    <div class="card-body">
                        @foreach ($items as $item)
                            <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                @php $path = $item->product->image; @endphp
                                @if($path)
                                    <img src="{{ asset('storage/' . $path) }}" alt="Product" class="me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                @else
                                    <div class="me-3 bg-light d-flex align-items-center justify-content-center text-muted" style="width: 60px; height: 60px;">N/A</div>
                                @endif

                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ $item->product->name }}</h6>
                                    <small>Qty: {{ $item->quantity }}</small>
                                    <div class="text-muted small">{{ $item->product->store->name ?? 'Store #'.$item->product->store_id }}</div>
                                </div>

                                <div class="text-end">
                                    <strong>${{ number_format($item->price * $item->quantity, 2) }}</strong>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-white">
                        <strong>Delivery Details</strong>
                    </div>
                    <div class="card-body">
                        <form id="checkout-form">
                            @csrf

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="delivery_name" value="{{ old('delivery_name', $user->name) }}" class="form-control" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="delivery_email" value="{{ old('delivery_email', $user->email) }}" class="form-control" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                    <input type="text" name="delivery_phone" value="{{ old('delivery_phone') }}" class="form-control" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Payment Method</label>
                                    <select name="payment_method" id="payment_method" class="form-select" required>
                                        <option value="cod">Cash on Delivery</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="demo_card">Demo Card Payment</option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Delivery Address</label>
                                    <textarea name="delivery_address" rows="3" class="form-control" required>{{ old('delivery_address', $user->address) }}</textarea>
                                </div>

                                <div class="col-12 d-none" id="transaction-reference-group">
                                    <label class="form-label">Transfer Reference</label>
                                    <input type="text" name="transaction_id" class="form-control" placeholder="Bank transfer reference or receipt ID">
                                    <small class="text-muted">Add the transfer reference when using bank transfer.</small>
                                </div>

                                <div class="col-12 d-none" id="demo-card-group">
                                    <div class="border rounded-3 p-3 bg-light">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div>
                                                <strong>Demo Card Details</strong>
                                                <div class="small text-muted">Use test card <strong>4242 4242 4242 4242</strong> for success or <strong>4000 0000 0000 0002</strong> for a declined payment demo.</div>
                                            </div>
                                            <span class="badge bg-dark">Sandbox</span>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label">Cardholder Name</label>
                                                <input type="text" name="demo_card_name" class="form-control demo-card-input" placeholder="Demo Customer">
                                            </div>

                                            <div class="col-12">
                                                <label class="form-label">Card Number</label>
                                                <input type="text" name="demo_card_number" class="form-control demo-card-input" placeholder="4242 4242 4242 4242">
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Expiry</label>
                                                <input type="text" name="demo_card_expiry" class="form-control demo-card-input" placeholder="12/30">
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">CVV</label>
                                                <input type="text" name="demo_card_cvv" class="form-control demo-card-input" placeholder="123">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Order Note</label>
                                    <textarea name="customer_note" rows="3" class="form-control" placeholder="Optional delivery notes"></textarea>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <strong>Order Summary</strong>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span>Subtotal</span>
                            <strong>${{ number_format($pricing['subtotal'], 2) }}</strong>
                        </div>

                        <div class="d-flex justify-content-between mb-3">
                            <span>Discount</span>
                            <strong class="text-success">- ${{ number_format($pricing['discount'], 2) }}</strong>
                        </div>

                        <div class="d-flex justify-content-between mb-3">
                            <span>Delivery</span>
                            <strong>$0.00</strong>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between mb-3">
                            <span>Total</span>
                            <strong>${{ number_format($pricing['total'], 2) }}</strong>
                        </div>

                        @if($pricing['coupon'])
                            <div class="alert alert-success small">
                                Coupon <strong>{{ $pricing['coupon']->code }}</strong> applied successfully.
                            </div>
                        @endif

                        <div class="alert alert-light border small">
                            Your order will save delivery and payment details exactly as submitted.
                        </div>

                        <div class="alert alert-info small">
                            Demo card payments are sandbox-only and do not charge any real money.
                        </div>

                        <div class="checkout-container"></div>

                        <button class="btn btn-success w-100 checkout" type="button">
                            Place Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
