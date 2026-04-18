<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreCategory;
use App\Models\StoreCategoryTranslation;
use Illuminate\Http\Request;

class StoreCategoryController extends Controller
{
    public function index()
    {
        $storecategories = StoreCategory::with(['translation', 'translations'])
            ->latest('id')
            ->paginate(12);

        return view('admin.storecategories.index', compact('storecategories'));
    }

    public function create()
    {
        return view('admin.storecategories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'logo' => 'required|image|mimes:jpg,jpeg,png,webp',
        ]);

        $logoPath = null;

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('category', 'public');
        }

        $storecategory = StoreCategory::create([
            'logo' => $logoPath,
        ]);

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
            StoreCategoryTranslation::create([
                'store_category_id' => $storecategory->id,
                'locale'  => $translation['locale'],
                'store_category'    => $translation['name'],
            ]);
        }

        return redirect()->route('admin.storecategories.index')->with('success', 'Category added successfully.');
    }

    public function edit(StoreCategory $storecategory)
    {
        $storecategory->load('translations');
        return view('admin.storecategories.edit', compact('storecategory'));
    }

    public function update(Request $request, StoreCategory $storecategory)
    {
        $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'logo'    => 'nullable|image|mimes:jpg,jpeg,png,webp',
        ]);

        // ✅ Update logo ONLY if new file is uploaded
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('storecategory', 'public');
            $storecategory->update([
                'logo' => $logoPath
            ]);
        }

        // ✅ Update English Translation
        $storecategory->translations()->updateOrCreate(
            ['locale' => 'en'],
            ['store_category' => $request->name_en]
        );

        // ✅ Update Arabic Translation
        $storecategory->translations()->updateOrCreate(
            ['locale' => 'ar'],
            ['store_category' => $request->name_ar]
        );

        return redirect()->route('admin.storecategories.index')
                        ->with('success', 'Store Category updated successfully.');
    }


    public function destroy(StoreCategory $storecategory)
    {
        $storecategory->delete();
        return redirect()->route('admin.storecategories.index')->with('success', 'Store Category deleted.');
    }
}
