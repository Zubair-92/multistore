<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminPermissionMiddleware
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = auth('admin')->user();

        if (! $user) {
            abort(403);
        }

        foreach ($permissions as $permission) {
            if ($user->hasAdminPermission($permission)) {
                return $next($request);
            }
        }

        abort(403, 'You do not have permission to access this section.');
    }
}
