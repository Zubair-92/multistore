<?php

namespace App\Http\Controllers\StoreAuth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserTranslation;
use App\Models\Role;
use App\Models\Store;
use App\Models\StoreTranslation;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\StoreCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StoreRegisterController extends Controller
{
    public function showRegistrationForm()
    {
        $storecategories = StoreCategory::with('translation')->get();
        return view('frontend.auth.store.register', compact('storecategories'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'store_category_id' => 'required|exists:store_categories,id',

            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',

            'desc_en' => 'required|string',
            'desc_ar' => 'required|string',

            'email' => 'required|email|unique:stores,email',
            'phone' => 'required|string|max:20',

            'logo'  => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',

            'auth_email' => 'required|email|unique:users,email',

            'addr_en' => 'required|string',
            'addr_ar' => 'required|string',

            'password'  => 'required|confirmed|min:6',
        ]);

        Role::ensureDefaults();

        $storeRole = Role::where('name', 'store')->firstOrFail();

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
                'description' => $request->desc_en,
                'address' => $request->addr_en,
            ],
            [
                'locale' => 'ar',
                'name' => $request->name_ar,
                'description' => $request->desc_ar,
                'address' => $request->addr_ar,
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
        User::create([
            'role_id'   => $storeRole->id,
            'store_id'  => $store->id,
            'email'     => $request->auth_email,
            'password'  => Hash::make($request->password),
            'is_active' => 1,
        ]);

        return redirect()->route('store.login')->with(
            'success',
            'Store registered successfully. Please wait for admin approval before logging in.'
        );
    }
}
