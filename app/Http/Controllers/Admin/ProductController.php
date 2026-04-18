<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\ProductCategory;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Store;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'stock' => ['nullable', Rule::in(['low', 'out'])],
        ]);

        $products = Product::with([
            'store.translation',
            'category.translation',
            'subcategory.translation',
            'productCategory.translation',
            'translations',
            'stockMovements',
        ])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('id', $search)
                        ->orWhereHas('translations', fn ($translationQuery) => $translationQuery->where('name', 'like', '%' . $search . '%'));
                });
            })
            ->when($filters['store_id'] ?? null, fn ($query, $storeId) => $query->where('store_id', $storeId))
            ->when(($filters['status'] ?? null) === 'active', fn ($query) => $query->where('status', true))
            ->when(($filters['status'] ?? null) === 'inactive', fn ($query) => $query->where('status', false))
            ->when(($filters['stock'] ?? null) === 'low', fn ($query) => $query->whereBetween('stock', [1, 5]))
            ->when(($filters['stock'] ?? null) === 'out', fn ($query) => $query->where('stock', '<=', 0))
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        $stores = Store::with('translation')->orderByDesc('id')->get();

        return view('admin.products.index', compact('products', 'stores', 'filters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $stores = Store::with('translation')->get();
        $categories = Category::with('translation')->get();
        $subcategories = SubCategory::with('translation')->get();
        $productCategories = ProductCategory::with('translation')->get();

        return view('admin.products.create', compact(
            'stores', 'categories', 'subcategories', 'productCategories'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
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

        // Upload image
        $imagePath = $request->hasFile('image') 
            ? $request->file('image')->store('products', 'public') 
            : null;

        // Create product
        $initialStock = (int) $request->stock;

        $product = Product::create([
            'store_id' => $request->store_id,
            'category_id' => $request->category_id,
            'subcategory_id' => $request->subcategory_id,
            'product_category_id' => $request->product_category_id,
            'price' => $request->price,
            'offer_price' => $request->offer_price,
            'stock' => $request->stock,
            'image' => $imagePath,
            'status' => $request->status ?? 1,
        ]);

        if ($initialStock > 0) {
            $product->stockMovements()->create([
                'user_id' => auth('admin')->id(),
                'direction' => 'in',
                'quantity' => $initialStock,
                'stock_before' => 0,
                'stock_after' => $initialStock,
                'reason' => 'Initial stock on product creation',
            ]);
        }

        // Insert translations
        ProductTranslation::create([
            'product_id' => $product->id,
            'locale' => 'en',
            'name' => $request->name_en,
            'description' => $request->description_en,
        ]);

        ProductTranslation::create([
            'product_id' => $product->id,
            'locale' => 'ar',
            'name' => $request->name_ar,
            'description' => $request->description_ar,
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $product->load('translations');

        $stores = Store::with('translation')->get();
        $categories = Category::with('translation')->get();
        $subcategories = SubCategory::with('translation')->get();
        $productCategories = ProductCategory::with('translation')->get();

        return view('admin.products.edit', compact(
            'product', 'stores', 'categories', 'subcategories', 'productCategories'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
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

        // Update image if uploaded
        $imagePath = $product->image;
        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $imagePath = $request->file('image')->store('products', 'public');
        }

        // Update product
        $product->update([
            'store_id' => $request->store_id,
            'category_id' => $request->category_id,
            'subcategory_id' => $request->subcategory_id,
            'product_category_id' => $request->product_category_id,
            'price' => $request->price,
            'offer_price' => $request->offer_price,
            'stock' => $request->stock,
            'image' => $imagePath,
            'status' => $request->status ?? 1,
        ]);

        // Update translations
        $product->translations()->updateOrCreate(
            ['locale' => 'en'],
            ['name' => $request->name_en, 'description' => $request->description_en]
        );

        $product->translations()->updateOrCreate(
            ['locale' => 'ar'],
            ['name' => $request->name_ar, 'description' => $request->description_ar]
        );

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function adjustStock(Request $request, Product $product)
    {
        $validated = $request->validate([
            'direction' => ['required', Rule::in(['in', 'out'])],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $product->adjustStock(
            (int) $validated['quantity'],
            $validated['direction'],
            auth('admin')->user(),
            $validated['reason'] ?? 'Admin stock adjustment'
        );

        return redirect()->route('admin.products.index')
            ->with('success', 'Product stock updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->translations()->delete();
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
