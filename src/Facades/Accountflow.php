<?php

namespace ArtflowStudio\AccountFlow\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Accountflow Facade - Primary Interface for AccountFlow Services
 *
 * Provides convenient static access to all AccountFlow services:
 *
 * @method static \ArtflowStudio\AccountFlow\App\Services\TransactionService transactions()
 * @method static \ArtflowStudio\AccountFlow\App\Services\AccountService accounts()
 * @method static \ArtflowStudio\AccountFlow\App\Services\CategoryService categories()
 * @method static \ArtflowStudio\AccountFlow\App\Services\PaymentMethodService paymentMethods()
 * @method static \ArtflowStudio\AccountFlow\App\Services\BudgetService budgets()
 * @method static \ArtflowStudio\AccountFlow\App\Services\ReportService reports()
 * @method static \ArtflowStudio\AccountFlow\App\Services\SettingsService settings()
 * @method static \ArtflowStudio\AccountFlow\App\Services\AuditService audit()
 *
 * @example
 * use ArtflowStudio\AccountFlow\Facades\Accountflow;
 *
 * // Create income transaction
 * $transaction = Accountflow::transactions()->createIncome([
 *     'amount' => 1000,
 *     'description' => 'Sale',
 * ]);
 *
 * // Access account balance
 * $balance = Accountflow::accounts()->getBalance($accountId);
 *
 * // Get settings
 * $salesCategoryId = Accountflow::settings()->defaultSalesCategoryId();
 *
 * @see \ArtflowStudio\AccountFlow\App\Services\AccountFlowManager
 */
class Accountflow extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'accountflow';
    }
}
