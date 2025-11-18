<?php

namespace ArtflowStudio\AccountFlow;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AccountFlowServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot()
    {
        // ============================================
        // Register Middleware Alias
        // ============================================
        $this->app['router']->aliasMiddleware('accountflow.feature', \ArtflowStudio\AccountFlow\App\Http\Middleware\CheckAccountflowFeature::class);

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
                // Test commands
                \ArtflowStudio\AccountFlow\App\Console\Commands\TestAccountflowFacade::class,
                \ArtflowStudio\AccountFlow\App\Console\Commands\TestTransactionService::class,
                \ArtflowStudio\AccountFlow\App\Console\Commands\TestAccountService::class,
                \ArtflowStudio\AccountFlow\App\Console\Commands\TestSettingsService::class,
                \ArtflowStudio\AccountFlow\App\Console\Commands\TestContainerBindings::class,
                \ArtflowStudio\AccountFlow\App\Console\Commands\TestAllServices::class,
                \ArtflowStudio\AccountFlow\App\Console\Commands\TestRealUsage::class,
                // Real-world commands
                \ArtflowStudio\AccountFlow\App\Console\Commands\CheckAccountflowStatus::class,
                \ArtflowStudio\AccountFlow\App\Console\Commands\SeedAccountflowData::class,
                \ArtflowStudio\AccountFlow\App\Console\Commands\ToggleFeature::class,
                \ArtflowStudio\AccountFlow\App\Console\Commands\AnalyzeLivewireComponents::class,
                \ArtflowStudio\AccountFlow\App\Console\Commands\TestFeatureService::class,
                \ArtflowStudio\AccountFlow\App\Console\Commands\RunAllTests::class,
            ]);
        }

        // ============================================
        // Merge Default Config
        // ============================================
        $this->mergeConfigFrom(
            __DIR__ . '/config/accountflow.php',
            'accountflow'
        );

        // ============================================
        // Register Blade Directives
        // ============================================
        Blade::directive('accountflowFeature', function ($expression) {
            return "<?php if(app('accountflow')->features()->isEnabled({$expression})): ?>";
        });

        Blade::directive('endaccountflowFeature', function () {
            return "<?php endif; ?>";
        });

        Blade::directive('featureEnabled', function ($expression) {
            return "<?php if(\ArtflowStudio\AccountFlow\Facades\Accountflow::features()->isEnabled({$expression})): ?>";
        });

        Blade::directive('endFeatureEnabled', function () {
            return "<?php endif; ?>";
        });

        Blade::directive('featureDisabled', function ($expression) {
            return "<?php if(\ArtflowStudio\AccountFlow\Facades\Accountflow::features()->isDisabled({$expression})): ?>";
        });

        Blade::directive('endFeatureDisabled', function () {
            return "<?php endif; ?>";
        });
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        // Register the AccountFlow manager into the container
        $this->app->singleton('accountflow', function () {
            return new \ArtflowStudio\AccountFlow\Services\AccountFlowManager();
        });

        // Register all services as singletons for easy access
        $this->app->singleton(\ArtflowStudio\AccountFlow\App\Services\TransactionService::class);
        $this->app->singleton(\ArtflowStudio\AccountFlow\App\Services\AccountService::class);
        $this->app->singleton(\ArtflowStudio\AccountFlow\App\Services\CategoryService::class);
        $this->app->singleton(\ArtflowStudio\AccountFlow\App\Services\PaymentMethodService::class);
        $this->app->singleton(\ArtflowStudio\AccountFlow\App\Services\BudgetService::class);
        $this->app->singleton(\ArtflowStudio\AccountFlow\App\Services\ReportService::class);
        $this->app->singleton(\ArtflowStudio\AccountFlow\App\Services\SettingsService::class);
        $this->app->singleton(\ArtflowStudio\AccountFlow\App\Services\AuditService::class);
        $this->app->singleton(\ArtflowStudio\AccountFlow\App\Services\FeatureService::class);

        // Register aliases for the facade
        $this->app->alias('accountflow', \ArtflowStudio\AccountFlow\Services\AccountFlowManager::class);
    }
}

