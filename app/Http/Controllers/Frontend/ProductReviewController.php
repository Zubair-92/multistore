<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        abort_unless($product->status && $product->store?->approved, 404);

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:255'],
            'review' => ['nullable', 'string', 'max:2000'],
        ]);

        $user = $request->user();

        $hasPurchasedProduct = $user->orders()
            ->where('status', '!=', 'cancelled')
            ->whereHas('items', fn ($query) => $query->where('product_id', $product->id))
            ->exists();

        abort_unless($hasPurchasedProduct, 403, 'You can only review products you have purchased.');

        $user->productReviews()->updateOrCreate(
            ['product_id' => $product->id],
            $validated
        );

        return back()->with('success', 'Your review has been saved.');
    }
}
