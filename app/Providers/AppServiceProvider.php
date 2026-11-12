<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        // Never force HTTPS on localhost / 127.0.0.1 or local environment
        $host = request()->getHost();
        $isLocal = app()->environment('local')
            || in_array($host, ['localhost', '127.0.0.1', '::1'])
            || str_contains($host, 'localhost')
            || str_contains($host, '127.0.0.1');

        if (!$isLocal) {
            if (request()->isSecure()
                || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
                || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
                || str_starts_with(config('app.url'), 'https://')) {
                URL::forceScheme('https');
            }
        }
    }
}
