<?php

namespace ArtflowStudio\AccountFlow\Services;

use ArtflowStudio\AccountFlow\App\Services\TransactionService;
use ArtflowStudio\AccountFlow\App\Services\AccountService;
use ArtflowStudio\AccountFlow\App\Services\CategoryService;
use ArtflowStudio\AccountFlow\App\Services\PaymentMethodService;
use ArtflowStudio\AccountFlow\App\Services\BudgetService;
use ArtflowStudio\AccountFlow\App\Services\ReportService;
use ArtflowStudio\AccountFlow\App\Services\SettingsService;
use ArtflowStudio\AccountFlow\App\Services\AuditService;
use ArtflowStudio\AccountFlow\App\Services\FeatureService;

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
}
