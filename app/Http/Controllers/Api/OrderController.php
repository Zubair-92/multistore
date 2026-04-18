<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class OrderController extends Controller
{
    /**
     * Checkout: Place an order from the cart.
     */
    public function checkout(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            "delivery_name" => "required|string|max:255",
            "delivery_email" => "required|email|max:255",
            "delivery_phone" => "required|string|max:20",
            "delivery_address" => "required|string",
            "payment_method" => "required|in:cod,bank_transfer,demo_card",
            "customer_note" => "nullable|string",
            "discount_amount" => "nullable|numeric|min:0",
            "source" => "nullable|string|in:web,pos"
        ]);

        if ($validator->fails()) {
            return response()->json(["success" => false, "errors" => $validator->errors()], 422);
        }

        $cart = Cart::with("items.product.translation")->where("user_id", $user->id)->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(["success" => false, "message" => "Your cart is empty."], 400);
        }

        try {
            DB::beginTransaction();

            $subtotal = 0;
            $orderItems = [];

            foreach ($cart->items as $item) {
                $product = $item->product;

                if ($product->stock < $item->quantity) {
                    return response()->json(["success" => false, "message" => "Product " . ($product->name ?? "N/A") . " is out of stock."], 400);
                }

                $itemPrice = (float) $item->price;
                $subtotal += $itemPrice * $item->quantity;

                $orderItems[] = [
                    "product_id" => $product->id,
                    "store_id" => $product->store_id,
                    "product_name" => $product->name ?? "N/A",
                    "price" => $itemPrice,
                    "quantity" => $item->quantity,
                ];

                $product->decrement("stock", $item->quantity);
            }

            $orderDiscount = (float) ($request->discount_amount ?? 0);
            $total = max(0, $subtotal - $orderDiscount);

            $order = Order::create([
                "user_id" => $user->id,
                "store_id" => $cart->items->first()->product->store_id,
                "total_amount" => $total,
                "subtotal_amount" => $subtotal,
                "discount_amount" => $orderDiscount,
                "status" => "pending",
                "source" => $request->get("source", "web"),
                "payment_method" => $request->payment_method,
                "payment_status" => $request->payment_method === "cod" ? "unpaid" : "pending",
                "delivery_name" => $request->delivery_name,
                "delivery_email" => $request->delivery_email,
                "delivery_phone" => $request->delivery_phone,
                "delivery_address" => $request->delivery_address,
                "customer_note" => $request->customer_note,
            ]);

            foreach ($orderItems as $itemData) {
                $itemData["order_id"] = $order->id;
                OrderItem::create($itemData);
            }

            $cart->items()->delete();
            DB::commit();

            return response()->json(["success" => true, "message" => "Order placed successfully.", "order_id" => $order->id], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(["success" => false, "message" => "Failed to place order: " . $e->getMessage()], 500);
        }
    }

    /**
     * Get Daily POS Report.
     */
    public function dailyReport(Request $request): JsonResponse
    {
        $today = Carbon::today();
        
        $totalSales = Order::where("source", "pos")
            ->whereDate("created_at", $today)
            ->sum("total_amount");

        $cashSales = Order::where("source", "pos")
            ->whereDate("created_at", $today)
            ->where("payment_method", "cod")
            ->sum("total_amount");

        $cardSales = Order::where("source", "pos")
            ->whereDate("created_at", $today)
            ->where("payment_method", "bank_transfer")
            ->sum("total_amount");

        $orderCount = Order::where("source", "pos")
            ->whereDate("created_at", $today)
            ->count();

        return response()->json([
            "success" => true,
            "data" => [
                "date" => $today->toDateString(),
                "total_sales" => (float) $totalSales,
                "cash_sales" => (float) $cashSales,
                "card_sales" => (float) $cardSales,
                "order_count" => $orderCount
            ]
        ]);
    }

    /**
     * Get POS orders history.
     */
    public function posOrders(Request $request): JsonResponse
    {
        $orders = Order::where("user_id", $request->user()->id)
            ->where("source", "pos")
            ->latest()
            ->paginate($request->get("per_page", 15));

        return response()->json([
            "success" => true,
            "data" => $orders->items(),
            "pagination" => [
                "total" => $orders->total(),
                "current_page" => $orders->currentPage(),
                "last_page" => $orders->lastPage(),
            ],
        ], 200);
    }

    /**
     * Get order history.
     */
    public function index(Request $request): JsonResponse
    {
        $orders = Order::where("user_id", $request->user()->id)
            ->latestFirst()
            ->paginate($request->get("per_page", 10));

        return response()->json([
            "success" => true,
            "data" => $orders->items(),
            "pagination" => [
                "total" => $orders->total(),
                "current_page" => $orders->currentPage(),
                "last_page" => $orders->lastPage(),
            ],
        ], 200);
    }

    /**
     * Get order details.
     */
    public function show($id, Request $request): JsonResponse
    {
        $order = Order::with(["items", "user"])
            ->where("user_id", $request->user()->id)
            ->find($id);

        if (!$order) {
            return response()->json(["success" => false, "message" => "Order not found."], 404);
        }

        return response()->json(["success" => true, "data" => $order]);
    }
}
