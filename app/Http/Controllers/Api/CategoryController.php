<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\StoreCategory;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * Get all general categories with their subcategories.
     */
    public function index(): JsonResponse
    {
        $categories = Category::with(["translation", "subcategory.translation"])->get();

        $formattedCategories = $categories->map(function ($category) {
            return [
                "id" => $category->id,
                "name" => $category->translation->category ?? "N/A",
                "logo" => $category->logo ? asset("storage/" . $category->logo) : null,
                "subcategories" => $category->subcategory->map(function ($sub) {
                    return [
                        "id" => $sub->id,
                        "name" => $sub->translation->sub_category ?? "N/A",
                        "logo" => $sub->logo ? asset("storage/" . $sub->logo) : null,
                    ];
                }),
            ];
        });

        return response()->json([
            "success" => true,
            "data" => $formattedCategories,
        ], 200);
    }

    /**
     * Get all store categories.
     */
    public function storeCategories(): JsonResponse
    {
        $storeCategories = StoreCategory::with(["translation"])->get();

        $formattedCategories = $storeCategories->map(function ($category) {
            return [
                "id" => $category->id,
                "name" => $category->translation->store_category ?? "N/A",
                "logo" => $category->logo ? asset("storage/" . $category->logo) : null,
            ];
        });

        return response()->json([
            "success" => true,
            "data" => $formattedCategories,
        ], 200);
    }
}
