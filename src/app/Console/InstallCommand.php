<?php

namespace ArtflowStudio\AccountFlow\App\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'accountflow:install
                            {--force : Overwrite existing published files}
                            {--skip-migrate : Skip running database migrations}
                            {--skip-seed : Skip seeding default data}';

    protected $description = 'Install AccountFlow — publish all files, run migrations, and seed defaults';

    public function handle(): int
    {
        $this->newLine();
        $this->components->info('Installing AccountFlow...');
        $this->newLine();

        $forcePublish = ['--force' => true];

        // 1. Config
        $this->components->task('Publishing configuration', function () use ($forcePublish) {
            $this->callSilently('vendor:publish', array_merge(['--tag' => 'accountflow-config'], $forcePublish));
        });

        // 2. Views
        $this->components->task('Publishing views', function () use ($forcePublish) {
            $this->callSilently('vendor:publish', array_merge(['--tag' => 'accountflow-views'], $forcePublish));
        });

        // 3. Models
        $this->components->task('Publishing models', function () use ($forcePublish) {
            $this->callSilently('vendor:publish', array_merge(['--tag' => 'accountflow-models'], $forcePublish));
        });

        // 4. Livewire components
        $this->components->task('Publishing Livewire components', function () use ($forcePublish) {
            $this->callSilently('vendor:publish', array_merge(['--tag' => 'accountflow-livewire'], $forcePublish));
        });

        // 5. Controllers
        $this->components->task('Publishing controllers', function () use ($forcePublish) {
            $this->callSilently('vendor:publish', array_merge(['--tag' => 'accountflow-controllers'], $forcePublish));
        });

        // 6. Migrations
        if (! $this->option('skip-migrate')) {
            $this->components->task('Running migrations', function () {
                $this->callSilently('migrate', ['--force' => true]);
            });
        }

        // 7. Seed default data
        if (! $this->option('skip-seed')) {
            $this->components->task('Seeding default data', function () {
                $this->seedFromPackage();
            });
        }

        $this->newLine();
        $this->components->info('AccountFlow installed successfully.');
        $this->newLine();
        $prefix = config('accountflow.route_prefix', 'accounts');
        $this->line("  ▸ Edit <comment>config/accountflow.php</comment> to set your <comment>layout</comment> and <comment>middlewares</comment>");
        $this->line("  ▸ Visit <comment>/" . $prefix . "</comment> to access AccountFlow");
        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * Run the package seeder directly from source.
     * Does not require composer dump-autoload after publishing.
     */
    private function seedFromPackage(): void
    {
        $seederFile = realpath(__DIR__ . '/../../database/seeders/AccountsTableSeeder.php');

        if (! $seederFile || ! file_exists($seederFile)) {
            $this->warn('Seeder file not found — skipping.');

            return;
        }

        require_once $seederFile;

        $seeder = new \Database\Seeders\AccountsTableSeeder();
        $seeder->setContainer(app());
        $seeder->run();
    }
}

