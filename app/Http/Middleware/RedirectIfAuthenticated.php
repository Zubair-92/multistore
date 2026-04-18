<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $guards = $guards === [] ? ['admin', 'store', 'web'] : $guards;

        foreach ($guards as $guard) {
            if (! Auth::guard($guard)->check()) {
                continue;
            }

            return match ($guard) {
                'admin' => redirect()->route('admin.home'),
                'store' => redirect()->route('store.profile'),
                default => redirect()->route('frontend.home'),
            };
        }

        return $next($request);
    }

}
