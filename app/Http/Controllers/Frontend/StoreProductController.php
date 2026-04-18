<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductTranslation;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class StoreProductController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'stock' => ['nullable', Rule::in(['low', 'out'])],
        ]);

        $products = Product::with(['translations', 'category.translation', 'subcategory.translation', 'stockMovements'])
            ->where('store_id', auth()->user()->store_id)
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('id', $search)
                        ->orWhereHas('translations', fn ($translationQuery) => $translationQuery->where('name', 'like', '%' . $search . '%'));
                });
            })
            ->when(($filters['status'] ?? null) === 'active', fn ($query) => $query->where('status', true))
            ->when(($filters['status'] ?? null) === 'inactive', fn ($query) => $query->where('status', false))
            ->when(($filters['stock'] ?? null) === 'low', fn ($query) => $query->whereBetween('stock', [1, 5]))
            ->when(($filters['stock'] ?? null) === 'out', fn ($query) => $query->where('stock', '<=', 0))
            ->latest('created_at')
            ->paginate(12)
            ->withQueryString();

        return view('frontend.store.products', compact('products', 'filters'));
    }

    public function create()
    {
        $categories = Category::with('translation')->get();
        $subcategories = SubCategory::with('translation')->get();
        $productCategories = ProductCategory::with('translation')->get();

        return view('frontend.store.products-create', compact('categories', 'subcategories', 'productCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'nullable|exists:sub_categories,id',
            'product_category_id' => 'nullable|exists:product_categories,id',
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'offer_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'status' => 'boolean',
        ]);

        $initialStock = (int) $validated['stock'];

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('products', 'public')
            : null;

        $product = Product::create([
            'store_id' => auth()->user()->store_id,
            'category_id' => $validated['category_id'],
            'subcategory_id' => $validated['subcategory_id'] ?? null,
            'product_category_id' => $validated['product_category_id'] ?? null,
            'price' => $validated['price'],
            'offer_price' => $validated['offer_price'] ?? null,
            'stock' => $validated['stock'],
            'image' => $imagePath,
            'status' => $request->boolean('status', true),
        ]);

        if ($initialStock > 0) {
            $product->stockMovements()->create([
                'user_id' => auth()->id(),
                'direction' => 'in',
                'quantity' => $initialStock,
                'stock_before' => 0,
                'stock_after' => $initialStock,
                'reason' => 'Initial stock on product creation',
            ]);
        }

        $product->translations()->createMany([
            [
                'locale' => 'en',
                'name' => $validated['name_en'],
                'description' => $validated['description_en'] ?? null,
            ],
            [
                'locale' => 'ar',
                'name' => $validated['name_ar'],
                'description' => $validated['description_ar'] ?? null,
            ],
        ]);

        return redirect()
            ->route('store.products')
            ->with('success', 'Product created successfully.');
    }

    public function adjustStock(Request $request, Product $product)
    {
        abort_unless($product->store_id === auth()->user()->store_id, 404);

        $validated = $request->validate([
            'direction' => ['required', Rule::in(['in', 'out'])],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $product->adjustStock(
            (int) $validated['quantity'],
            $validated['direction'],
            auth()->user(),
            $validated['reason'] ?? 'Manual stock adjustment'
        );

        return redirect()
            ->route('store.products')
            ->with('success', 'Stock updated successfully.');
    }

    public function edit(Product $product)
    {
        abort_unless($product->store_id === auth()->user()->store_id, 404);

        $product->load('translations');
        $categories = Category::with('translation')->get();
        $subcategories = SubCategory::with('translation')->get();
        $productCategories = ProductCategory::with('translation')->get();

        return view('frontend.store.products-edit', compact('product', 'categories', 'subcategories', 'productCategories'));
    }

    public function update(Request $request, Product $product)
    {
        abort_unless($product->store_id === auth()->user()->store_id, 404);

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'nullable|exists:sub_categories,id',
            'product_category_id' => 'nullable|exists:product_categories,id',
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'offer_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'status' => 'boolean',
        ]);

        $imagePath = $product->image;
        if ($request->hasFile('image')) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product->update([
            'category_id' => $validated['category_id'],
            'subcategory_id' => $validated['subcategory_id'] ?? null,
            'product_category_id' => $validated['product_category_id'] ?? null,
            'price' => $validated['price'],
            'offer_price' => $validated['offer_price'] ?? null,
            'stock' => $validated['stock'],
            'image' => $imagePath,
            'status' => $request->boolean('status', true),
        ]);

        $product->translations()->updateOrCreate(
            ['locale' => 'en'],
            ['name' => $validated['name_en'], 'description' => $validated['description_en'] ?? null]
        );

        $product->translations()->updateOrCreate(
            ['locale' => 'ar'],
            ['name' => $validated['name_ar'], 'description' => $validated['description_ar'] ?? null]
        );

        return redirect()
            ->route('store.products')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        abort_unless($product->store_id === auth()->user()->store_id, 404);

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->translations()->delete();
        $product->delete();

        return redirect()
            ->route('store.products')
            ->with('success', 'Product deleted successfully.');
    }
}
