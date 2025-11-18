<?php

namespace ArtflowStudio\AccountFlow\App\Console\Commands;

use Illuminate\Console\Command;
use ArtflowStudio\AccountFlow\Facades\Accountflow;

class TestSettingsService extends Command
{
    protected $signature = 'accountflow:test-settings';
    protected $description = 'Test SettingsService methods';

    public function handle()
    {
        $this->info('🧪 Testing SettingsService...');
        $this->newLine();

        try {
            $service = Accountflow::settings();
            $this->info('✓ SettingsService loaded: ' . get_class($service));
            $this->newLine();

            // Test method existence
            $methods = [
                'get',
                'set',
                'defaultSalesCategoryId',
                'defaultExpenseCategoryId',
                'defaultAccountId',
                'defaultPaymentMethodId',
                'defaultTransactionType',
            ];

            foreach ($methods as $method) {
                if (method_exists($service, $method)) {
                    $this->info("  ✓ Method exists: {$method}()");
                } else {
                    $this->error("  ✗ Method missing: {$method}()");
                }
            }

            $this->newLine();
            $this->info('✅ SettingsService test completed!');
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ TEST FAILED: ' . $e->getMessage());
            return 1;
        }
    }
}
