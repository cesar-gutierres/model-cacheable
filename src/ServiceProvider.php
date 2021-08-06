<?php

namespace Leve\Cacheable;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Leve\Cacheable\Commands\FlushCommand;
use Leve\Cacheable\Commands\ReIndexCommand;

class ServiceProvider extends BaseServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/model_cached.php' => config_path('model_cached.php')
        ], 'config');


        // $this->app->get('cacheable')->register();
    }

    /**
     * @return void
     */
    public function register()
    {
        // register
        $this->app->singleton('cacheable', Strategy::class);

        $this->mergeConfigFrom(__DIR__ . '/../config/model_cached.php', 'model_cached');

        // register commands
        $this->registerCommands();
    }

    /**
     * @return void
     */
    public function registerCommands(): void
    {
        $this->commands([
            FlushCommand::class,
            ReIndexCommand::class
        ]);
    }
}
