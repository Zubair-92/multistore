<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::guard('admin')->attempt($credentials)) {
            return back()->withErrors([
                'email' => 'Invalid login credentials',
            ]);
        }

        $request->session()->regenerate();

        $userRole = Auth::guard('admin')->user()->role->name;

        if (! in_array($userRole, ['admin', 'sub_admin']) || ! Auth::guard('admin')->user()->is_active) {
            Auth::guard('admin')->logout();
            $request->session()->migrate(true);
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'You are not authorized to access the admin panel.',
            ]);
        }

        return redirect()->route('admin.home');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->migrate(true);
        $request->session()->regenerateToken();

        return redirect()->route('admin.auth.login');
    }
}
