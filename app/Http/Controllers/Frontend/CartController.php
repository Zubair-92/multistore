<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Support\CartPricing;
use Illuminate\Http\Request;
use Auth;

class CartController extends Controller
{
    protected function isPurchasableProduct(?Product $product): bool
    {
        return (bool) $product
            && (bool) $product->status
            && (bool) $product->store?->approved;
    }

    protected function syncSessionCart(): \Illuminate\Support\Collection
    {
        $sessionCart = collect(session()->get('cart', []));

        if ($sessionCart->isEmpty()) {
            return collect();
        }

        $products = Product::query()
            ->with(['translations', 'translation', 'store'])
            ->whereIn('id', $sessionCart->keys()->all())
            ->get()
            ->keyBy('id');

        $normalizedCart = $sessionCart
            ->map(function ($item, $productId) use ($products) {
                $product = $products->get((int) $productId);

                if (! $this->isPurchasableProduct($product)) {
                    return null;
                }

                return [
                    'product_id' => $product->id,
                    'name' => $product->name ?? ($item['name'] ?? ('Product #' . $product->id)),
                    'price' => (float) ($product->offer_price ?: $product->price),
                    'image' => $product->image,
                    'quantity' => max(1, (int) ($item['quantity'] ?? 1)),
                ];
            })
            ->filter()
            ->values();

        session()->put('cart', $normalizedCart->keyBy('product_id')->all());

        return $normalizedCart->values();
    }

    protected function syncUserCart(Cart $cart): \Illuminate\Support\Collection
    {
        $items = $cart->items()->with(['product.store', 'product.translations', 'product.translation'])->get();

        $invalidItemIds = $items
            ->filter(fn ($item) => ! $this->isPurchasableProduct($item->product))
            ->pluck('id');

        if ($invalidItemIds->isNotEmpty()) {
            $cart->items()->whereIn('id', $invalidItemIds)->delete();
        }

        return $cart->items()->with(['product.store', 'product.translations', 'product.translation'])->get();
    }

    // Show cart page
    public function index()
    {
        if (Auth::check()) {
            $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
            $items = $this->syncUserCart($cart);
        } else {
            $items = $this->syncSessionCart();
        }

        $coupon = Coupon::where('code', session('coupon.code'))->first();
        $pricing = CartPricing::calculate($items, $coupon);

        if (! $pricing['coupon'] && session()->has('coupon')) {
            session()->forget('coupon');
        }

        return view('frontend.cart.index', compact('items', 'pricing'));
    }


    // Add to cart
    public function add(Request $request)
    {
        $productId = $request->product_id;
        $quantity = $request->quantity ?? 1;

        $product = Product::findOrFail($productId);

        abort_unless($this->isPurchasableProduct($product), 404);

        // Logged-in user
        if (Auth::check()) {

            $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);

            $item = $cart->items()->where('product_id', $productId)->first();

            if ($item) {
                $item->quantity += $quantity;
                $item->save();
            } else {
                $cart->items()->create([
                    'product_id' => $productId,
                    'quantity'   => $quantity,
                    'price'      => $product->offer_price ?: $product->price
                ]);
            }

            $cartCount = $cart->items()->sum('quantity');

            return response()->json([
                'success' => true,
                'message' => 'Product added to cart.',
                'cart_count' => $cartCount
            ]);
        }


        // Guest (session)
        $cart = session()->get('cart', []);

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
        } else {
            $cart[$productId] = [
                'product_id' => $productId,
                'name'       => $product->name ?? ('Product #' . $product->id),
                'price'      => $product->offer_price ?: $product->price,
                'image'      => $product->image,
                'quantity'   => $quantity,
            ];
        }

        session()->put('cart', $cart);

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart.',
            'cart_count' => collect($cart)->sum('quantity')
        ]);
    }


    // Update quantity
    public function update(Request $request)
    {
        $productId = $request->id;
        $qty = (int) $request->qty;


        // Authenticated user
        if (Auth::check()) {
            $cart = Cart::where('user_id', Auth::id())->first();

            if ($cart) {
                $item = $cart->items()->where('product_id', $productId)->first();

                if ($item) {
                    if ($qty <= 0) {
                        $item->delete();
                    } else {
                        $item->quantity = $qty;
                        $item->save();
                    }
                }

                $items = $this->syncUserCart($cart);
                $updatedView = view('frontend.cart._items', compact('items'))->render();

                return response()->json([
                    'success' => true,
                    'cart_count' => $cart->items()->sum('quantity'),
                    'html' => $updatedView
                ]);
            }
        }


        // Guest
        $cart = collect(session()->get('cart', []));

        if ($qty <= 0) {
            $cart->forget($productId);
        } else {
            if ($cart->has($productId)) {
                $item = $cart->get($productId);
                $item['quantity'] = $qty;
                $cart->put($productId, $item);
            }
        }

        session()->put('cart', $cart->all());

        $items = $this->syncSessionCart();
        $updatedView = view('frontend.cart._items', compact('items'))->render();

        return response()->json([
            'success' => true,
            'cart_count' => $items->sum('quantity'),
            'html' => $updatedView
        ]);
    }


    // Remove
    public function remove(Request $request)
    {
        $productId = $request->product_id;

        // Logged-in user
        if (Auth::check()) {

            $cart = Cart::where('user_id', Auth::id())->first();

            if ($cart) {
                $cart->items()->where('product_id', $productId)->delete();
            }

            $items = $cart ? $this->syncUserCart($cart) : collect();
            $updatedView = view('frontend.cart._items', compact('items'))->render();

            return response()->json([
                'success' => true,
                'message' => 'Product removed from cart.',
                'cart_count' => $cart ? $cart->items()->sum('quantity') : 0,
                'html' => $updatedView
            ]);
        }


        // Guest
        $cart = collect(session()->get('cart', []));
        $cart->forget($productId);
        session()->put('cart', $cart->all());

        $items = $this->syncSessionCart();
        $updatedView = view('frontend.cart._items', compact('items'))->render();

        return response()->json([
            'success' => true,
            'message' => 'Product removed from cart.',
            'cart_count' => $items->sum('quantity'),
            'html' => $updatedView
        ]);
    }


    // Get cart count globally
    public static function getCartCount()
    {
        if (Auth::check()) {
            $cart = Cart::where('user_id', Auth::id())->first();
            return $cart ? $cart->items()->sum('quantity') : 0;
        }

        return collect(session()->get('cart', []))->sum('quantity');
    }

    public function applyCoupon(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50'],
        ]);

        $items = Auth::check()
            ? optional(Cart::where('user_id', Auth::id())->with('items')->first())->items ?? collect()
            : collect(session()->get('cart', []));

        $coupon = Coupon::where('code', strtoupper(trim($validated['code'])))->first();

        if (! $coupon || ! CartPricing::calculate($items, $coupon)['coupon']) {
            return back()->with('error', 'Coupon is invalid or not applicable.');
        }

        session([
            'coupon' => [
                'code' => $coupon->code,
            ],
        ]);

        return back()->with('success', 'Coupon applied successfully.');
    }

    public function removeCoupon()
    {
        session()->forget('coupon');

        return back()->with('success', 'Coupon removed.');
    }
}
