<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ProductCategory;
use App\Models\ProductCategoryTranslation;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function index()
    {
        $productcategories = ProductCategory::with(['translation', 'translations', 'subcategory.category.translation'])
            ->latest('id')
            ->paginate(12);

        return view('admin.productcategories.index', compact('productcategories'));
    }

    public function create()
    {
        $categories = Category::with('translation','subcategory.translation')->get();
        return view('admin.productcategories.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required',
            'sub_category_id' => 'required',
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'logo' => 'required|image|mimes:jpg,jpeg,png,webp',
        ]);

        $category_id = $request->category_id;
        $sub_category_id = $request->sub_category_id;
        $logoPath = null;

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('productcategory', 'public');
        }

        // ✅ Create Product Category
        $productcategory = ProductCategory::create([
            'category_id' => $category_id,
            'sub_category_id' => $sub_category_id,
            'logo' => $logoPath,
        ]);

        // ✅ Insert Translations
        $translations = [
            [
                'locale'  => 'ar',
                'name'    => $request->name_ar,
            ],
            [
                'locale'  => 'en',
                'name'    => $request->name_en,
            ],
        ];

        foreach ($translations as $translation) {
            ProductCategoryTranslation::create([
                'product_category_id' => $productcategory->id,
                'locale'  => $translation['locale'],
                'product_category' => $translation['name'],
            ]);
        }

        return redirect()->route('admin.productcategories.index')
            ->with('success', 'Product Category added successfully.');
    }

    public function edit(ProductCategory $productcategory)
    {
        $productcategory->load('translations');
        return view('admin.productcategories.edit', compact('productcategory'));
    }

    public function update(Request $request, ProductCategory $productcategory)
    {
        $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'logo'    => 'nullable|image|mimes:jpg,jpeg,png,webp',
        ]);

        // ✅ Update logo ONLY if new file is uploaded
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('productcategory', 'public');
            $productcategory->update([
                'logo' => $logoPath
            ]);
        }

        // ✅ Update English Translation
        $productcategory->translations()->updateOrCreate(
            ['locale' => 'en'],
            ['product_category' => $request->name_en]
        );

        // ✅ Update Arabic Translation
        $productcategory->translations()->updateOrCreate(
            ['locale' => 'ar'],
            ['product_category' => $request->name_ar]
        );

        return redirect()->route('admin.productcategories.index')
            ->with('success', 'Product Category updated successfully.');
    }

    public function destroy(ProductCategory $productcategory)
    {
        $productcategory->delete();

        return redirect()->route('admin.productcategories.index')
            ->with('success', 'Product Category deleted.');
    }
}
