<?php

namespace ArtflowStudio\AccountFlow;

use Illuminate\Support\ServiceProvider;

class AccountFlowServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Publish Config
        $this->publishes([
            __DIR__ . '/config/accountflow.php' => config_path('accountflow.php'),
        ], 'accountflow-config');

        // Publish Migrations
        $this->publishes([
            __DIR__ . '/database/migrations/' => database_path('migrations'),
        ], 'accountflow-migrations');

        // Publish Seeders
        $this->publishes([
            __DIR__ . '/database/seeders/' => database_path('seeders'),
        ], 'accountflow-seeders');

        // Publish Views
        $this->publishes([
            __DIR__ . '/views/vendor/accountflow' => resource_path('views/vendor/accountflow'),
        ], 'accountflow-views');

        // Publish Models
        $this->publishes([
            __DIR__ . '/Models/AccountFlow/' => app_path('Models/AccountFlow'),
        ], 'accountflow-models');

        // Publish Controllers
        $this->publishes([
            __DIR__ . '/Controllers/AccountFlow/' => app_path('Http/Controllers/AccountFlow'),
        ], 'accountflow-controllers');

        // Publish Routes
        $this->publishes([
            __DIR__ . '/routes/accountflow.php' => base_path('routes/accountflow.php'),
        ], 'accountflow-routes');

        // Publish Assets
        $this->publishes([
            __DIR__ . '/public/vendor/accountflow/' => public_path('vendor/accountflow'),
        ], 'accountflow-assets');

        // Load Views
        $this->loadViewsFrom(__DIR__ . '/views/vendor/accountflow', 'accountflow');

        // Load Routes
        $this->loadRoutesFrom(__DIR__ . '/routes/accountflow.php');

        // Registering the command
        if ($this->app->runningInConsole()) {
            $this->commands([
                \ArtflowStudio\AccountFlow\Console\InstallCommand::class,
                \ArtflowStudio\AccountFlow\Console\AccountFlowMigrateCommand::class,

            ]);
        }

        // Merge Default Config
        $this->mergeConfigFrom(
            __DIR__ . '/config/accountflow.php', 'accountflow'
        );
    }

    public function register()
    {
        // Register any additional bindings or services
    }
}
