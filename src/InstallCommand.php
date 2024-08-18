<?php

namespace ArtflowStudio\AccountFlow\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'accountflow:install';
    protected $description = 'Install the AccountFlow package';

    public function handle()
    {
        $this->info('Publishing migrations...');
        $this->call('vendor:publish', ['--tag' => 'migrations']);

        $this->info('Running migrations...');
        $this->call('migrate');

        $this->info('Seeding the database...');
        $this->call('db:seed', ['--class' => 'ArtflowStudio\\AccountFlow\\Database\\Seeders\\AccountFlowSeeder']);

        $this->info('AccountFlow installed successfully.');
    }
}
