<?php

namespace ArtflowStudio\AccountFlow;

use Illuminate\Support\ServiceProvider;

class AccountFlowServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot()
    {
        // ============================================
        // Publish Configuration (only config is published)
        // ============================================
        $this->publishes([
            __DIR__ . '/config/accountflow.php' => config_path('accountflow.php'),
        ], 'accountflow-config');

        // ============================================
        // Load Views from package
        // ============================================
        $this->loadViewsFrom(__DIR__ . '/resources/views/vendor/artflow-studio/accountflow', 'accountflow');

        // ============================================
        // Load Routes from package
        // ============================================
        // Only load routes if the classes are already published/available
        try {
            $this->loadRoutesFrom(__DIR__ . '/routes/accountflow.php');
        } catch (\Exception $e) {
            // Routes will fail until symlinks are created, that's ok
        }

        // ============================================
        // Register Console Commands
        // ============================================
        if ($this->app->runningInConsole()) {
            $this->commands([
                \ArtflowStudio\AccountFlow\App\Console\InstallCommand::class,
                \ArtflowStudio\AccountFlow\App\Console\AccountFlowLinkCommand::class,
                \ArtflowStudio\AccountFlow\App\Console\AccountFlowSyncCommand::class,
                \ArtflowStudio\AccountFlow\App\Console\AccountFlowDbCommand::class,
            ]);
        }

        // ============================================
        // Merge Default Config
        // ============================================
        $this->mergeConfigFrom(
            __DIR__ . '/config/accountflow.php',
            'accountflow'
        );
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        // Register any additional bindings or services
        // Bind the AccountFlow service into the container if needed
    }
}

