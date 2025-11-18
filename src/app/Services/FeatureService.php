<?php

namespace ArtflowStudio\AccountFlow\App\Services;

/**
 * FeatureService - Manage feature toggles
 * 
 * Handles enabling/disabling of AccountFlow features
 */
class FeatureService
{
    /**
     * Check if a feature is enabled
     */
    public function isEnabled(string $feature): bool
    {
        $featureMap = [
            'audit' => 'audit_trail',
            'audit_trail' => 'audit_trail',
            'budgets' => 'budgets_module',
            'planned_payments' => 'planned_payments_module',
            'reports' => 'trial_balance_module',
            'assets' => 'assets_module',
            'loans' => 'loan_module',
            'wallets' => 'user_wallet_module',
            'equity' => 'equity_module',
            'cashbook' => 'cashbook_module',
            'multi_accounts' => 'multi_accounts_module',
            'templates' => 'transaction_templates',
            'transaction_templates' => 'transaction_templates',
            'payment_methods' => 'payment_methods_module',
            'categories' => 'categories_module',
            'transfers' => 'transfers_module',
            'profit_loss' => 'profit_loss_report',
            'trial_balance' => 'trial_balance_report',
        ];

        $key = $featureMap[$feature] ?? $feature;

        $setting = \DB::table('ac_settings')->where('key', $key)->first();

        return $setting && $setting->value === 'enabled';
    }

    /**
     * Check if a feature is disabled
     */
    public function isDisabled(string $feature): bool
    {
        return !$this->isEnabled($feature);
    }

    /**
     * Enable a feature
     */
    public function enable(string $feature): bool
    {
        return $this->toggle($feature, 'enabled');
    }

    /**
     * Disable a feature
     */
    public function disable(string $feature): bool
    {
        return $this->toggle($feature, 'disabled');
    }

    /**
     * Toggle a feature
     */
    public function toggle(string $feature, string $status): bool
    {
        $featureMap = [
            'audit' => 'audit_trail',
            'audit_trail' => 'audit_trail',
            'budgets' => 'budgets_module',
            'planned_payments' => 'planned_payments_module',
            'reports' => 'trial_balance_module',
            'assets' => 'assets_module',
            'loans' => 'loan_module',
            'wallets' => 'user_wallet_module',
            'equity' => 'equity_module',
            'cashbook' => 'cashbook_module',
            'multi_accounts' => 'multi_accounts_module',
            'templates' => 'transaction_templates',
            'transaction_templates' => 'transaction_templates',
            'payment_methods' => 'payment_methods_module',
            'categories' => 'categories_module',
            'transfers' => 'transfers_module',
            'profit_loss' => 'profit_loss_report',
            'trial_balance' => 'trial_balance_report',
        ];

        $key = $featureMap[$feature] ?? $feature;

        try {
            \DB::table('ac_settings')
                ->where('key', $key)
                ->update(['value' => $status]);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get all features and their status
     */
    public function getAllFeatures(): array
    {
        $features = [
            'audit_trail' => 'Audit Trail',
            'budgets_module' => 'Budgets Module',
            'planned_payments_module' => 'Planned Payments Module',
            'trial_balance_module' => 'Trial Balance Module',
            'assets_module' => 'Assets Module',
            'loan_module' => 'Loan Module',
            'user_wallet_module' => 'User Wallet Module',
            'equity_module' => 'Equity Module',
            'cashbook_module' => 'Cashbook Module',
            'multi_accounts_module' => 'Multi Accounts Module',
            'transaction_templates' => 'Transaction Templates',
            'payment_methods_module' => 'Payment Methods',
            'categories_module' => 'Custom Categories',
            'transfers_module' => 'Account Transfers',
            'profit_loss_report' => 'Profit & Loss Report',
            'trial_balance_report' => 'Trial Balance Report',
        ];

        $result = [];
        foreach ($features as $key => $name) {
            $setting = \DB::table('ac_settings')->where('key', $key)->first();
            $result[$key] = [
                'name' => $name,
                'enabled' => $setting && $setting->value === 'enabled',
                'key' => $key,
            ];
        }

        return $result;
    }
}
