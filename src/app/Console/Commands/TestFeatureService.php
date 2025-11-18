<?php

namespace ArtflowStudio\AccountFlow\App\Console\Commands;

use Illuminate\Console\Command;

class TestFeatureService extends Command
{
    protected $signature = 'accountflow:test-features';
    protected $description = 'Test FeatureService functionality';

    public function handle()
    {
        $this->info('🧪 Testing FeatureService...');
        $this->newLine();

        try {
            $service = \ArtflowStudio\AccountFlow\Facades\Accountflow::features();
            $this->info('✓ FeatureService loaded: ' . get_class($service));
            $this->newLine();

            // Test getting all features
            $this->info('📋 All Features Status:');
            $features = $service->getAllFeatures();
            
            foreach ($features as $key => $feature) {
                $status = $feature['enabled'] ? '✅ ENABLED' : '❌ DISABLED';
                $this->line("  {$status} - {$feature['name']}");
            }
            $this->newLine();

            // Test individual checks
            $this->info('🔍 Testing individual feature checks:');
            $this->line('  • isEnabled(\'audit\'): ' . ($service->isEnabled('audit') ? 'true' : 'false'));
            $this->line('  • isEnabled(\'budgets\'): ' . ($service->isEnabled('budgets') ? 'true' : 'false'));
            $this->line('  • isDisabled(\'audit\'): ' . ($service->isDisabled('audit') ? 'true' : 'false'));
            $this->newLine();

            $this->info('✅ FeatureService test completed!');
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ TEST FAILED: ' . $e->getMessage());
            $this->line($e->getTraceAsString());
            return 1;
        }
    }
}
