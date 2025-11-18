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
            'budgets_module' => 'Budgets',
            'planned_payments_module' => 'Planned Payments',
            'trial_balance_module' => 'Reports & Trial Balance',
            'assets_module' => 'Assets',
            'loan_module' => 'Loans',
            'user_wallet_module' => 'User Wallets',
            'equity_module' => 'Equity',
            'cashbook_module' => 'Cashbook',
            'multi_accounts_module' => 'Multi Accounts',
            'transaction_templates' => 'Transaction Templates',
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
