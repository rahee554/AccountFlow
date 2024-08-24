<?php

namespace ArtflowStudio\AccountFlow\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'accountflow:install';
    protected $description = 'Install the AccountFlow package';

    public function handle()
    {
        $this->info('Publishing configuration...');
        $this->call('vendor:publish', ['--tag' => 'accountflow-config']);

        $this->info('Publishing migrations...');
        $this->call('vendor:publish', ['--tag' => 'accountflow-migrations']);

        $this->info('Publishing views...');
        $this->call('vendor:publish', ['--tag' => 'accountflow-views']);

        $this->info('Publishing models...');
        $this->call('vendor:publish', ['--tag' => 'accountflow-models']);

        $this->info('Publishing controllers...');
        $this->call('vendor:publish', ['--tag' => 'accountflow-controllers']);

        $this->info('Publishing routes...');
        $this->call('vendor:publish', ['--tag' => 'accountflow-routes']);

        $this->info('Running migrations...');
        $this->call('migrate');

        $this->info('Seeding the database...');
        $this->call('db:seed', ['--class' => 'ArtflowStudio\\AccountFlow\\Database\\Seeders\\AccountFlowSeeder']);

        $this->info('AccountFlow installed successfully.');
    }
}
