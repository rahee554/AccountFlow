<?php

namespace ArtflowStudio\AccountFlow\App\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'accountflow:install';
    protected $description = 'Install the AccountFlow package';

    public function handle(): int
    {
        $this->info('Publishing configuration...');
        $this->call('vendor:publish', ['--tag' => 'accountflow-config', '--force' => true]);

        $this->info('Running migrations...');
        $this->call('migrate', ['--force' => false]);

        $this->info('Seeding the database...');
        $this->call('db:seed', ['--class' => 'Database\Seeders\AccountsTableSeeder', '--force' => false]);

        $this->info('AccountFlow installed successfully.');
        $this->line('💡 Run <comment>php artisan accountflow:link</comment> to link package files into your app directories.');

        return self::SUCCESS;
    }
}

