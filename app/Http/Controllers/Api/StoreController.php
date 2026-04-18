<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StoreController extends Controller
{
    /**
     * Get all approved stores with pagination and search.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Store::where("approved", true)->with(["translation", "storeCategory.translation"]);

        // Search by store name
        if ($request->has("search")) {
            $search = $request->search;
            $query->whereHas("translations", function ($q) use ($search) {
                $q->where("name", "like", "%" . $search . "%");
            });
        }

        // Filter by store category
        if ($request->has("category_id")) {
            $query->where("store_category_id", $request->category_id);
        }

        $stores = $query->paginate($request->get("per_page", 15));

        $formattedStores = collect($stores->items())->map(function ($store) {
            return [
                "id" => $store->id,
                "name" => $store->translation->name ?? "N/A",
                "description" => $store->translation->description ?? "",
                "address" => $store->translation->address ?? "",
                "email" => $store->email,
                "phone" => $store->phone,
                "logo" => $store->logo ? asset("storage/" . $store->logo) : null,
                "category" => $store->storeCategory->translation->store_category ?? "N/A",
            ];
        });

        return response()->json([
            "success" => true,
            "data" => $formattedStores,
            "pagination" => [
                "total" => $stores->total(),
                "current_page" => $stores->currentPage(),
                "last_page" => $stores->lastPage(),
                "per_page" => $stores->perPage(),
            ],
        ], 200);
    }

    /**
     * Get details of a single store.
     */
    public function show($id): JsonResponse
    {
        $store = Store::where("approved", true)
            ->with(["translation", "storeCategory.translation", "products.translation"])
            ->find($id);

        if (!$store) {
            return response()->json([
                "success" => false,
                "message" => "Store not found or not approved.",
            ], 404);
        }

        $data = [
            "id" => $store->id,
            "name" => $store->translation->name ?? "N/A",
            "description" => $store->translation->description ?? "",
            "address" => $store->translation->address ?? "",
            "email" => $store->email,
            "phone" => $store->phone,
            "logo" => $store->logo ? asset("storage/" . $store->logo) : null,
            "category" => $store->storeCategory->translation->store_category ?? "N/A",
            "products" => $store->products->map(function ($product) {
                return [
                    "id" => $product->id,
                    "name" => $product->translation->name ?? "N/A",
                    "price" => $product->price,
                    "image" => $product->image ? asset("storage/" . $product->image) : null,
                ];
            }),
        ];

        return response()->json([
            "success" => true,
            "data" => $data,
        ], 200);
    }

    /**
     * Get stores by category.
     */
    public function storesByCategory($categoryId): JsonResponse
    {
        $stores = Store::where("approved", true)
            ->where("store_category_id", $categoryId)
            ->with(["translation"])
            ->get();

        $formattedStores = $stores->map(function ($store) {
            return [
                "id" => $store->id,
                "name" => $store->translation->name ?? "N/A",
                "address" => $store->translation->address ?? "",
                "logo" => $store->logo ? asset("storage/" . $store->logo) : null,
            ];
        });

        return response()->json([
            "success" => true,
            "data" => $formattedStores,
        ], 200);
    }
}
