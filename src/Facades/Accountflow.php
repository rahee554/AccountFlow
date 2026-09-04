<?php

namespace ArtflowStudio\AccountFlow\Facades;

use ArtflowStudio\AccountFlow\Services\AccountFlowManager;
use ArtflowStudio\AccountFlow\Services\AccountService;
use ArtflowStudio\AccountFlow\Services\AssetService;
use ArtflowStudio\AccountFlow\Services\AuditService;
use ArtflowStudio\AccountFlow\Services\BudgetService;
use ArtflowStudio\AccountFlow\Services\CategoryService;
use ArtflowStudio\AccountFlow\Services\EquityService;
use ArtflowStudio\AccountFlow\Services\FeatureService;
use ArtflowStudio\AccountFlow\Services\LoanService;
use ArtflowStudio\AccountFlow\Services\MoneyService;
use ArtflowStudio\AccountFlow\Services\PaymentMethodService;
use ArtflowStudio\AccountFlow\Services\PlannedPaymentService;
use ArtflowStudio\AccountFlow\Services\ReportService;
use ArtflowStudio\AccountFlow\Services\SettingsService;
use ArtflowStudio\AccountFlow\Services\TransactionService;
use ArtflowStudio\AccountFlow\Services\TransferService;
use ArtflowStudio\AccountFlow\Services\WalletService;
use Illuminate\Support\Facades\Facade;

/**
 * The entry point to every AccountFlow service.
 *
 * @method static MoneyService money()
 * @method static TransactionService transactions()
 * @method static AccountService accounts()
 * @method static CategoryService categories()
 * @method static PaymentMethodService paymentMethods()
 * @method static BudgetService budgets()
 * @method static ReportService reports()
 * @method static SettingsService settings()
 * @method static AuditService audit()
 * @method static FeatureService features()
 * @method static TransferService transfers()
 * @method static PlannedPaymentService plannedPayments()
 * @method static AssetService assets()
 * @method static LoanService loans()
 * @method static EquityService equity()
 * @method static WalletService wallets()
 *
 * @see AccountFlowManager
 *
 * @example
 * Accountflow::transactions()->income(1500, 'Invoice #221 payment');
 * Accountflow::accounts()->getBalance($accountId);
 * Accountflow::reports()->profitAndLoss('2026-01-01', '2026-12-31');
 * Accountflow::features()->isEnabled('budgets');
 */
class Accountflow extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'accountflow';
    }
}
