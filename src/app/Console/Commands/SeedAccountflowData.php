<?php

namespace ArtflowStudio\AccountFlow\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SeedAccountflowData extends Command
{
    protected $signature = 'accountflow:seed {--force : Skip confirmation when data already exists}';

    protected $description = 'Seed AccountFlow tables with default categories, settings, and payment methods';

    public function handle(): int
    {
        $this->newLine();
        $this->components->info('Seeding AccountFlow data...');
        $this->newLine();

        if (! Schema::hasTable('ac_accounts')) {
            $this->components->error('AccountFlow tables not found. Run migrations first: php artisan accountflow:migrate');

            return self::FAILURE;
        }

        $categoryCount = DB::table('ac_categories')->count();

        if ($categoryCount > 0 && ! $this->option('force')) {
            $this->components->warn('AccountFlow data already exists (' . $categoryCount . ' categories found).');
            $this->newLine();

            if (! $this->confirm('Re-seed? This will delete and re-create default categories, settings, and payment methods.', false)) {
                $this->line('  Seeding cancelled.');
                $this->newLine();

                return self::SUCCESS;
            }
        }

        try {
            $seederFile = realpath(__DIR__ . '/../../../database/seeders/AccountsTableSeeder.php');

            if (! $seederFile || ! file_exists($seederFile)) {
                $this->components->error('Seeder file not found in package source.');

                return self::FAILURE;
            }

            require_once $seederFile;

            $seeder = new \Database\Seeders\AccountsTableSeeder();
            $seeder->setContainer(app());
            $seeder->run();

            $this->newLine();
            $this->components->info('Seeding completed.');
            $this->newLine();
            $this->line('  Categories:       ' . DB::table('ac_categories')->count());
            $this->line('  Payment methods:  ' . DB::table('ac_payment_methods')->count());
            $this->line('  Settings:         ' . DB::table('ac_settings')->count());
            $this->newLine();

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->components->error('Seeding failed: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}

