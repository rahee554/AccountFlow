<?php

namespace ArtflowStudio\AccountFlow\App\Console\Commands;

use Illuminate\Console\Command;

class ToggleFeature extends Command
{
    protected $signature = 'accountflow:feature {feature} {status}';
    protected $description = 'Enable or disable AccountFlow features';

    public function handle()
    {
        $feature = $this->argument('feature');
        $status = $this->argument('status');

        if (!in_array($status, ['enable', 'disable', 'enabled', 'disabled'])) {
            $this->error('Status must be: enable, disable, enabled, or disabled');
            return 1;
        }

        $statusValue = (str_starts_with($status, 'enable')) ? 'enabled' : 'disabled';

        $features = [
            'audit' => 'audit_trail',
            'audit-trail' => 'audit_trail',
            'budgets' => 'budgets_module',
            'planned-payments' => 'planned_payments_module',
            'reports' => 'trial_balance_module',
            'assets' => 'assets_module',
            'loans' => 'loan_module',
            'wallets' => 'user_wallet_module',
            'equity' => 'equity_module',
            'cashbook' => 'cashbook_module',
            'multi-accounts' => 'multi_accounts_module',
            'templates' => 'transaction_templates',
        ];

        $featureKey = $features[$feature] ?? $feature;

        try {
            $setting = \DB::table('ac_settings')->where('key', $featureKey)->first();

            if (!$setting) {
                $this->error("Feature '{$feature}' not found!");
                $this->newLine();
                $this->info('Available features:');
                foreach (array_keys($features) as $f) {
                    $this->line("  - {$f}");
                }
                return 1;
            }

            \DB::table('ac_settings')
                ->where('key', $featureKey)
                ->update(['value' => $statusValue]);

            $emoji = $statusValue === 'enabled' ? '✅' : '❌';
            $this->info("{$emoji} Feature '{$feature}' is now {$statusValue}");

            return 0;

        } catch (\Exception $e) {
            $this->error('Failed to update feature: ' . $e->getMessage());
            return 1;
        }
    }
}
