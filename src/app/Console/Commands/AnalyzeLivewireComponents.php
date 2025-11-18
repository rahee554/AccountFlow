<?php

namespace ArtflowStudio\AccountFlow\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AnalyzeLivewireComponents extends Command
{
    protected $signature = 'accountflow:analyze-livewire';
    protected $description = 'Analyze Livewire components for AccountFlow usage and issues';

    public function handle()
    {
        $this->info('🔍 Analyzing Livewire Components...');
        $this->newLine();

        $componentsPath = app_path('Livewire/AccountFlow');
        
        if (!File::exists($componentsPath)) {
            $this->error('AccountFlow Livewire directory not found!');
            return 1;
        }

        $analysis = [
            'using_facade' => [],
            'using_old_controllers' => [],
            'missing_imports' => [],
            'feature_dependencies' => [],
        ];

        $this->analyzeDirectory($componentsPath, $analysis);

        // Display results
        $this->info('📊 ANALYSIS RESULTS');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();

        $this->info('✅ Components using Accountflow facade: ' . count($analysis['using_facade']));
        foreach ($analysis['using_facade'] as $component) {
            $this->line("  ✓ {$component}");
        }
        $this->newLine();

        if (count($analysis['using_old_controllers']) > 0) {
            $this->warn('⚠️  Components still using old controllers: ' . count($analysis['using_old_controllers']));
            foreach ($analysis['using_old_controllers'] as $component => $controllers) {
                $this->line("  • {$component}");
                foreach ($controllers as $controller) {
                    $this->line("    - {$controller}");
                }
            }
            $this->newLine();
        }

        if (count($analysis['feature_dependencies']) > 0) {
            $this->info('🔧 Components by feature module:');
            foreach ($analysis['feature_dependencies'] as $feature => $components) {
                $this->line("  📁 {$feature}: " . count($components) . " components");
            }
            $this->newLine();
        }

        $this->info('✅ Analysis complete!');
        return 0;
    }

    private function analyzeDirectory($path, &$analysis, $prefix = '')
    {
        $items = File::allFiles($path);

        foreach ($items as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $content = File::get($file->getPathname());
            $relativePath = str_replace(app_path('Livewire/AccountFlow/'), '', $file->getPathname());
            $componentName = str_replace(['/', '\\', '.php'], ['.', '.', ''], $relativePath);

            // Check for Accountflow facade usage
            if (str_contains($content, 'use ArtflowStudio\AccountFlow\Facades\Accountflow')) {
                $analysis['using_facade'][] = $componentName;
            }

            // Check for old controller usage
            $oldControllers = [];
            if (str_contains($content, 'DefaultController::')) {
                $oldControllers[] = 'DefaultController';
            }
            if (str_contains($content, 'AccountsController::')) {
                $oldControllers[] = 'AccountsController';
            }
            if (!empty($oldControllers)) {
                $analysis['using_old_controllers'][$componentName] = $oldControllers;
            }

            // Detect feature dependencies
            $feature = $this->detectFeature($file->getPath());
            if ($feature) {
                if (!isset($analysis['feature_dependencies'][$feature])) {
                    $analysis['feature_dependencies'][$feature] = [];
                }
                $analysis['feature_dependencies'][$feature][] = $componentName;
            }
        }
    }

    private function detectFeature($path)
    {
        $features = [
            'Budgets' => 'budgets_module',
            'AuditTrail' => 'audit_trail',
            'Assets' => 'assets_module',
            'Loans' => 'loan_module',
            'Wallets' => 'user_wallet_module',
            'Equity' => 'equity_module',
            'PlannedPayments' => 'planned_payments_module',
            'Reports' => 'trial_balance_module',
        ];

        foreach ($features as $dir => $feature) {
            if (str_contains($path, $dir)) {
                return $feature;
            }
        }

        return null;
    }
}
