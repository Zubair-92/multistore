<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, ...$roles)
    {
        $normalizedRoles = collect($roles)
            ->map(fn ($role) => $role === 'subadmin' ? 'sub_admin' : $role)
            ->all();

        $user = collect([
            $request->user('admin'),
            $request->user('store'),
            $request->user('web'),
            Auth::user(),
        ])->filter()->first(fn ($candidate) => $candidate->hasAnyRole($normalizedRoles));

        if (! $user) {
            abort(403);
        }

        return $next($request);
    }

}
