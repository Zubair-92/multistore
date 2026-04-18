<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\StoreTranslation;
use App\Models\Role;
use App\Models\User;
use App\Models\UserTranslation;
use App\Models\StoreCategory;
use App\Notifications\StoreApprovedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StoreController extends Controller
{
    // ✅ LIST STORES
    public function index()
    {
        $stores = Store::with(['storecategory.translation', 'translations'])
            ->latest('id')
            ->paginate(12);

        return view('admin.stores.index', compact('stores'));
    }

    // ✅ CREATE FORM
    public function create()
    {
        $storecategories = StoreCategory::with('translation')->get();
        return view('admin.stores.create', compact('storecategories'));
    }

    // ✅ STORE DATA
    public function store(Request $request)
    {
        $request->validate([
            'store_category_id' => 'required|exists:store_categories,id',

            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',

            'description_en' => 'required|string',
            'description_ar' => 'required|string',

            'email' => 'required|email|unique:stores,email',
            'phone' => 'required|string|max:20',

            'logo'  => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',

            'auth_email' => 'required|email|unique:users,email',

            'address_en' => 'required|string',
            'address_ar' => 'required|string',

            'password'  => 'required|confirmed|min:6',
        ]);

        $storeRole = Role::where('name', 'store')->first();

        // ✅ Upload Logo
        $logoPath = $request->file('logo')->store('stores', 'public');

        // ✅ Create Store
        $store = Store::create([
            'store_category_id' => $request->store_category_id,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'approved' => 0,
            'logo'     => $logoPath,
        ]);

        // ✅ Store Translations (NAME + DESCRIPTION together)
        $storeTranslations = [
            [
                'locale' => 'en',
                'name' => $request->name_en,
                'description' => $request->description_en,
                'address' => $request->address_en,
            ],
            [
                'locale' => 'ar',
                'name' => $request->name_ar,
                'description' => $request->description_ar,
                'address' => $request->address_ar,
            ],
        ];

        foreach ($storeTranslations as $translation) {
            StoreTranslation::create([
                'store_id'    => $store->id,
                'locale'      => $translation['locale'],
                'name'        => $translation['name'],
                'description' => $translation['description'],
                'address' => $translation['address'],
            ]);
        }

        // ✅ Create User (Store Owner)
        $user = User::create([
            'role_id'   => $storeRole->id,
            'store_id'  => $store->id,
            'email'     => $request->auth_email,
            'password'  => Hash::make($request->password),
            'is_active' => 1,
        ]);

        return redirect()->route('admin.stores.index')
            ->with('success', 'Store created successfully.');
    }

    // ✅ EDIT FORM
    public function edit(Store $store)
    {
        $storecategories = StoreCategory::with('translation')->get();
        $store->load('translations');

        return view('admin.stores.edit', compact('store', 'storecategories'));
    }

    // ✅ UPDATE STORE
    public function update(Request $request, Store $store)
    {
        $wasApproved = $store->approved;

        $request->validate([
            'store_category_id' => 'required|exists:store_categories,id',

            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',

            'desc_en' => 'required|string',
            'desc_ar' => 'required|string',

            'email' => 'required|email|unique:stores,email,' . $store->id,
            'phone' => 'required|string|max:20',

            'logo'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            'approved' => 'nullable|boolean',
        ]);

        // ✅ Update main store table
        $store->update([
            'store_category_id' => $request->store_category_id,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'approved' => $request->approved ?? 0,
        ]);

        // ✅ Update logo (if uploaded)
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('stores', 'public');

            $store->update([
                'logo' => $path
            ]);
        }

        // ✅ Update translations (NAME + DESCRIPTION together)
        $translations = [
            'en' => [
                'name' => $request->name_en,
                'description' => $request->desc_en,
            ],
            'ar' => [
                'name' => $request->name_ar,
                'description' => $request->desc_ar,
            ],
        ];

        foreach ($translations as $locale => $data) {
            $store->translations()->updateOrCreate(
                [
                    'store_id' => $store->id,
                    'locale'   => $locale,
                ],
                [
                    'name'        => $data['name'],
                    'description' => $data['description'],
                ]
            );
        }

        if (! $wasApproved && $store->fresh()->approved) {
            $store->users()->get()->each->notify(new StoreApprovedNotification($store->fresh('translations')));
        }

        return redirect()->route('admin.stores.index')
            ->with('success', 'Store updated successfully.');
    }

    // ✅ DELETE STORE
    public function destroy(Store $store)
    {
        $store->delete();
        return redirect()->route('admin.stores.index')
            ->with('success', 'Store deleted successfully.');
    }

    public function toggleApprove(Request $request)
    {
        $store = Store::findOrFail($request->id);

        $store->approved = !$store->approved;
        $store->save();

        if ($store->approved) {
            $store->users()->get()->each->notify(new StoreApprovedNotification($store->fresh('translations')));
        }

        return response()->json([
            'status' => true,
            'message' => 'Store status updated'
        ]);
    }
    public function bulkAction(Request $request)
    {
        $ids = $request->ids;
        $action = $request->action;

        if ($action === 'approve') {
            Store::whereIn('id', $ids)->update(['approved' => 1]);
            Store::whereIn('id', $ids)->with(['users', 'translations'])->get()
                ->each(fn ($store) => $store->users->each->notify(new StoreApprovedNotification($store)));
        }

        if ($action === 'unapprove') {
            Store::whereIn('id', $ids)->update(['approved' => 0]);
        }

        if ($action === 'delete') {
            Store::whereIn('id', $ids)->delete();
        }

        return response()->json([
            'status' => true,
            'message' => 'Bulk action completed'
        ]);
    }
}
