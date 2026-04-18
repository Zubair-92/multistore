<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo($request)
    {
        // If the request does not expect JSON, redirect to your custom login page
        if (! $request->expectsJson()) {

            // If accessing store routes
            if ($request->is('store/*')) {
                return route('store.login');
            }

            // If accessing admin routes
            if ($request->is('admin/*')) {
                return route('admin.auth.login');
            }

            return route('login');  // <-- your custom login route name
        }
    }
}
