<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;

class CartController extends Controller
{
    /**
     * ?? Add to Cart
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            "product_id" => "required|exists:products,id",
            "quantity" => "required|integer|min:1",
            "price" => "nullable|numeric|min:0"
        ]);

        $user = $request->user();
        $product = Product::findOrFail($request->product_id);
        
        // Use provided price (for manual discount) or fallback to current product price
        $price = $request->has("price") ? (float) $request->price : (float) $product->current_price;

        $cart = Cart::firstOrCreate(["user_id" => $user->id]);

        $item = CartItem::where("cart_id", $cart->id)
                        ->where("product_id", $request->product_id)
                        ->first();

        if ($item) {
            $item->quantity += $request->quantity;
            $item->price = $price; 
            $item->save();
        } else {
            $item = CartItem::create([
                "cart_id" => $cart->id,
                "product_id" => $request->product_id,
                "quantity" => $request->quantity,
                "price" => $price
            ]);
        }

        return response()->json(["status" => true, "message" => "Added to cart", "item" => $item]);
    }

    /**
     * ?? View Cart
     */
    public function viewCart(Request $request)
    {
        $cart = Cart::with("items.product.translation")
                    ->where("user_id", $request->user()->id)
                    ->first();

        return response()->json(["status" => true, "cart" => $cart]);
    }

    /**
     * ?? Update Item (Quantity or Price/Discount)
     */
    public function updateItem(Request $request, $id)
    {
        $request->validate([
            "quantity" => "nullable|integer|min:1",
            "price" => "nullable|numeric|min:0"
        ]);

        $item = CartItem::findOrFail($id);
        
        if ($request->has("quantity")) {
            $item->quantity = $request->quantity;
        }
        
        if ($request->has("price")) {
            $item->price = (float) $request->price;
        }

        $item->save();

        return response()->json(["status" => true, "message" => "Item updated", "item" => $item]);
    }

    public function removeItem($id)
    {
        CartItem::findOrFail($id)->delete();
        return response()->json(["status" => true, "message" => "Item removed"]);
    }

    public function clearCart(Request $request)
    {
        $cart = Cart::where("user_id", $request->user()->id)->first();
        if ($cart) $cart->items()->delete();
        return response()->json(["status" => true, "message" => "Cart cleared"]);
    }
}
