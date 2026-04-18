<?php

namespace App\Http\Controllers\StoreAuth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('frontend.auth.store.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::guard('store')->attempt($credentials)) {
            return back()->with([
                'error' => 'Invalid login credentials',
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::guard('store')->user();
        $user->loadMissing(['role', 'store']);

        if (! $user->isStore()) {
            Auth::guard('store')->logout();
            $request->session()->migrate(true);
            $request->session()->regenerateToken();

            return back()->with([
                'error' => 'You are not a store user.',
            ]);
        }

        if (! $user->store || ! $user->store->isApproved()) {
            Auth::guard('store')->logout();
            $request->session()->migrate(true);
            $request->session()->regenerateToken();

            return back()->with([
                'error' => 'Your store account is pending approval.',
            ]);
        }

        return redirect()->route('store.profile');
    }

    public function logout(Request $request)
    {
        Auth::guard('store')->logout();
        $request->session()->migrate(true);
        $request->session()->regenerateToken();

        return redirect()->route('store.login');
    }
}
