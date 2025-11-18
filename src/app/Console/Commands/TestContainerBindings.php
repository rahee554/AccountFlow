<?php

namespace ArtflowStudio\AccountFlow\App\Console\Commands;

use Illuminate\Console\Command;

class TestContainerBindings extends Command
{
    protected $signature = 'accountflow:test-container';
    protected $description = 'Test Laravel container bindings for AccountFlow';

    public function handle()
    {
        $this->info('🧪 Testing Container Bindings...');
        $this->newLine();

        try {
            // Test 1: Main 'accountflow' binding
            $this->info('Test 1: Main accountflow binding');
            $manager = app('accountflow');
            $this->info('  ✓ Resolved: ' . get_class($manager));
            $this->newLine();

            // Test 2: Service bindings
            $services = [
                'TransactionService' => \ArtflowStudio\AccountFlow\App\Services\TransactionService::class,
                'AccountService' => \ArtflowStudio\AccountFlow\App\Services\AccountService::class,
                'CategoryService' => \ArtflowStudio\AccountFlow\App\Services\CategoryService::class,
                'PaymentMethodService' => \ArtflowStudio\AccountFlow\App\Services\PaymentMethodService::class,
                'BudgetService' => \ArtflowStudio\AccountFlow\App\Services\BudgetService::class,
                'ReportService' => \ArtflowStudio\AccountFlow\App\Services\ReportService::class,
                'SettingsService' => \ArtflowStudio\AccountFlow\App\Services\SettingsService::class,
                'AuditService' => \ArtflowStudio\AccountFlow\App\Services\AuditService::class,
            ];

            $this->info('Test 2: Service class bindings');
            foreach ($services as $name => $class) {
                $service = app()->make($class);
                $this->info("  ✓ {$name}: " . get_class($service));
            }
            $this->newLine();

            // Test 3: Service provider loaded
            $this->info('Test 3: Service provider check');
            $providers = app()->getLoadedProviders();
            if (isset($providers['ArtflowStudio\\AccountFlow\\AccountFlowServiceProvider'])) {
                $this->info('  ✓ AccountFlowServiceProvider is loaded');
            } else {
                $this->warn('  ⚠ AccountFlowServiceProvider not found in loaded providers');
            }
            $this->newLine();

            $this->info('✅ All container bindings verified!');
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ TEST FAILED: ' . $e->getMessage());
            $this->newLine();
            $this->line($e->getTraceAsString());
            return 1;
        }
    }
}
