<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        // Robust Reverse Proxy & GitHub Codespaces HTTPS/Host Detection
        $forwardedHost = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? null;
        $forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null;
        $isHttps = ($forwardedProto === 'https') 
            || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
            || ($forwardedHost && str_contains($forwardedHost, 'github.dev'))
            || (isset($_SERVER['HTTP_HOST']) && str_contains($_SERVER['HTTP_HOST'], 'github.dev'));

        if ($isHttps) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        if ($forwardedHost) {
            $scheme = $isHttps ? 'https' : 'http';
            \Illuminate\Support\Facades\URL::forceRootUrl("{$scheme}://{$forwardedHost}");
        }
    }
}
