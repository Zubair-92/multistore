<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Notifications\LowStockAlertNotification;
use App\Notifications\NewOrderPlacedNotification;
use App\Support\CartPricing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    public function __construct()
    {
        // checkout page must be protected
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();

        // get the cart with items + product details
        $cart = Cart::where('user_id', Auth::id())
                ->with('items.product.store.translation')
                ->first();

        $items = $cart ? $cart->items : collect();

        $coupon = Coupon::where('code', session('coupon.code'))->first();
        $pricing = CartPricing::calculate($items, $coupon);

        if (! $pricing['coupon'] && session()->has('coupon')) {
            session()->forget('coupon');
        }

        return view('frontend.checkout.index', compact('items', 'user', 'pricing'));
    }

    public function placeOrder(Request $request)
    {
        $userId = Auth::id();
        $user = Auth::user();

        $validated = $request->validate([
            'delivery_name' => ['required', 'string', 'max:255'],
            'delivery_email' => ['required', 'email', 'max:255'],
            'delivery_phone' => ['required', 'string', 'max:30'],
            'delivery_address' => ['required', 'string', 'max:1000'],
            'payment_method' => ['required', Rule::in(Order::PAYMENT_METHODS)],
            'transaction_id' => ['nullable', 'required_if:payment_method,bank_transfer', 'string', 'max:255'],
            'demo_card_name' => ['nullable', 'required_if:payment_method,demo_card', 'string', 'max:255'],
            'demo_card_number' => ['nullable', 'required_if:payment_method,demo_card', 'string', 'max:25'],
            'demo_card_expiry' => ['nullable', 'required_if:payment_method,demo_card', 'string', 'max:10'],
            'demo_card_cvv' => ['nullable', 'required_if:payment_method,demo_card', 'string', 'max:6'],
            'customer_note' => ['nullable', 'string', 'max:1000'],
        ]);

        // Get logged-in user's cart items
        $cart = Cart::where('user_id', $userId)->with('items.product.translations')->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty.',
            ], 422);
        }

        foreach ($cart->items as $item) {
            if (! $item->product || ! $item->product->status) {
                return response()->json([
                    'success' => false,
                    'message' => 'One of the products in your cart is no longer available.',
                ], 422);
            }

            if ($item->product->stock < $item->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not enough stock for ' . ($item->product->name ?? ('Product #' . $item->product->id)) . '.',
                ], 422);
            }
        }

        $coupon = Coupon::where('code', session('coupon.code'))->first();
        $pricing = CartPricing::calculate($cart->items, $coupon);

        $demoPaymentReference = null;

        if ($validated['payment_method'] === 'demo_card') {
            $sanitizedDemoCardNumber = preg_replace('/\D+/', '', $validated['demo_card_number']);

            if (! in_array($sanitizedDemoCardNumber, ['4242424242424242', '4000000000000002'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Use demo card 4242 4242 4242 4242 for success or 4000 0000 0000 0002 to simulate a declined payment.',
                ], 422);
            }

            if ($sanitizedDemoCardNumber === '4000000000000002') {
                return response()->json([
                    'success' => false,
                    'message' => 'Demo payment was declined. Use 4242 4242 4242 4242 to complete the order successfully.',
                ], 422);
            }

            $demoPaymentReference = 'DEMO-' . strtoupper(Str::random(10));
        }

        $paymentStatus = match ($validated['payment_method']) {
            'bank_transfer' => 'pending',
            'demo_card' => 'paid',
            default => 'unpaid',
        };

        $storeIds = $cart->items->pluck('product.store_id')->filter()->unique()->values();

        $order = DB::transaction(function () use ($cart, $paymentStatus, $pricing, $storeIds, $user, $userId, $validated, $demoPaymentReference) {
            $order = Order::create([
                'user_id' => $userId,
                'store_id' => $storeIds->count() === 1 ? $storeIds->first() : null,
                'total_amount' => $pricing['total'],
                'subtotal_amount' => $pricing['subtotal'],
                'status' => 'pending',
                'payment_method' => $validated['payment_method'],
                'payment_status' => $paymentStatus,
                'transaction_id' => $validated['transaction_id'] ?? $demoPaymentReference,
                'delivery_name' => $validated['delivery_name'],
                'delivery_email' => $validated['delivery_email'],
                'delivery_phone' => $validated['delivery_phone'],
                'delivery_address' => $validated['delivery_address'],
                'customer_note' => $validated['customer_note'] ?? null,
                'discount_code' => $pricing['coupon']?->code,
                'discount_amount' => $pricing['discount'],
            ]);

            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product->id,
                    'store_id' => $item->product->store_id,
                    'product_name' => $item->product->name ?? ('Product #'.$item->product->id),
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                ]);

                $item->product->decrement('stock', $item->quantity);

                $item->product->refresh();

                if ($item->product->stock <= 5) {
                    Notification::send($item->product->store->users, new LowStockAlertNotification($item->product));
                }
            }

            $cart->items()->delete();
            session()->forget('coupon');

            $user->syncTranslation([
                'name' => $user->name,
                'address' => $validated['delivery_address'],
            ], 'en');

            if ($pricing['coupon']) {
                $pricing['coupon']->increment('used_count');
            }

            return $order;
        });

        $order->load(['user', 'items.store.users']);

        $user->notify(new NewOrderPlacedNotification($order, 'customer'));

        $storeUsers = $order->items
            ->flatMap(fn ($item) => $item->store?->users ?? collect())
            ->unique('id')
            ->values();

        Notification::send($storeUsers, new NewOrderPlacedNotification($order, 'store'));

        return response()->json([
            'success' => true,
            'message' => $validated['payment_method'] === 'demo_card'
                ? 'Demo payment approved and order placed successfully.'
                : 'Order Placed Successfully.',
            'redirect' => route('profile.orders.show', $order),
        ]);
    }

}
