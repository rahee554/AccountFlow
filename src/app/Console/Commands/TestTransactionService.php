<?php

namespace ArtflowStudio\AccountFlow\App\Console\Commands;

use Illuminate\Console\Command;
use ArtflowStudio\AccountFlow\Facades\Accountflow;

class TestTransactionService extends Command
{
    protected $signature = 'accountflow:test-transactions';
    protected $description = 'Test TransactionService methods';

    public function handle()
    {
        $this->info('🧪 Testing TransactionService...');
        $this->newLine();

        try {
            $service = Accountflow::transactions();
            $this->info('✓ TransactionService loaded: ' . get_class($service));
            $this->newLine();

            // Test method existence
            $methods = [
                'create',
                'createIncome',
                'createExpense',
                'update',
                'delete',
                'reverse',
                'getById',
                'getAll',
                'getSummary',
            ];

            foreach ($methods as $method) {
                if (method_exists($service, $method)) {
                    $this->info("  ✓ Method exists: {$method}()");
                } else {
                    $this->error("  ✗ Method missing: {$method}()");
                }
            }

            $this->newLine();
            $this->info('✅ TransactionService test completed!');
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ TEST FAILED: ' . $e->getMessage());
            return 1;
        }
    }
}
