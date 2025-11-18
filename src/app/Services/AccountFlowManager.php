<?php

namespace ArtflowStudio\AccountFlow\App\Services;

/**
 * AccountFlowManager - Service Container
 *
 * Provides access to all AccountFlow services through a single manager.
 * Register services here and expose them via the container.
 *
 * Usage:
 * - Via Facade: AC::transactions()->create([...])
 * - Via Container: app('accountflow')->transactions()->create([...])
 * - Direct: TransactionService::create([...])
 */
class AccountFlowManager
{
    protected $services = [];

    public function __construct()
    {
        // Register services
        $this->services['transactions'] = TransactionService::class;
        $this->services['accounts'] = AccountService::class;
        $this->services['categories'] = CategoryService::class;
        $this->services['paymentMethods'] = PaymentMethodService::class;
        $this->services['budgets'] = BudgetService::class;
        $this->services['reports'] = ReportService::class;
        $this->services['settings'] = SettingsService::class;
        $this->services['audit'] = AuditService::class;
    }

    /**
     * Get a service instance
     */
    public function transactions(): TransactionService
    {
        return app()->make(TransactionService::class);
    }

    public function accounts(): AccountService
    {
        return app()->make(AccountService::class);
    }

    public function categories(): CategoryService
    {
        return app()->make(CategoryService::class);
    }

    public function paymentMethods(): PaymentMethodService
    {
        return app()->make(PaymentMethodService::class);
    }

    public function budgets(): BudgetService
    {
        return app()->make(BudgetService::class);
    }

    public function reports(): ReportService
    {
        return app()->make(ReportService::class);
    }

    public function settings(): SettingsService
    {
        return app()->make(SettingsService::class);
    }

    public function audit(): AuditService
    {
        return app()->make(AuditService::class);
    }

    /**
     * Get all registered services
     */
    public function services(): array
    {
        return $this->services;
    }

    /**
     * Dynamic service access
     */
    public function __call(string $method, array $parameters)
    {
        // Check if service exists
        $service = ucfirst($method);
        $serviceClass = "ArtflowStudio\\AccountFlow\\App\\Services\\{$service}Service";

        if (class_exists($serviceClass)) {
            return app($serviceClass);
        }

        throw new \BadMethodCallException("Service [{$method}] not found in AccountFlow.");
    }
}
