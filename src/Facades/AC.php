<?php

namespace ArtflowStudio\AccountFlow\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * AC Facade - Shorthand for AccountFlow Services
 *
 * Provides convenient static access to AccountFlow services with short syntax:
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
 * @see \ArtflowStudio\AccountFlow\App\Services\AccountFlowManager
 */
class AC extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'accountflow';
    }
}
