<?php

namespace ArtflowStudio\AccountFlow\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SeedAccountflowData extends Command
{
    protected $signature = 'accountflow:seed {--force : Force seeding even if data exists}';
    protected $description = 'Seed AccountFlow tables with initial data';

    public function handle()
    {
        $this->info('🌱 Seeding AccountFlow Data...');
        $this->newLine();

        // Check if tables exist
        if (!\Schema::hasTable('accounts')) {
            $this->error('❌ Accounts table not found!');
            $this->warn('Run migrations first: php artisan migrate');
            return 1;
        }

        // Check if data already exists
        $accountCount = \DB::table('accounts')->count();
        $categoryCount = \DB::table('ac_categories')->count();

        if ($accountCount > 0 || $categoryCount > 0) {
            if (!$this->option('force')) {
                $this->warn('⚠️  Data already exists!');
                $this->line("  Accounts: {$accountCount}");
                $this->line("  Categories: {$categoryCount}");
                $this->newLine();
                
                if (!$this->confirm('Do you want to re-seed? This will delete existing data!', false)) {
                    $this->info('Seeding cancelled.');
                    return 0;
                }
            }
        }

        try {
            $this->info('Running AccountsTableSeeder...');
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\AccountsTableSeeder'
            ]);

            $this->newLine();
            $this->info('✅ Seeding completed successfully!');
            $this->newLine();

            // Show summary
            $this->info('📊 SEEDED DATA SUMMARY');
            $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->line('  Accounts: ' . \DB::table('accounts')->count());
            $this->line('  Categories: ' . \DB::table('ac_categories')->count());
            $this->line('  Payment Methods: ' . \DB::table('ac_payment_methods')->count());
            $this->line('  Settings: ' . \DB::table('ac_settings')->count());
            
            if (config('accountflow.dummy_data_seed') === true) {
                $this->line('  Transactions (dummy): ' . \DB::table('ac_transactions')->count());
                $this->line('  Assets (dummy): ' . \DB::table('ac_assets')->count());
                $this->line('  Budgets (dummy): ' . \DB::table('ac_budgets')->count());
                $this->line('  Audit Trail (dummy): ' . \DB::table('ac_audit_trail')->count());
            }

            $this->newLine();
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Seeding failed!');
            $this->error($e->getMessage());
            return 1;
        }
    }
}
