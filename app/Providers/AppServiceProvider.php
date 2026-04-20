<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // ?? Force HTTPS in production
        if (config("app.env") === "production" || env("APP_ENV") === "production") {
            URL::forceScheme("https");
        }

        // ?? Share global variables with all views
        View::composer("*", function ($view) {
            $currentGuard = null;
            $currentUser = null;

            if (Auth::guard("web")->check()) {
                $currentGuard = "web";
                $currentUser = Auth::guard("web")->user();
            } elseif (Auth::guard("store")->check()) {
                $currentGuard = "store";
                $currentUser = Auth::guard("store")->user();
            }

            $cartCount = 0;
            if ($currentUser) {
                $cart = Cart::where("user_id", $currentUser->id)->first();
                $cartCount = $cart ? $cart->items()->count() : 0;
            }

            $view->with([
                "currentGuard" => $currentGuard,
                "currentUser" => $currentUser,
                "cartCount" => $cartCount,
            ]);
        });
    }
}
