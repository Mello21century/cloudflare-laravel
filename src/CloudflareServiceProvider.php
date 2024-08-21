<?php

namespace Space\Cloudflare;

use Illuminate\Support\ServiceProvider;
use Space\Cloudflare\Services\Cloudflare;

class CloudflareServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views/','cloudflare');
        $this->publishes([__DIR__ . '/../config/cloudflare.php' => config_path('cloudflare.php')], 'cloudflare');

    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/cloudflare.php', 'cloudflare');

        $this->app->singleton('cloudflare', function () {
            $email = config('cloudflare.email');
            $apiKey = config('cloudflare.apiKey');
            return new Cloudflare(email: $email, apiKey: $apiKey);
        });
        $this->app->bind(Cloudflare::class, function () {
            $email = config('cloudflare.email');
            $apiKey = config('cloudflare.apiKey');
            return new Cloudflare(email: $email, apiKey: $apiKey);
        });
    }
}
