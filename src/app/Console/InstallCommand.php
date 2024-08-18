<?php

namespace ArtflowStudio\AccountFlow\App\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'accountflow:install';
    protected $description = 'Install the AccountFlow package';

    public function handle()
    {
        $this->info('Publishing configuration...');
        $this->call('vendor:publish', ['--tag' => 'accountflow-config', '--force' => true]);

        $this->info('Publishing migrations...');
        $this->call('vendor:publish', ['--tag' => 'accountflow-migrations', '--force' => true]);

        $this->info('Publishing views...');
        $this->call('vendor:publish', ['--tag' => 'accountflow-views', '--force' => true]);

        $this->info('Publishing models...');
        $this->call('vendor:publish', ['--tag' => 'accountflow-models', '--force' => true]);

        $this->info('Publishing controllers...');
        $this->call('vendor:publish', ['--tag' => 'accountflow-controllers', '--force' => true]);

        $this->info('Publishing routes...');
        $this->call('vendor:publish', ['--tag' => 'accountflow-routes', '--force' => true]);

        $this->info('Publishing assets...');
        $this->call('vendor:publish', ['--tag' => 'accountflow-assets', '--force' => true]);

        $this->info('Running migrations...');
        $this->call('migrate:fresh', ['--path' => 'database/migrations/AccountFlow', '--force' => false]);
        
        $this->info('Seeding the database...');
        $this->call('db:seed', ['--class' => 'Database\Seeders\AccountsTableSeeder', '--force' => false]);
        
        $this->info('AccountFlow installed successfully.');
    }
}

