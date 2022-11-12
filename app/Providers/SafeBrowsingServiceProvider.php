<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\SafeBrowsing;

class SafeBrowsingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('SafeBrowsing', function($app){
            return new SafeBrowsing();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
