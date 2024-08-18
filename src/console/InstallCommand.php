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
        $this->call('vendor:publish', ['--tag' => 'config']);

        $this->info('Publishing views...');
        $this->call('vendor:publish', ['--tag' => 'views']);

        $this->info('Publishing migrations...');
        $this->call('vendor:publish', ['--tag' => 'migrations']);

        $this->info('Running migrations...');
        $this->call('migrate');

        $this->info('AccountFlow installed successfully.');
    }
}
