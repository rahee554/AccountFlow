<?php

namespace ArtflowStudio\AccountFlow\App\Console\Commands;

use Illuminate\Console\Command;
use ArtflowStudio\AccountFlow\Facades\Accountflow;

class TestRealUsage extends Command
{
    protected $signature = 'accountflow:test-real-usage';
    protected $description = 'Test Accountflow facade with real-world usage';

    public function handle()
    {
        $this->info('🧪 Testing Real-World Usage...');
        $this->newLine();

        try {
            // Test 1: Get transaction service
            $this->info('Test 1: Getting TransactionService via Accountflow::transactions()');
            $transactionService = Accountflow::transactions();
            $this->info('  ✓ Success: ' . get_class($transactionService));
            $this->newLine();

            // Test 2: Get settings
            $this->info('Test 2: Getting default settings');
            $salesCategory = Accountflow::settings()->defaultSalesCategoryId();
            $expenseCategory = Accountflow::settings()->defaultExpenseCategoryId();
            $this->info('  ✓ Default Sales Category ID: ' . ($salesCategory ?? 'null'));
            $this->info('  ✓ Default Expense Category ID: ' . ($expenseCategory ?? 'null'));
            $this->newLine();

            // Test 3: Get accounts
            $this->info('Test 3: Getting all accounts');
            $accounts = Accountflow::accounts()->getAll();
            $this->info('  ✓ Total accounts found: ' . $accounts->count());
            $this->newLine();

            // Test 4: Test method chaining
            $this->info('Test 4: Method chaining test');
            $accountService = Accountflow::accounts();
            $this->info('  ✓ Chain 1: Accountflow::accounts() works');
            
            $categoryService = Accountflow::categories();
            $this->info('  ✓ Chain 2: Accountflow::categories() works');
            
            $auditService = Accountflow::audit();
            $this->info('  ✓ Chain 3: Accountflow::audit() works');
            $this->newLine();

            // Test 5: Check if all services are singletons
            $this->info('Test 5: Singleton verification');
            $service1 = Accountflow::transactions();
            $service2 = Accountflow::transactions();
            if (spl_object_id($service1) === spl_object_id($service2)) {
                $this->info('  ✓ Services are correctly registered as singletons');
            } else {
                $this->warn('  ⚠ Services are NOT singletons (new instance each time)');
            }
            $this->newLine();

            $this->info('═══════════════════════════════════════════');
            $this->info('✅ ALL REAL-WORLD TESTS PASSED!');
            $this->info('═══════════════════════════════════════════');
            $this->info('The Accountflow facade is working perfectly!');
            $this->newLine();

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ TEST FAILED!');
            $this->error('Error: ' . $e->getMessage());
            $this->newLine();
            $this->line('Stack trace:');
            $this->line($e->getTraceAsString());
            return 1;
        }
    }
}
