<?php

namespace ArtflowStudio\AccountFlow\Services;

/**
 * AccountFlowManager - Service Container
 *
 * Provides access to all AccountFlow services through a single manager.
 * This is the main entry point for the Accountflow facade.
 *
 * Usage:
 * - Via Facade: Accountflow::transactions()->create([...])
 * - Via Container: app('accountflow')->transactions()->create([...])
 */
class AccountFlowManager
{
    /**
     * Get TransactionService instance
     */
    public function transactions(): TransactionService
    {
        return app()->make(TransactionService::class);
    }

    /**
     * Get AccountService instance
     */
    public function accounts(): AccountService
    {
        return app()->make(AccountService::class);
    }

    /**
     * Get CategoryService instance
     */
    public function categories(): CategoryService
    {
        return app()->make(CategoryService::class);
    }

    /**
     * Get PaymentMethodService instance
     */
    public function paymentMethods(): PaymentMethodService
    {
        return app()->make(PaymentMethodService::class);
    }

    /**
     * Get BudgetService instance
     */
    public function budgets(): BudgetService
    {
        return app()->make(BudgetService::class);
    }

    /**
     * Get ReportService instance
     */
    public function reports(): ReportService
    {
        return app()->make(ReportService::class);
    }

    /**
     * Get SettingsService instance
     */
    public function settings(): SettingsService
    {
        return app()->make(SettingsService::class);
    }

    /**
     * Get AuditService instance
     */
    public function audit(): AuditService
    {
        return app()->make(AuditService::class);
    }

    /**
     * Get FeatureService instance
     */
    public function features(): FeatureService
    {
        return app()->make(FeatureService::class);
    }

    public function transfers(): TransferService
    {
        return app()->make(TransferService::class);
    }

    public function plannedPayments(): PlannedPaymentService
    {
        return app()->make(PlannedPaymentService::class);
    }

    /**
     * The plain-language front door: received / spent / moved / balance.
     */
    public function money(): MoneyService
    {
        return app()->make(MoneyService::class);
    }

    public function wallets(): WalletService
    {
        return app()->make(WalletService::class);
    }

    public function assets(): AssetService
    {
        return app()->make(AssetService::class);
    }

    public function loans(): LoanService
    {
        return app()->make(LoanService::class);
    }

    public function equity(): EquityService
    {
        return app()->make(EquityService::class);
    }
}
