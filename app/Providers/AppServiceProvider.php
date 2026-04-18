<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use App\Http\Controllers\Frontend\CartController;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //Vite::prefetch(concurrency: 3);
        Paginator::useBootstrapFive();

        if ($this->app->environment('testing')) {
            config([
                'view.compiled' => storage_path('framework/testing/views'),
            ]);

            File::ensureDirectoryExists(config('view.compiled'));
        }

        view()->composer('*', function ($view) {
            $currentGuard = Auth::guard('store')->check()
                ? 'store'
                : (Auth::guard('web')->check() ? 'web' : null);

            $currentUser = $currentGuard ? Auth::guard($currentGuard)->user() : null;

            $view->with([
                'cartCount' => CartController::getCartCount(),
                'currentGuard' => $currentGuard,
                'currentUser' => $currentUser,
            ]);
        });
    }
}
