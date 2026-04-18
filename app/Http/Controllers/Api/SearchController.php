<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    /**
     * Search products and stores.
     */
    public function search(Request $request): JsonResponse
    {
        $keyword = $request->get("q", "");

        if (empty($keyword)) {
            return response()->json([
                "success" => true,
                "data" => [
                    "products" => [],
                    "stores" => [],
                ],
            ]);
        }

        // Search products
        $products = Product::where("status", "active")
            ->whereHas("translations", function ($query) use ($keyword) {
                $query->where("name", "like", "%" . $keyword . "%")
                    ->orWhere("description", "like", "%" . $keyword . "%");
            })
            ->with(["translation"])
            ->limit(10)
            ->get();

        $formattedProducts = $products->map(function ($product) {
            return [
                "id" => $product->id,
                "name" => $product->translation->name ?? "N/A",
                "price" => $product->price,
                "image" => $product->image ? asset("storage/" . $product->image) : null,
            ];
        });

        // Search stores
        $stores = Store::where("approved", true)
            ->whereHas("translations", function ($query) use ($keyword) {
                $query->where("name", "like", "%" . $keyword . "%")
                    ->orWhere("description", "like", "%" . $keyword . "%");
            })
            ->with(["translation"])
            ->limit(10)
            ->get();

        $formattedStores = $stores->map(function ($store) {
            return [
                "id" => $store->id,
                "name" => $store->translation->name ?? "N/A",
                "logo" => $store->logo ? asset("storage/" . $store->logo) : null,
            ];
        });

        return response()->json([
            "success" => true,
            "data" => [
                "products" => $formattedProducts,
                "stores" => $formattedStores,
            ],
        ]);
    }
}
