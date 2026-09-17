<?php

namespace ArtflowStudio\AccountFlow\Console;

use ArtflowStudio\AccountFlow\Models\Category;
use ArtflowStudio\AccountFlow\Models\PaymentMethod;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

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

        $seeded = false;

        if ($this->option('seed')) {
            $this->components->task(
                'Seeding defaults',
                fn (): bool => $this->callSilently('accountflow:seed') === self::SUCCESS,
            );
            $seeded = true;
        } elseif ($this->missingCategoriesOrPaymentMethods()) {
            $this->newLine();
            if ($this->confirm('No categories or payment methods found yet — without them, transactions cannot be created. Seed AccountFlow defaults now?', true)) {
                $this->components->task(
                    'Seeding defaults',
                    fn (): bool => $this->callSilently('accountflow:seed', ['--force' => true]) === self::SUCCESS,
                );
                $seeded = true;
            }
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

        if (! $seeded) {
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

    private function missingCategoriesOrPaymentMethods(): bool
    {
        if (! Schema::hasTable('accounts') || ! Schema::hasTable('ac_categories') || ! Schema::hasTable('ac_payment_methods')) {
            return false;
        }

        return Category::count() === 0 || PaymentMethod::count() === 0;
    }
}
