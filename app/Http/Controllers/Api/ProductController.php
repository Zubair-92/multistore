<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // ? List all products (paginated)
    public function index(Request $request)
    {
        $query = Product::with([
            "translation",
            "store",
            "category.translation",
            "subcategory.translation",
            "productCategory.translation",
        ])->active();

        // ?? Search by name or SKU
        if ($request->filled("search")) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where("sku", "like", "%" . $search . "%")
                  ->orWhereHas("translation", function ($q2) use ($search) {
                      $q2->where("name", "like", "%" . $search . "%");
                  });
            });
        }

        // ?? Filters
        if ($request->filled("category_id")) { $query->where("category_id", $request->category_id); }
        if ($request->filled("sub_category_id")) { $query->where("sub_category_id", $request->sub_category_id); }
        if ($request->filled("store_id")) { $query->where("store_id", $request->store_id); }

        // ?? Latest + pagination
        $products = $query->latest()->paginate($request->get("per_page", 12));

        $formattedProducts = collect($products->items())->map(function ($product) {
            return [
                "id" => $product->id,
                "sku" => $product->sku,
                "name" => $product->name ?? "N/A",
                "price" => (float) $product->price,
                "offer_price" => $product->offer_price ? (float) $product->offer_price : null,
                "current_price" => (float) $product->current_price,
                "stock" => $product->stock,
                "image" => $product->image ? asset("storage/" . $product->image) : null,
                "store" => $product->store->translation->name ?? "N/A",
            ];
        });

        return response()->json([
            "status" => true,
            "data" => [
                "data" => $formattedProducts,
                "total" => $products->total(),
                "current_page" => $products->currentPage(),
                "last_page" => $products->lastPage(),
                "per_page" => $products->perPage(),
            ],
        ]);
    }

    public function show($id)
    {
        $product = Product::with([
                "translation",
                "store",
                "category.translation",
                "subcategory.translation",
                "productCategory.translation",
                "reviews.user",
            ])
            ->findOrFail($id);

        return response()->json([
            "status" => true,
            "data" => [
                "id" => $product->id,
                "sku" => $product->sku,
                "name" => $product->name,
                "description" => optional($product->translation)->description,
                "price" => (float) $product->price,
                "offer_price" => $product->offer_price ? (float) $product->offer_price : null,
                "current_price" => (float) $product->current_price,
                "stock" => $product->stock,
                "image" => $product->image ? asset("storage/" . $product->image) : null,
                "rating" => $product->averageRating(),
                "reviews" => $product->reviews->map(fn($r) => [
                    "user" => $r->user?->name, "rating" => $r->rating, "title" => $r->title, "review" => $r->review
                ]),
            ]
        ]);
    }
}
