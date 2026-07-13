<?php

namespace ArtflowStudio\AccountFlow;

use Illuminate\Support\Facades\File;
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
        $this->app['router']->aliasMiddleware('accountflow.admin', \ArtflowStudio\AccountFlow\App\Http\Middleware\CheckAdminAccess::class);

        // ============================================
        // Publish Configuration (only config is published by default)
        // ============================================
        $this->publishes([
            __DIR__ . '/config/accountflow.php' => config_path('accountflow.php'),
        ], 'accountflow-config');

        // Optionally publish views to allow host-app overrides:
        // php artisan vendor:publish --tag=accountflow-views
        $this->publishes([
            __DIR__ . '/resources/views/vendor/artflow-studio/accountflow' => resource_path('views/vendor/accountflow'),
        ], 'accountflow-views');

        // Publish models — run via accountflow:install or vendor:publish --tag=accountflow-models
        $this->publishes([
            __DIR__ . '/app/Models' => app_path('Models/AccountFlow'),
        ], 'accountflow-models');

        // Publish Livewire components — run via accountflow:install or vendor:publish --tag=accountflow-livewire
        $this->publishes([
            __DIR__ . '/app/Livewire/AccountFlow' => app_path('Livewire/AccountFlow'),
        ], 'accountflow-livewire');

        // Publish controllers — run via accountflow:install or vendor:publish --tag=accountflow-controllers
        $this->publishes([
            __DIR__ . '/app/Http/Controllers/AccountFlow' => app_path('Http/Controllers/AccountFlow'),
        ], 'accountflow-controllers');

        // ============================================
        // Load Views from package
        // ============================================
        $this->loadViewsFrom(__DIR__ . '/resources/views/vendor/artflow-studio/accountflow', 'accountflow');

        // Auto-discover migrations — php artisan migrate picks these up automatically
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        // ============================================
        // Load Routes from package
        // ============================================
        $routesPath = __DIR__ . '/routes/accountflow.php';

        if (File::exists($routesPath)) {
            $this->loadRoutesFrom($routesPath);
        }

        // ============================================
        // Register Console Commands
        // ============================================
        if ($this->app->runningInConsole()) {
            $this->commands([
                \ArtflowStudio\AccountFlow\App\Console\InstallCommand::class,
                \ArtflowStudio\AccountFlow\App\Console\AccountFlowLinkCommand::class,
                \ArtflowStudio\AccountFlow\App\Console\AccountFlowMigrateCommand::class,
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
                // Skill install
                \ArtflowStudio\AccountFlow\App\Console\Commands\SkillInstallCommand::class,
                // Delink
                \ArtflowStudio\AccountFlow\App\Console\Commands\DelinkCommand::class,
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
        // @accountflow(['table' => 'transactions']) — render a standalone table with no layout/header
        Blade::directive('accountflow', function ($expression) {
            return "<?php echo \$__env->make('accountflow::components.table', {$expression}, \\Illuminate\\Support\\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>";
        });    }

    /**
     * Register any application services.
     */
    public function register()
    {
        // ============================================
        // Package Class Autoloader
        // ============================================
        // Load App\Livewire\AccountFlow\*, App\Models\AccountFlow\*, and
        // App\Http\Controllers\AccountFlow\* from the package src directory.
        // This allows all classes to be resolved without requiring symlinks.
        $srcDir = __DIR__;

        spl_autoload_register(function (string $class) use ($srcDir): void {
            $map = [
                'App\\Livewire\\AccountFlow\\'         => $srcDir . '/app/Livewire/AccountFlow/',
                'App\\Models\\AccountFlow\\'           => $srcDir . '/app/Models/',
                'App\\Http\\Controllers\\AccountFlow\\' => $srcDir . '/app/Http/Controllers/AccountFlow/',
            ];

            foreach ($map as $prefix => $baseDir) {
                if (str_starts_with($class, $prefix)) {
                    $relative = substr($class, strlen($prefix));
                    $file     = $baseDir . str_replace('\\', '/', $relative) . '.php';

                    if (is_file($file)) {
                        require_once $file;
                    }

                    return;
                }
            }
        });

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

