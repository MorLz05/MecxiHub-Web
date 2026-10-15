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
        // Forzar HTTPS y host correcto cuando venimos por ngrok
        $host = request()->header('host') ?? request()->getHost();

        if ($host && str_contains($host, 'ngrok')) {
            URL::forceScheme('https');
            URL::forceRootUrl('https://' . $host);
        }
    }
}
