<?php

namespace ArtflowStudio\AccountFlow\App\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    protected $signature = 'accountflow:install
                            {--force : Overwrite existing published files}';

    protected $description = 'Install AccountFlow — publish config, views, models, Livewire components, migrations, and seeders';

    public function handle(): int
    {
        $this->newLine();
        $this->components->info('Installing AccountFlow...');
        $this->newLine();

        $force = (bool) $this->option('force');

        // 1. Config
        $this->components->task('Publishing configuration', function () use ($force) {
            $this->callSilently('vendor:publish', [
                '--tag'   => 'accountflow-config',
                '--force' => true,
            ]);
        });

        // 2. Views
        $this->components->task('Publishing views', function () use ($force) {
            $this->callSilently('vendor:publish', [
                '--tag'   => 'accountflow-views',
                '--force' => true,
            ]);
        });

        // 3. Models
        $this->components->task('Publishing models', function () use ($force) {
            $this->callSilently('vendor:publish', [
                '--tag'   => 'accountflow-models',
                '--force' => true,
            ]);
        });

        // 4. Livewire components
        $this->components->task('Publishing Livewire components', function () use ($force) {
            $this->callSilently('vendor:publish', [
                '--tag'   => 'accountflow-livewire',
                '--force' => true,
            ]);
        });

        // 5. Controllers
        $this->components->task('Publishing controllers', function () use ($force) {
            $this->callSilently('vendor:publish', [
                '--tag'   => 'accountflow-controllers',
                '--force' => true,
            ]);
        });

        // 6. Copy migrations
        $this->components->task('Copying migrations', function () use ($force) {
            $this->copyFiles(
                __DIR__ . '/../../database/migrations',
                database_path('migrations'),
                $force
            );
        });

        // 7. Copy seeders
        $this->components->task('Copying seeders', function () use ($force) {
            $this->copyFiles(
                __DIR__ . '/../../database/seeders',
                database_path('seeders'),
                $force
            );
        });

        $this->newLine();
        $this->components->info('AccountFlow installed successfully.');
        $this->newLine();
        $this->line('  <fg=yellow>Next steps:</>  ');
        $this->line('  <fg=cyan>1.</> Run <comment>php artisan accountflow:migrate</comment> to run the database migrations');
        $this->line('  <fg=cyan>2.</> Run <comment>php artisan accountflow:seed</comment> to seed default categories and settings');
        $this->line('  <fg=cyan>3.</> Edit <comment>config/accountflow.php</comment> to set your <comment>layout</comment> and <comment>middlewares</comment>');
        $this->line('  <fg=cyan>4.</> Visit <comment>/' . config('accountflow.route_prefix', 'accounts') . '</comment> to open AccountFlow');
        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * Copy all files from $sourceDir to $targetDir.
     * Skips existing files unless $force is true.
     *
     * @return array{copied: int, skipped: int}
     */
    private function copyFiles(string $sourceDir, string $targetDir, bool $force): array
    {
        if (! File::isDirectory($sourceDir)) {
            return ['copied' => 0, 'skipped' => 0];
        }

        if (! File::isDirectory($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        $copied  = 0;
        $skipped = 0;

        foreach (File::files($sourceDir) as $file) {
            $dest = $targetDir . DIRECTORY_SEPARATOR . $file->getFilename();

            if (File::exists($dest) && ! $force) {
                $skipped++;
                continue;
            }

            File::copy($file->getPathname(), $dest);
            $copied++;
        }

        return ['copied' => $copied, 'skipped' => $skipped];
    }
}