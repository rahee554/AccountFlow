<?php

namespace ArtflowStudio\AccountFlow\App\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class AccountFlowMigrateCommand extends Command
{
    protected $signature = 'accountflow:migrate';
    protected $description = 'Run specific migrations for the AccountFlow package';

    public function handle()
    {
        $migrations = [
            'database/migrations/9900_create_accounts_tables.php',
            'database/migrations/9901_add_columns_to_account.php',
        ];

        foreach ($migrations as $migration) {
            $this->info("Running migration: {$migration}");

            try {
                // Require the migration file and run the `up` method
                require_once base_path($migration);
                $migrationClass = $this->getMigrationClass($migration);
                (new $migrationClass)->up();

                $this->info("Migration {$migration} completed successfully.");
            } catch (\Exception $e) {
                $this->error("Migration {$migration} failed: " . $e->getMessage());
            }
        }
    }

    protected function getMigrationClass($migrationFile)
    {
        // Get the class name from the migration file name
        $className = ucfirst(str_replace('.php', '', basename($migrationFile)));
        // Convert file name to a class name, e.g., "9900_create_accounts_tables" => "CreateAccountsTables"
        return collect(explode('_', $className))
            ->map(function ($part) {
                return ucfirst($part);
            })
            ->implode('');
    }
}

