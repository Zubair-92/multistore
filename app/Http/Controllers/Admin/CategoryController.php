<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryTranslation;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with(['translation', 'translations'])
            ->latest('id')
            ->paginate(12);

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
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

        $category = Category::create([
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
            CategoryTranslation::create([
                'category_id' => $category->id,
                'locale'  => $translation['locale'],
                'category'    => $translation['name'],
            ]);
        }

        return redirect()->route('admin.categories.index')->with('success', 'Category added successfully.');
    }

    public function edit(Category $category)
    {
        $category->load('translations');
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'logo'    => 'nullable|image|mimes:jpg,jpeg,png,webp',
        ]);

        // ✅ Update logo ONLY if new file is uploaded
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('category', 'public');
            $category->update([
                'logo' => $logoPath
            ]);
        }

        // ✅ Update English Translation
        $category->translations()->updateOrCreate(
            ['locale' => 'en'],
            ['category' => $request->name_en]
        );

        // ✅ Update Arabic Translation
        $category->translations()->updateOrCreate(
            ['locale' => 'ar'],
            ['category' => $request->name_ar]
        );

        return redirect()->route('admin.categories.index')
                        ->with('success', 'Category updated successfully.');
    }


    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Category deleted.');
    }
}
