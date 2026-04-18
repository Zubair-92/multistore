@forelse ($items as $item)

    <div class="cart-item d-flex justify-content-between border-bottom py-3">

        {{-- Product info --}}
        <div class="d-flex">
            @php
                // Guest: $item is array  |  Logged in: $item is CartItem model
                $img = is_array($item) ? $item['image'] : $item->product->image;
                $name = is_array($item) ? $item['name'] : $item->product->name;
                $price = is_array($item) ? $item['price'] : $item->price;
                $qty = is_array($item) ? $item['quantity'] : $item->quantity;
                $productId = is_array($item) ? $item['product_id'] : $item->product->id;
            @endphp

            <img src="{{ asset('storage/' . $img) }}" width="70">

            <div class="ms-3">
                <strong>{{ $name }}</strong>
                <p>QAR {{ $price }}</p>
            </div>
        </div>

        {{-- Quantity --}}
        <input type="number"
               name="quantity"
               value="{{ $qty }}"
               min="1"
               class="form-control w-25 upt-qty-pro-frm-c"
               data-pro-id="{{ $productId }}">

        {{-- Remove --}}
        <a href="#"
           class="btn btn-danger btn-sm rmv-fm-c"
           data-pro-id="{{ $productId }}">
            Remove
        </a>

    </div>

@empty
    <p>Your cart is empty.</p>
@endforelse
