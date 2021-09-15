<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\TextManager;

class TextManagerProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('textmanager',function($app){
            return new TextManager;
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }
}