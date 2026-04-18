<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserTranslation;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AdminRegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('admin.auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'address_ar' => 'required|string|max:255',
            'address_en' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:6',
        ]);

        Role::ensureDefaults();
        $adminRole = Role::where('name', 'admin')->firstOrFail();

        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $adminRole->id,
            'is_active' => 1,
        ]);

        $translations = [
            [
                'locale' => 'ar',
                'name' => $request->name_ar,
                'address' => $request->address_ar,
            ],
            [
                'locale' => 'en',
                'name' => $request->name_en,
                'address' => $request->address_en,
            ],
        ];

        foreach ($translations as $translation) {
            UserTranslation::create([
                'user_id' => $user->id,
                'locale' => $translation['locale'],
                'name' => $translation['name'],
                'address' => $translation['address'],
            ]);
        }

        Auth::guard('admin')->login($user);
        $request->session()->regenerate();

        return redirect()->route('admin.home')->with('success', 'Admin created successfully!');
    }
}
