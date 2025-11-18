<?php

namespace ArtflowStudio\AccountFlow\App\Console\Commands;

use Illuminate\Console\Command;
use ArtflowStudio\AccountFlow\Facades\Accountflow;

class TestAllServices extends Command
{
    protected $signature = 'accountflow:test-all';
    protected $description = 'Run all AccountFlow service tests';

    public function handle()
    {
        $this->info('╔════════════════════════════════════════════════════════════════╗');
        $this->info('║        ACCOUNTFLOW PACKAGE - COMPLETE TEST SUITE              ║');
        $this->info('╚════════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $tests = [
            'Facade Resolution' => 'accountflow:test-facade',
            'Transaction Service' => 'accountflow:test-transactions',
            'Account Service' => 'accountflow:test-accounts',
            'Settings Service' => 'accountflow:test-settings',
            'Container Bindings' => 'accountflow:test-container',
        ];

        $passed = 0;
        $failed = 0;

        foreach ($tests as $name => $command) {
            $this->info("Running: {$name}");
            $exitCode = $this->call($command);
            
            if ($exitCode === 0) {
                $passed++;
                $this->info("  ✅ PASSED");
            } else {
                $failed++;
                $this->error("  ❌ FAILED");
            }
            $this->newLine();
        }

        $this->info('═══════════════════════════════════════════');
        $this->info("Results: {$passed} passed, {$failed} failed");
        $this->info('═══════════════════════════════════════════');

        if ($failed === 0) {
            $this->info('🎉 ALL TESTS PASSED! Package is working correctly.');
            return 0;
        } else {
            $this->error('⚠️  Some tests failed. Please check the errors above.');
            return 1;
        }
    }
}
