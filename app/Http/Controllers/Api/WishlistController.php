<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WishlistController extends Controller
{
    /**
     * Get user's wishlist.
     */
    public function index(Request $request): JsonResponse
    {
        $wishlist = Wishlist::where("user_id", $request->user()->id)
            ->with(["product.translation"])
            ->get();

        $formattedWishlist = $wishlist->map(function ($item) {
            $product = $item->product;
            if (!$product) return null;

            return [
                "id" => $item->id,
                "product" => [
                    "id" => $product->id,
                    "name" => $product->translation->name ?? "N/A",
                    "price" => $product->price,
                    "image" => $product->image ? asset("storage/" . $product->image) : null,
                ]
            ];
        })->filter();

        return response()->json([
            "success" => true,
            "data" => $formattedWishlist,
        ], 200);
    }

    /**
     * Toggle product in wishlist (Add/Remove).
     */
    public function toggle(Request $request): JsonResponse
    {
        $request->validate([
            "product_id" => "required|exists:products,id",
        ]);

        $userId = $request->user()->id;
        $productId = $request->product_id;

        $wishlistItem = Wishlist::where("user_id", $userId)
            ->where("product_id", $productId)
            ->first();

        if ($wishlistItem) {
            $wishlistItem->delete();
            $message = "Product removed from wishlist.";
            $action = "removed";
        } else {
            Wishlist::create([
                "user_id" => $userId,
                "product_id" => $productId,
            ]);
            $message = "Product added to wishlist.";
            $action = "added";
        }

        return response()->json([
            "success" => true,
            "message" => $message,
            "action" => $action,
        ]);
    }
}
