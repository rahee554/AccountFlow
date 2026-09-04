<?php

namespace ArtflowStudio\AccountFlow\Enums;

/**
 * Every action AccountFlow authorizes.
 *
 * `view-*` abilities gate reading a screen. `manage-*` abilities gate anything
 * that writes. Management abilities fall back to the configured admin check
 * when the host application has not defined a gate for them; view abilities
 * fall back to "any authenticated user", because the route middleware stack
 * has already required authentication.
 *
 * Define a gate to override either default:
 *
 *   Gate::define('accountflow.manage-transactions', fn ($user) => $user->isAccountant());
 */
enum Ability: string
{
    case ViewDashboard = 'view-dashboard';
    case ViewTransactions = 'view-transactions';
    case ManageTransactions = 'manage-transactions';
    case ViewAccounts = 'view-accounts';
    case ManageAccounts = 'manage-accounts';
    case ViewCategories = 'view-categories';
    case ManageCategories = 'manage-categories';
    case ViewPaymentMethods = 'view-payment-methods';
    case ManagePaymentMethods = 'manage-payment-methods';
    case ViewTransfers = 'view-transfers';
    case ManageTransfers = 'manage-transfers';
    case ViewBudgets = 'view-budgets';
    case ManageBudgets = 'manage-budgets';
    case ViewAssets = 'view-assets';
    case ManageAssets = 'manage-assets';
    case ViewLoans = 'view-loans';
    case ManageLoans = 'manage-loans';
    case ViewEquity = 'view-equity';
    case ManageEquity = 'manage-equity';
    case ViewPlannedPayments = 'view-planned-payments';
    case ManagePlannedPayments = 'manage-planned-payments';
    case ViewWallets = 'view-wallets';
    case ManageWallets = 'manage-wallets';
    case ViewTemplates = 'view-templates';
    case ManageTemplates = 'manage-templates';
    case ViewReports = 'view-reports';
    case ViewAuditTrail = 'view-audit-trail';
    case ManageSettings = 'manage-settings';
    case ManageFeatures = 'manage-features';

    /**
     * Does this ability write? Management abilities default to admin-only.
     */
    public function isManagement(): bool
    {
        return str_starts_with($this->value, 'manage-');
    }

    /**
     * The gate name the host application can define to override the default.
     */
    public function gate(): string
    {
        return config('accountflow.authorization.gate_prefix', 'accountflow').'.'.$this->value;
    }

    public function label(): string
    {
        return ucfirst(str_replace('-', ' ', $this->value));
    }
}
