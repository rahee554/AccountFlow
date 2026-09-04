<?php

namespace ArtflowStudio\AccountFlow\Console;

use Exception;
use Illuminate\Console\Command;

class AccountFlowMigrateFreshCommand extends Command
{
    protected $signature = 'accountflow:migrate:fresh {--seed : Seed the database after fresh migration}';

    protected $description = 'Drop all tables and re-run all AccountFlow migrations, optionally with seeding';

    public function handle()
    {
        $this->info('🔄 Starting fresh AccountFlow migration...');
        $this->newLine();

        // Confirm with user
        if (! $this->confirm('This will drop all tables and recreate them. Do you want to continue?')) {
            $this->warn('Migration cancelled.');

            return 1;
        }

        try {
            // Drop all tables (fresh)
            $this->info('📦 Resetting database...');
            $this->call('migrate:fresh', [
                '--force' => true,
            ]);

            $this->newLine();
            $this->info('✅ Database reset successfully!');

            // Seed if requested
            if ($this->option('seed')) {
                $this->newLine();
                $this->info('🌱 Seeding AccountFlow data...');

                // Run the specific seeder
                $this->call('db:seed', [
                    '--class' => 'Database\Seeders\AccountsTableSeeder',
                    '--force' => true,
                ]);

                $this->newLine();
                $this->info('✅ Seeding completed successfully!');
            }

            $this->newLine();
            $this->info('🎉 AccountFlow migration fresh completed!');
            $this->line('You can now access the accounts dashboard at: /accounts');

            return 0;

        } catch (Exception $e) {
            $this->error('❌ Error during migration: '.$e->getMessage());

            return 1;
        }
    }
}
