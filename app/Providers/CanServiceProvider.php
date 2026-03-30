<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\CustomFacade\Can;

class CanServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->bind('can', function () {
            return new Can();
        });
    }
}
