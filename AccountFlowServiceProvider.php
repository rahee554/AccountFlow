<?php
namespace ArtflowStudio\AccountFlow;

use Illuminate\Support\ServiceProvider;

class AccountFlowServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Publish Configuration to /config
        $this->publishes([
            __DIR__.'/../config/accountflow.php' => config_path('accountflow.php'),
        ], 'config');

        // Load Views from vendor folder after publishing
        $this->loadViewsFrom(resource_path('views/vendor/accountflow'), 'accountflow');

        // Publish Views to /views/vendor/accountflow
        $this->publishes([
            __DIR__.'/../views' => resource_path('views/vendor/accountflow'),
        ], 'views');

        // Load and Publish Migrations
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'migrations');

            // Register Commands (e.g., Install Command)
            $this->commands([
                \ArtflowStudio\AccountFlow\Console\InstallCommand::class,
            ]);
        }
    }

    public function register()
    {
        // Merge the Config from your package with the app's config
        $this->mergeConfigFrom(
            __DIR__.'/../config/accountflow.php', 'accountflow'
        );
    }
}
