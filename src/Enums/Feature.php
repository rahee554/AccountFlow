<?php

namespace ArtflowStudio\AccountFlow\Enums;

/**
 * Every togglable AccountFlow module.
 *
 * This is the single source of truth for feature keys. 0.2.x kept a 20-entry
 * alias map duplicated inside two `FeatureService` methods, and `Setting::defaults()`
 * listed a *different* set of keys — so several modules were unreachable on a
 * fresh install until the (destructive) seeder ran.
 *
 * The case value is the canonical settings key; `aliases()` holds the short
 * names used in route middleware and Blade directives.
 */
enum Feature: string
{
    case MultiAccounts = 'multi_accounts_module';
    case CustomCategory = 'custom_category';
    case Categories = 'categories_module';
    case Ledger = 'ledger_module';
    case Cashbook = 'cashbook_module';
    case TrialBalanceModule = 'trial_balance_module';
    case TrialBalanceReport = 'trial_balance_report';
    case ProfitLossReport = 'profit_loss_report';
    case Assets = 'assets_module';
    case Purchases = 'purchase_module';
    case MultiPaymentMethods = 'multi_payment_methods';
    case PaymentMethods = 'payment_methods_module';
    case Loans = 'loan_module';
    case UserWallets = 'user_wallet_module';
    case IncomeForm = 'income_form';
    case Equity = 'equity_module';
    case Budgets = 'budgets_module';
    case PlannedPayments = 'planned_payments_module';
    case TransactionTemplates = 'transaction_templates';
    case AuditTrail = 'audit_trail';
    case Transfers = 'transfers_module';

    /**
     * Short names accepted wherever a feature is referenced.
     *
     * @return list<string>
     */
    public function aliases(): array
    {
        return match ($this) {
            self::AuditTrail => ['audit', 'audit-trail'],
            self::Budgets => ['budgets'],
            self::PlannedPayments => ['planned_payments', 'planned-payments'],
            self::Assets => ['assets'],
            self::Loans => ['loans', 'loan'],
            self::UserWallets => ['wallets', 'user_wallets', 'users-wallets'],
            self::Equity => ['equity'],
            self::Cashbook => ['cashbook'],
            self::MultiAccounts => ['multi_accounts'],
            self::TransactionTemplates => ['templates'],
            self::PaymentMethods => ['payment_methods', 'payment-methods'],
            self::Categories => ['categories'],
            self::Transfers => ['transfers'],
            self::ProfitLossReport => ['profit_loss', 'profit-loss'],
            self::TrialBalanceReport => ['trial_balance', 'trial-balance'],
            self::Purchases => ['purchases'],
            default => [],
        };
    }

    /**
     * Resolve a canonical key or any alias to a case.
     */
    public static function tryParse(self|string $value): ?self
    {
        if ($value instanceof self) {
            return $value;
        }

        if ($case = self::tryFrom($value)) {
            return $case;
        }

        foreach (self::cases() as $case) {
            if (in_array($value, $case->aliases(), true)) {
                return $case;
            }
        }

        return null;
    }

    /**
     * Every module ships enabled. A fresh install should be fully usable
     * without running a seeder.
     */
    public function enabledByDefault(): bool
    {
        return true;
    }

    public function label(): string
    {
        return ucwords(str_replace(['_module', '_report', '_'], ['', ' report', ' '], $this->value));
    }

    /**
     * Canonical key => default value, for seeding and for Setting::defaults().
     *
     * @return array<string,string>
     */
    public static function defaults(): array
    {
        $defaults = [];

        foreach (self::cases() as $case) {
            $defaults[$case->value] = $case->enabledByDefault() ? 'enabled' : 'disabled';
        }

        return $defaults;
    }
}
