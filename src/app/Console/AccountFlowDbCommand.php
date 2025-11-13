<?php

namespace ArtflowStudio\AccountFlow\App\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class AccountFlowDbCommand extends Command
{
    protected $signature = 'accountflow:db {--force : Skip confirmation prompts} {--fresh : Run migrate:fresh instead of migrate}';
    protected $description = 'Interactive AccountFlow database setup: migrate & seed with status checks (project migrations first, then package)';

    public function handle()
    {
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('  🗂️  AccountFlow Database Setup Command');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->newLine();

        $fresh = $this->option('fresh');
        $force = $this->option('force');

        // ============================================
        // Step 1: Check for migrations
        // ============================================
        $this->line('📋 Step 1: Checking for AccountFlow migrations...');
        $projectMigrationsPath = base_path('database/migrations');
        $packageMigrationsPath = dirname(dirname(dirname(__FILE__))) . '/../database/migrations';

        $projectAccountMigrations = $this->findAccountMigrations($projectMigrationsPath);
        $packageAccountMigrations = $this->findAccountMigrations($packageMigrationsPath);

        $this->line('  ✓ Project migrations folder: ' . $projectMigrationsPath);
        if (!empty($projectAccountMigrations)) {
            $this->info('    ✓ Found ' . count($projectAccountMigrations) . ' AccountFlow migration(s) in project');
            foreach ($projectAccountMigrations as $migration) {
                $this->line('      - ' . basename($migration));
            }
        } else {
            $this->warn('    ⚠ No AccountFlow migrations found in project');
        }

        $this->line('  ✓ Package migrations folder: ' . $packageMigrationsPath);
        if (!empty($packageAccountMigrations)) {
            $this->info('    ✓ Found ' . count($packageAccountMigrations) . ' AccountFlow migration(s) in package');
            foreach ($packageAccountMigrations as $migration) {
                $this->line('      - ' . basename($migration));
            }
        } else {
            $this->warn('    ⚠ No AccountFlow migrations found in package');
        }
        $this->newLine();

        // ============================================
        // Step 2: Check for seeders
        // ============================================
        $this->line('📋 Step 2: Checking for AccountFlow seeders...');
        $projectSeedersPath = base_path('database/seeders');
        $packageSeedersPath = dirname(dirname(dirname(__FILE__))) . '/../database/seeders';

        $projectAccountSeeders = $this->findAccountSeeders($projectSeedersPath);
        $packageAccountSeeders = $this->findAccountSeeders($packageSeedersPath);

        $this->line('  ✓ Project seeders folder: ' . $projectSeedersPath);
        if (!empty($projectAccountSeeders)) {
            $this->info('    ✓ Found ' . count($projectAccountSeeders) . ' AccountFlow seeder(s) in project');
            foreach ($projectAccountSeeders as $seeder) {
                $this->line('      - ' . basename($seeder));
            }
        } else {
            $this->warn('    ⚠ No AccountFlow seeders found in project');
        }

        $this->line('  ✓ Package seeders folder: ' . $packageSeedersPath);
        if (!empty($packageAccountSeeders)) {
            $this->info('    ✓ Found ' . count($packageAccountSeeders) . ' AccountFlow seeder(s) in package');
            foreach ($packageAccountSeeders as $seeder) {
                $this->line('      - ' . basename($seeder));
            }
        } else {
            $this->warn('    ⚠ No AccountFlow seeders found in package');
        }
        $this->newLine();

        // ============================================
        // Step 3: Determine migration & seed sources
        // ============================================
        $this->line('📋 Step 3: Determining migration sources (Priority: Project > Package)...');
        $usedMigrationsSource = !empty($projectAccountMigrations) ? 'project' : 'package';
        $usedSeedersSource = !empty($projectAccountSeeders) ? 'project' : 'package';

        if ($usedMigrationsSource === 'project') {
            $this->info('  ✓ Will use migrations from PROJECT');
        } else {
            $this->warn('  ⚠ Will use migrations from PACKAGE (no project migrations found)');
        }

        if ($usedSeedersSource === 'project') {
            $this->info('  ✓ Will use seeders from PROJECT');
        } else {
            $this->warn('  ⚠ Will use seeders from PACKAGE (no project seeders found)');
        }
        $this->newLine();

        // ============================================
        // Step 4: Confirm & Execute
        // ============================================
        if (!$force) {
            $this->warn('⚠️  This will:');
            if ($fresh) {
                $this->line('  - Run: php artisan migrate:fresh');
            } else {
                $this->line('  - Run: php artisan migrate');
            }
            $this->line('  - Run: php artisan db:seed');
            $this->line('  - Seed AccountFlow data');
            $this->newLine();

            if (!$this->confirm('Do you want to continue?')) {
                $this->info('❌ Command cancelled.');
                return 0;
            }
        }

        // ============================================
        // Step 5: Run migrate:fresh or migrate
        // ============================================
        $this->line('🚀 Step 4: Running migrations...');
        if ($fresh) {
            $this->line('  → Running: php artisan migrate:fresh --force');
            Artisan::call('migrate:fresh', ['--force' => true]);
        } else {
            $this->line('  → Running: php artisan migrate');
            Artisan::call('migrate');
        }
        $this->info('  ✓ Migrations completed');
        $this->newLine();

        // ============================================
        // Step 6: Run seeders
        // ============================================
        $this->line('🌱 Step 5: Running seeders...');
        $this->line('  → Running: php artisan db:seed');
        Artisan::call('db:seed');
        $this->info('  ✓ Seeding completed');
        $this->newLine();

        // ============================================
        // Final Summary
        // ============================================
        $this->info('═══════════════════════════════════════════════════════════');
        $this->info('  ✅ AccountFlow database setup completed successfully!');
        $this->info('═══════════════════════════════════════════════════════════');
        $this->newLine();
        $this->line('📊 Summary:');
        $this->line("  - Migrations Source: {$usedMigrationsSource}");
        $this->line("  - Seeders Source: {$usedSeedersSource}");
        $this->line('  - Migrations: ' . (count($projectAccountMigrations) + count($packageAccountMigrations)) . ' total');
        $this->line('  - Seeders: ' . (count($projectAccountSeeders) + count($packageAccountSeeders)) . ' total');
        $this->newLine();
        $this->line('💡 Next steps:');
        $this->line('  - Check database: verify tables created in your database');
        $this->line('  - Run tests: php artisan test');
        $this->newLine();

        return 0;
    }

    /**
     * Find all AccountFlow-related migration files in a directory
     * Looks for files containing "account" (case-insensitive) in the filename
     *
     * @param string $path
     * @return array
     */
    private function findAccountMigrations($path)
    {
        if (!File::exists($path)) {
            return [];
        }

        $files = File::files($path);
        $accountMigrations = [];

        foreach ($files as $file) {
            $filename = strtolower($file->getFilename());
            if (strpos($filename, 'account') !== false && $file->getExtension() === 'php') {
                $accountMigrations[] = $file->getPathname();
            }
        }

        return $accountMigrations;
    }

    /**
     * Find all AccountFlow-related seeder files in a directory
     * Looks for files containing "account" (case-insensitive) in the filename
     *
     * @param string $path
     * @return array
     */
    private function findAccountSeeders($path)
    {
        if (!File::exists($path)) {
            return [];
        }

        $files = File::files($path);
        $accountSeeders = [];

        foreach ($files as $file) {
            $filename = strtolower($file->getFilename());
            if (strpos($filename, 'account') !== false && $file->getExtension() === 'php') {
                $accountSeeders[] = $file->getPathname();
            }
        }

        return $accountSeeders;
    }
}
