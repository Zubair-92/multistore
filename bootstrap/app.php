<?php
require_once __DIR__ . '/../database_url_fix.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php', //added new
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class, 
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Aliases for route middleware
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'admin.permission' => \App\Http\Middleware\AdminPermissionMiddleware::class,
            'store.approved' => \App\Http\Middleware\EnsureApprovedStore::class,
        ]);

        // IMPORTANT: Replace Laravel's default Authenticate middleware
        $middleware->replace(
            \Illuminate\Auth\Middleware\Authenticate::class,
            \App\Http\Middleware\Authenticate::class
        );
    })


    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TokenMismatchException $e, $request) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Your session expired. Please refresh the page and try again.',
                ], 419);
            }

            return redirect()
                ->back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->with('error', 'Your session expired. Please try again.');
        });
    })->create();

