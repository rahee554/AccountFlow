<?php

namespace ArtflowStudio\AccountFlow\Console;

use Illuminate\Console\Command;

class AccountFlowMigrateCommand extends Command
{
    protected $signature = 'accountflow:migrate
                            {--force : Run migrations without confirmation (use in production)}';

    protected $description = 'Run AccountFlow database migrations';

    public function handle(): int
    {
        $this->newLine();
        $this->components->info('Running AccountFlow migrations...');
        $this->newLine();

        $params = ['--force' => (bool) $this->option('force')];

        $exitCode = $this->call('migrate', $params);

        if ($exitCode === 0) {
            $this->newLine();
            $this->components->info('Migrations completed. Run <comment>php artisan accountflow:seed</comment> to seed defaults.');
            $this->newLine();
        }

        return $exitCode;
    }
}
