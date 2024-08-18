<?php

namespace ArtflowStudio\AccountFlow\App\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class AccountFlowSeedCommand extends Command
{
    protected $signature = 'accountflow:seed';
    protected $description = 'Run the AccountsTableSeeder for the AccountFlow package';

    public function handle()
    {
        $seederClass = 'AccountsTableSeeder';

        if (!class_exists($seederClass)) {
            $this->error("Seeder class {$seederClass} does not exist.");
            return;
        }

        $this->info("Running seeder: {$seederClass}");

        try {
            // Call the specific seeder class
            Artisan::call('db:seed', ['--class' => $seederClass, '--force' => true]);

            $this->info("Seeder {$seederClass} completed successfully.");
        } catch (\Exception $e) {
            $this->error("Seeder {$seederClass} failed: " . $e->getMessage());
        }
    }
}

