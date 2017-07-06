<?php namespace Cviebrock\EloquentLoggable;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;


class ServiceProvider extends BaseServiceProvider
{

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../resources/config/loggable.php' => config_path('loggable.php'),
        ], 'config');
    }
}
