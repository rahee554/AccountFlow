<?php

namespace ArtflowStudio\AccountFlow\App\Console\Commands;

use Illuminate\Console\Command;

class RunAllTests extends Command
{
    protected $signature = 'accountflow:test-complete';
    protected $description = 'Run complete AccountFlow test suite including real-world commands';

    public function handle()
    {
        $this->info('╔════════════════════════════════════════════════════════════════╗');
        $this->info('║     ACCOUNTFLOW COMPLETE TEST SUITE - ALL COMMANDS            ║');
        $this->info('╚════════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $tests = [
            '1. Status Check' => 'accountflow:status',
            '2. Facade Resolution' => 'accountflow:test-facade',
            '3. Transaction Service' => 'accountflow:test-transactions',
            '4. Account Service' => 'accountflow:test-accounts',
            '5. Settings Service' => 'accountflow:test-settings',
            '6. Feature Service' => 'accountflow:test-features',
            '7. Container Bindings' => 'accountflow:test-container',
            '8. Real Usage' => 'accountflow:test-real-usage',
            '9. Livewire Analysis' => 'accountflow:analyze-livewire',
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
            $this->info('🎉 ALL TESTS PASSED! Package is fully functional.');
            $this->newLine();
            $this->info('Available Commands Summary:');
            $this->line('  • php artisan accountflow:status - Check system status');
            $this->line('  • php artisan accountflow:seed - Seed data');
            $this->line('  • php artisan accountflow:feature {name} {enable|disable} - Toggle features');
            $this->line('  • php artisan accountflow:analyze-livewire - Analyze components');
            $this->newLine();
            return 0;
        } else {
            $this->error('⚠️  Some tests failed. Please check the errors above.');
            return 1;
        }
    }
}
