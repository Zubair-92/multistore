<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // ?? Force HTTPS in production
        if (config("app.env") === "production" || env("APP_ENV") === "production") {
            URL::forceScheme("https");
            
            // ?? Trust Render Proxies for correct asset URLs
            Request::setTrustedProxies(
                ["0.0.0.0/0", "2000::/3"], 
                Request::HEADER_X_FORWARDED_FOR | 
                Request::HEADER_X_FORWARDED_HOST | 
                Request::HEADER_X_FORWARDED_PORT | 
                Request::HEADER_X_FORWARDED_PROTO
            );
        }
    }
}
