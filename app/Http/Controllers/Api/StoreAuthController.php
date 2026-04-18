<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Store;
use App\Models\StoreTranslation;
use Illuminate\Support\Facades\Hash;

class StoreAuthController extends Controller
{
    /**
     * ✅ Store Registration API
     */
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

        // Ensure default roles exist
        Role::ensureDefaults();

        $storeRole = Role::where('name', 'store')->firstOrFail();

        // ✅ Upload Logo
        $logoPath = $request->file('logo')->store('stores', 'public');

        // ✅ Create Store
        $store = Store::create([
            'store_category_id' => $request->store_category_id,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'approved' => 0, // must be approved by admin
            'logo'     => $logoPath,
        ]);

        // ✅ Store Translations
        $translations = [
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

        foreach ($translations as $t) {
            StoreTranslation::create([
                'store_id' => $store->id,
                'locale' => $t['locale'],
                'name' => $t['name'],
                'description' => $t['description'],
                'address' => $t['address'],
            ]);
        }

        // ✅ Create Store Owner User
        $user = User::create([
            'role_id'   => $storeRole->id,
            'store_id'  => $store->id,
            'email'     => $request->auth_email,
            'password'  => Hash::make($request->password),
            'is_active' => 1,
        ]);

        // ✅ Create API Token
        $token = $user->createToken('store_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Store registered successfully. Waiting for admin approval.',
            'store' => $store,
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * ✅ Store Login API
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        // ❌ Invalid credentials
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        // ❌ Check if user has store
        if (!$user->store_id) {
            return response()->json([
                'status' => false,
                'message' => 'This user is not a store owner'
            ], 403);
        }

        $store = Store::find($user->store_id);

        // ❌ Check store approval
        if (!$store || $store->approved == 0) {
            return response()->json([
                'status' => false,
                'message' => 'Store not approved yet'
            ], 403);
        }

        // ✅ Create Token
        $token = $user->createToken('store_login_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'user' => $user,
            'store' => $store,
            'token' => $token,
        ]);
    }

    /**
     * ✅ Logout API
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully'
        ]);
    }
}