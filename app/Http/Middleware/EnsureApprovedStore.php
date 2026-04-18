<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureApprovedStore
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isStore()) {
            return redirect()->route('store.login');
        }

        $user->loadMissing('store');

        if (! $user->store || ! $user->store->isApproved()) {
            Auth::guard('store')->logout();
            $request->session()->migrate(true);
            $request->session()->regenerateToken();

            return redirect()
                ->route('store.login')
                ->with('error', 'Your store account is pending approval.');
        }

        return $next($request);
    }
}
