<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{ 

    public function showLoginForm()
    {
        return view('frontend.auth.login');
    }


    public function login(Request $request)
    {
        // Validate login input
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Try to log in the user
        if (Auth::guard('web')->attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::guard('web')->user();

            // MERGE GUEST CART → USER CART
            $sessionCart = session()->get('cart', []);

            if (!empty($sessionCart)) {

                $cart = \App\Models\Cart::firstOrCreate([
                    'user_id' => $user->id
                ]);

                foreach ($sessionCart as $item) {
                    $cart->items()->updateOrCreate(
                        ['product_id' => $item['product_id']],
                        [
                            'quantity' => \DB::raw("quantity + {$item['quantity']}"),
                            'price' => $item['price']
                        ]
                    );
                }

                // Remove guest cart
                session()->forget('cart');
            }

            // Redirect after login
            return redirect()->intended(route('dashboard', absolute: false));
        }

        return back()->withErrors([
            'email' => 'Invalid login credentials.',
        ]);
    }


    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->migrate(true);
        $request->session()->regenerateToken();

        return redirect('/');
    }

}
