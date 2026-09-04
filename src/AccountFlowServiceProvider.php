<?php

namespace ArtflowStudio\AccountFlow;

use ArtflowStudio\AccountFlow\Http\Middleware\Authorize;
use ArtflowStudio\AccountFlow\Http\Middleware\CheckAccountflowFeature;
use ArtflowStudio\AccountFlow\Http\Middleware\CheckAdminAccess;
use ArtflowStudio\AccountFlow\Listeners\AuditSubscriber;
use ArtflowStudio\AccountFlow\Models\Transaction;
use ArtflowStudio\AccountFlow\Services\AccountFlowManager;
use ArtflowStudio\AccountFlow\Support\ComponentResolver;
use ArtflowStudio\AccountFlow\Support\LegacyAliases;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AccountFlowServiceProvider extends ServiceProvider
{
    /**
     * Console commands registered when running in the console.
     *
     * @var list<class-string>
     */
    private const COMMANDS = [
        Console\InstallCommand::class,
        Console\AccountFlowMigrateCommand::class,
        Console\AccountFlowMigrateFreshCommand::class,
        Console\AccountFlowDbCommand::class,
        Console\Commands\CheckAccountflowStatus::class,
        Console\Commands\SeedAccountflowData::class,
        Console\Commands\ToggleFeature::class,
        Console\Commands\AnalyzeLivewireComponents::class,
        Console\Commands\SkillInstallCommand::class,
        Console\Commands\DelinkCommand::class,
        Console\Commands\PostPlannedPayments::class,
        Console\Commands\BackfillTransfers::class,
        Console\Commands\RecalculateBalances::class,
        Console\Commands\Diagnose::class,
    ];

    /**
     * Service classes bound as singletons and exposed through the manager.
     *
     * @var list<class-string>
     */
    private const SERVICES = [
        Services\TransactionService::class,
        Services\AccountService::class,
        Services\CategoryService::class,
        Services\PaymentMethodService::class,
        Services\BudgetService::class,
        Services\ReportService::class,
        Services\SettingsService::class,
        Services\AuditService::class,
        Services\FeatureService::class,
        Services\TransferService::class,
        Services\PlannedPaymentService::class,
        Services\AssetService::class,
        Services\LoanService::class,
        Services\EquityService::class,
        Services\MoneyService::class,
        Services\WalletService::class,
    ];

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/accountflow.php', 'accountflow');

        $this->app->singleton('accountflow', fn (): AccountFlowManager => new AccountFlowManager);
        $this->app->alias('accountflow', AccountFlowManager::class);

        foreach (self::SERVICES as $service) {
            $this->app->singleton($service);
        }
    }

    public function boot(): void
    {
        $this->registerLegacyAliases();
        $this->registerMiddleware();
        $this->registerLivewireComponents();
        $this->registerHostRelations();
        $this->registerEventSubscribers();
        $this->registerPublishing();
        $this->registerBladeDirectives();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'accountflow');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/accountflow.php');

        if ($this->app->runningInConsole()) {
            $this->commands(self::COMMANDS);
        }
    }

    /**
     * Alias the pre-0.3.0 `App\…\AccountFlow\*` class names to their current
     * equivalents so existing host applications keep working.
     */
    private function registerLegacyAliases(): void
    {
        if (config('accountflow.legacy_aliases', true)) {
            LegacyAliases::register();
        }
    }

    private function registerMiddleware(): void
    {
        $router = $this->app['router'];

        $router->aliasMiddleware('accountflow.feature', CheckAccountflowFeature::class);
        $router->aliasMiddleware('accountflow.admin', CheckAdminAccess::class);
        $router->aliasMiddleware('accountflow.can', Authorize::class);
    }

    /**
     * Teach Livewire how to find components that live in this package.
     */
    private function registerLivewireComponents(): void
    {
        ComponentResolver::register();
    }

    /**
     * Attach optional relations to host-application models.
     *
     * 0.2.x hardcoded `\App\Models\InvoicePayment` inside the Transaction
     * model, which made the package depend on one specific CRM. The relation
     * now exists only when the host application asks for it.
     */
    private function registerHostRelations(): void
    {
        $invoicePayment = config('accountflow.models.invoice_payment');

        if (is_string($invoicePayment) && class_exists($invoicePayment)) {
            Transaction::resolveRelationUsing(
                'invoicePayment',
                fn (Transaction $transaction) => $transaction->hasOne($invoicePayment),
            );
        }
    }

    /**
     * Wire the audit trail to domain events.
     *
     * Auditing is a listener rather than calls scattered through the services,
     * so a new write path cannot forget to log.
     */
    private function registerEventSubscribers(): void
    {
        Event::subscribe(AuditSubscriber::class);
    }

    /**
     * Publishable assets.
     *
     * Models, Livewire components and controllers are deliberately NOT
     * publishable. In 0.2.x they were, which meant the same fully-qualified
     * class name existed both in the package and in `app/`, and which one
     * loaded depended on autoloader ordering. Override behaviour through
     * config, container bindings and published views instead.
     */
    private function registerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/accountflow.php' => config_path('accountflow.php'),
        ], 'accountflow-config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/accountflow'),
        ], 'accountflow-views');

        $this->publishes([
            __DIR__.'/../public/assets' => public_path('vendor/artflow-studio/accountflow/assets'),
        ], 'accountflow-assets');

        $this->publishes([
            __DIR__.'/../database/seeders' => database_path('seeders'),
        ], 'accountflow-seeders');
    }

    private function registerBladeDirectives(): void
    {
        Blade::directive(
            'accountflowFeature',
            fn (string $expression): string => "<?php if(app('accountflow')->features()->isEnabled({$expression})): ?>",
        );
        Blade::directive('endaccountflowFeature', fn (): string => '<?php endif; ?>');

        Blade::directive(
            'featureEnabled',
            fn (string $expression): string => "<?php if(app('accountflow')->features()->isEnabled({$expression})): ?>",
        );
        Blade::directive('endFeatureEnabled', fn (): string => '<?php endif; ?>');

        Blade::directive(
            'featureDisabled',
            fn (string $expression): string => "<?php if(app('accountflow')->features()->isDisabled({$expression})): ?>",
        );
        Blade::directive('endFeatureDisabled', fn (): string => '<?php endif; ?>');

        // @accountflow(['table' => 'transactions']) — a standalone table, no layout or header.
        Blade::directive(
            'accountflow',
            fn (string $expression): string => "<?php echo \$__env->make('accountflow::components.table', {$expression}, \\Illuminate\\Support\\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>",
        );
    }
}
