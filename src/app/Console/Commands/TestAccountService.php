<?php

namespace ArtflowStudio\AccountFlow\App\Console\Commands;

use Illuminate\Console\Command;
use ArtflowStudio\AccountFlow\Facades\Accountflow;

class TestAccountService extends Command
{
    protected $signature = 'accountflow:test-accounts';
    protected $description = 'Test AccountService methods';

    public function handle()
    {
        $this->info('🧪 Testing AccountService...');
        $this->newLine();

        try {
            $service = Accountflow::accounts();
            $this->info('✓ AccountService loaded: ' . get_class($service));
            $this->newLine();

            // Test method existence
            $methods = [
                'create',
                'update',
                'delete',
                'getById',
                'getAll',
                'getBalance',
                'addToBalance',
                'subtractFromBalance',
                'updateAllAccountBalances',
            ];

            foreach ($methods as $method) {
                if (method_exists($service, $method)) {
                    $this->info("  ✓ Method exists: {$method}()");
                } else {
                    $this->error("  ✗ Method missing: {$method}()");
                }
            }

            $this->newLine();
            $this->info('✅ AccountService test completed!');
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ TEST FAILED: ' . $e->getMessage());
            return 1;
        }
    }
}
