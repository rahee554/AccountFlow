<?php

namespace ArtflowStudio\AccountFlow\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class CheckAccountflowStatus extends Command
{
    protected $signature = 'accountflow:status';
    protected $description = 'Check AccountFlow migration and seeder status';

    public function handle()
    {
        $this->info('🔍 Checking AccountFlow Status...');
        $this->newLine();

        // Check migrations
        $this->info('📊 DATABASE TABLES STATUS');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $tables = [
            'accounts' => 'Accounts',
            'ac_payment_methods' => 'Payment Methods',
            'ac_categories' => 'Categories',
            'ac_transactions' => 'Transactions',
            'ac_transfers' => 'Transfers',
            'ac_assets' => 'Assets',
            'ac_budgets' => 'Budgets',
            'ac_audit_trail' => 'Audit Trail',
            'ac_settings' => 'Settings',
            'ac_loans' => 'Loans',
            'ac_loan_partners' => 'Loan Partners',
            'ac_user_wallets' => 'User Wallets',
        ];

        $allExist = true;
        foreach ($tables as $table => $name) {
            if (Schema::hasTable($table)) {
                $count = \DB::table($table)->count();
                $this->info("  ✓ {$name} ({$table}): {$count} records");
            } else {
                $this->error("  ✗ {$name} ({$table}): NOT FOUND");
                $allExist = false;
            }
        }

        $this->newLine();
        $this->info('🔧 FEATURE MODULES STATUS');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        if (Schema::hasTable('ac_settings')) {
            $features = [
                'multi_accounts_module' => 'Multi Accounts',
                'cashbook_module' => 'Cashbook',
                'trial_balance_module' => 'Trial Balance',
                'assets_module' => 'Assets',
                'purchase_module' => 'Purchase',
                'loan_module' => 'Loans',
                'user_wallet_module' => 'User Wallets',
                'equity_module' => 'Equity',
                'budgets_module' => 'Budgets',
                'planned_payments_module' => 'Planned Payments',
                'audit_trail' => 'Audit Trail',
                'transaction_templates' => 'Transaction Templates',
            ];

            foreach ($features as $key => $name) {
                $setting = \DB::table('ac_settings')->where('key', $key)->first();
                if ($setting) {
                    $status = $setting->value === 'enabled' ? '✓ ENABLED' : '✗ DISABLED';
                    $color = $setting->value === 'enabled' ? 'info' : 'comment';
                    $this->$color("  {$status} - {$name}");
                } else {
                    $this->warn("  ? NOT SET - {$name}");
                }
            }
        } else {
            $this->error('  Settings table not found!');
        }

        $this->newLine();

        if (!$allExist) {
            $this->warn('⚠️  Some tables are missing. Run migrations:');
            $this->line('   php artisan migrate');
            $this->newLine();
            return 1;
        }

        $this->info('✅ All AccountFlow tables exist!');
        $this->newLine();

        return 0;
    }
}
