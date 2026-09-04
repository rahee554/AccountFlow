<?php

namespace ArtflowStudio\AccountFlow\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'accountflow:install
                            {--force : Overwrite files that have already been published}
                            {--migrate : Run the AccountFlow migrations}
                            {--seed : Seed default accounts, categories, payment methods and settings}';

    protected $description = 'Install AccountFlow — publish config and assets, then optionally migrate and seed';

    public function handle(): int
    {
        $force = (bool) $this->option('force');

        $this->newLine();
        $this->components->info('Installing AccountFlow...');
        $this->newLine();

        if (! $force && $this->configIsPublished()) {
            $this->components->warn(
                'config/accountflow.php already exists — leaving it untouched. Pass --force to overwrite.',
            );
        }

        $this->publish('accountflow-config', 'Publishing configuration', $force);
        $this->publish('accountflow-assets', 'Publishing assets', $force);

        if ($this->option('migrate')) {
            $this->components->task(
                'Running migrations',
                fn (): bool => $this->callSilently('migrate', ['--force' => true]) === self::SUCCESS,
            );
        }

        if ($this->option('seed')) {
            $this->components->task(
                'Seeding defaults',
                fn (): bool => $this->callSilently('accountflow:seed') === self::SUCCESS,
            );
        }

        $this->newLine();
        $this->components->info('AccountFlow installed.');
        $this->newLine();

        $this->line('  <fg=yellow>Next steps:</>');
        $step = 1;

        if (! $this->option('migrate')) {
            $this->line("  <fg=cyan>{$step}.</> Run <comment>php artisan migrate</comment> — AccountFlow's migrations are auto-discovered");
            $step++;
        }

        if (! $this->option('seed')) {
            $this->line("  <fg=cyan>{$step}.</> Run <comment>php artisan accountflow:seed</comment> to create default accounts and categories");
            $step++;
        }

        $this->line("  <fg=cyan>{$step}.</> Set <comment>layout</comment> and <comment>middlewares</comment> in <comment>config/accountflow.php</comment>");
        $step++;
        $this->line("  <fg=cyan>{$step}.</> Visit <comment>/".config('accountflow.route_prefix', 'accounts').'</comment>');
        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * Publish a tag, honouring --force instead of overwriting unconditionally.
     */
    private function publish(string $tag, string $label, bool $force): void
    {
        $this->components->task($label, function () use ($tag, $force): bool {
            $arguments = ['--tag' => $tag];

            if ($force) {
                $arguments['--force'] = true;
            }

            return $this->callSilently('vendor:publish', $arguments) === self::SUCCESS;
        });
    }

    private function configIsPublished(): bool
    {
        return file_exists(config_path('accountflow.php'));
    }
}
