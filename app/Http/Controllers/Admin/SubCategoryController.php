<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubCategory;
use App\Models\SubCategoryTranslation;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubCategoryController extends Controller
{
    public function index()
    {
        $subcategories = SubCategory::with(['category.translation', 'translation', 'translations'])
            ->latest('id')
            ->paginate(12);

        return view('admin.subcategories.index', compact('subcategories'));
    }

    public function create()
    {
        $categories = Category::with('translation')->get();
        return view('admin.subcategories.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name_en'     => 'required|string|max:255',
            'name_ar'     => 'required|string|max:255',
            'logo'        => 'required|image|mimes:jpg,jpeg,png,webp',
        ]);

        // ✅ Store Logo
        $logoPath = $request->file('logo')->store('subcategories', 'public');

        // ✅ Create Subcategory
        $subcategory = SubCategory::create([
            'category_id' => $request->category_id,
            'logo'        => $logoPath,
        ]);

        // ✅ Insert translations
        $translations = [
            ['locale' => 'en', 'subcategory' => $request->name_en],
            ['locale' => 'ar', 'subcategory' => $request->name_ar],
        ];

        foreach ($translations as $translation) {
            SubCategoryTranslation::create([
                'sub_category_id' => $subcategory->id,
                'locale'         => $translation['locale'],
                'sub_category'    => $translation['subcategory'],
            ]);
        }

        return redirect()->route('admin.subcategories.index')
                         ->with('success', 'Subcategory created successfully.');
    }

    public function edit(SubCategory $subcategory)
    {
        $subcategory->load('translations');
        $categories = Category::with('translation')->get();

        return view('admin.subcategories.edit', compact('subcategory', 'categories'));
    }

    public function update(Request $request, SubCategory $subcategory)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name_en'     => 'required|string|max:255',
            'name_ar'     => 'required|string|max:255',
            'logo'        => 'nullable|image|mimes:jpg,jpeg,png,webp',
        ]);

        $data = [
            'category_id' => $request->category_id,
        ];

        // ✅ If new logo uploaded → delete old → save new
        if ($request->hasFile('logo')) {
            if ($subcategory->logo && Storage::disk('public')->exists($subcategory->logo)) {
                Storage::disk('public')->delete($subcategory->logo);
            }

            $data['logo'] = $request->file('logo')->store('subcategories', 'public');
        }

        $subcategory->update($data);

        // ✅ Update translations
        $subcategory->translations()->updateOrCreate(
            ['locale' => 'en'],
            ['sub_category' => $request->name_en]
        );

        $subcategory->translations()->updateOrCreate(
            ['locale' => 'ar'],
            ['sub_category' => $request->name_ar]
        );

        return redirect()->route('admin.subcategories.index')
                         ->with('success', 'Subcategory updated successfully.');
    }

    public function destroy(SubCategory $subcategory)
    {
        // ✅ Delete logo from storage
        if ($subcategory->logo && Storage::disk('public')->exists($subcategory->logo)) {
            Storage::disk('public')->delete($subcategory->logo);
        }

        $subcategory->delete();

        return redirect()->route('admin.subcategories.index')
                         ->with('success', 'Subcategory deleted successfully.');
    }
}
