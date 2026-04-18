<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $products = $request->user()
            ->wishlistProducts()
            ->with([
                'translations',
                'translation',
                'store.translations',
                'store.translation',
                'category.translation',
            ])
            ->latest('wishlists.id')
            ->paginate(12)
            ->withQueryString();

        return view('frontend.customer.wishlist', compact('products'));
    }

    public function store(Request $request, Product $product)
    {
        abort_unless($product->status && $product->store?->approved, 404);

        $request->user()->wishlistItems()->firstOrCreate([
            'product_id' => $product->id,
        ]);

        return back()->with('success', 'Product saved to your wishlist.');
    }

    public function destroy(Request $request, Product $product)
    {
        $request->user()->wishlistItems()->where('product_id', $product->id)->delete();

        return back()->with('success', 'Product removed from your wishlist.');
    }
}
