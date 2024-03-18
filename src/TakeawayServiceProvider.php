<?php

namespace Foodticket\Takeaway;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class TakeawayServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishConfig();
        $this->registerRoutes();
    }

    public function register(): void
    {
        if ($this->app instanceof Application && $this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/takeaway.php' => config_path('takeaway.php'),
            ], 'config');
        }
    }

    protected function registerRoutes(): void
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });
    }

    protected function routeConfiguration(): array
    {
        return [
            'prefix' => config('takeaway.routes_prefix'),
            'middleware' => config('takeaway.routes_middleware'),
        ];
    }

    protected function publishConfig(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/takeaway.php' => config_path('takeaway.php'),
            ], 'config');
        }

        $this->mergeConfigFrom(__DIR__ . '/../config/takeaway.php', 'takeaway');
    }
}
