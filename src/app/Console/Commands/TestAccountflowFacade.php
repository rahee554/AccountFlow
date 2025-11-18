<?php

namespace ArtflowStudio\AccountFlow\App\Console\Commands;

use Illuminate\Console\Command;
use ArtflowStudio\AccountFlow\Facades\Accountflow;

class TestAccountflowFacade extends Command
{
    protected $signature = 'accountflow:test-facade';
    protected $description = 'Test if Accountflow facade is working correctly';

    public function handle()
    {
        $this->info('🧪 Testing Accountflow Facade...');
        $this->newLine();

        try {
            // Test 1: Facade class exists
            $this->info('✓ Test 1: Facade class exists');
            $this->line('  Class: ' . Accountflow::class);
            $this->newLine();

            // Test 2: Container binding exists
            $this->info('✓ Test 2: Testing container binding...');
            $manager = app('accountflow');
            $this->line('  Manager: ' . get_class($manager));
            $this->newLine();

            // Test 3: Transactions service
            $this->info('✓ Test 3: Testing transactions() method...');
            $transactionService = Accountflow::transactions();
            $this->line('  Service: ' . get_class($transactionService));
            $this->newLine();

            // Test 4: Accounts service
            $this->info('✓ Test 4: Testing accounts() method...');
            $accountService = Accountflow::accounts();
            $this->line('  Service: ' . get_class($accountService));
            $this->newLine();

            // Test 5: Categories service
            $this->info('✓ Test 5: Testing categories() method...');
            $categoryService = Accountflow::categories();
            $this->line('  Service: ' . get_class($categoryService));
            $this->newLine();

            // Test 6: Payment Methods service
            $this->info('✓ Test 6: Testing paymentMethods() method...');
            $paymentMethodService = Accountflow::paymentMethods();
            $this->line('  Service: ' . get_class($paymentMethodService));
            $this->newLine();

            // Test 7: Budgets service
            $this->info('✓ Test 7: Testing budgets() method...');
            $budgetService = Accountflow::budgets();
            $this->line('  Service: ' . get_class($budgetService));
            $this->newLine();

            // Test 8: Reports service
            $this->info('✓ Test 8: Testing reports() method...');
            $reportService = Accountflow::reports();
            $this->line('  Service: ' . get_class($reportService));
            $this->newLine();

            // Test 9: Settings service
            $this->info('✓ Test 9: Testing settings() method...');
            $settingsService = Accountflow::settings();
            $this->line('  Service: ' . get_class($settingsService));
            $this->newLine();

            // Test 10: Audit service
            $this->info('✓ Test 10: Testing audit() method...');
            $auditService = Accountflow::audit();
            $this->line('  Service: ' . get_class($auditService));
            $this->newLine();

            $this->info('═══════════════════════════════════════════');
            $this->info('✅ ALL TESTS PASSED!');
            $this->info('═══════════════════════════════════════════');
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
