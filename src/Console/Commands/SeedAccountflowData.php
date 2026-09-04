<?php

namespace ArtflowStudio\AccountFlow\Console\Commands;

use ArtflowStudio\AccountFlow\Database\Seeders\AccountsTableSeeder;
use ArtflowStudio\AccountFlow\Models\Account;
use ArtflowStudio\AccountFlow\Models\Category;
use ArtflowStudio\AccountFlow\Models\PaymentMethod;
use ArtflowStudio\AccountFlow\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Seeds the default chart of accounts, categories, payment methods and settings.
 *
 * This command never actually ran: it guarded on `Schema::hasTable('ac_accounts')`,
 * but the table is called `accounts`, so it always reported "tables not found".
 * A second command also claimed `accountflow:seed` and silently shadowed this
 * one; it failed too, checking `class_exists('AccountsTableSeeder')` without a
 * namespace. Both are fixed — there is one seed command now, and it works.
 *
 * The seeder itself is idempotent: it matches on natural keys, never deletes,
 * and leaves settings you have already changed alone. Running it twice changes
 * nothing, so the old "this will delete and re-create" warning is gone.
 */
class SeedAccountflowData extends Command
{
    protected $signature = 'accountflow:seed
                            {--force : Do not ask before re-seeding}
                            {--overwrite-settings : Also reset settings you have changed}';

    protected $description = 'Seed default accounts, categories, payment methods and settings';

    public function handle(): int
    {
        $this->newLine();

        if (! Schema::hasTable('accounts') || ! Schema::hasTable('ac_categories')) {
            $this->components->error('AccountFlow tables are missing. Run: php artisan migrate');

            return self::FAILURE;
        }

        $existing = Category::count();

        if ($existing > 0 && ! $this->option('force')) {
            $this->components->info("AccountFlow already has {$existing} categories.");
            $this->line('  Seeding is safe to repeat: nothing is deleted, and only missing rows are added.');
            $this->newLine();

            if (! $this->confirm('Add any missing defaults?', true)) {
                $this->components->info('Nothing to do.');

                return self::SUCCESS;
            }
        }

        $before = $this->counts();

        try {
            $seeder = new AccountsTableSeeder;
            $seeder->overwriteSettings = (bool) $this->option('overwrite-settings');
            $seeder->setContainer($this->laravel);
            $seeder->run();
        } catch (Throwable $e) {
            $this->components->error('Seeding failed: '.$e->getMessage());

            return self::FAILURE;
        }

        $after = $this->counts();

        $this->newLine();
        $this->table(
            ['', 'Before', 'After', 'Added'],
            array_map(
                fn (string $label): array => [
                    $label, $before[$label], $after[$label], $after[$label] - $before[$label],
                ],
                array_keys($before),
            ),
        );

        $this->newLine();
        $this->components->info('Seeding complete.');
        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * @return array<string,int>
     */
    private function counts(): array
    {
        return [
            'Accounts' => Account::count(),
            'Categories' => Category::count(),
            'Payment methods' => PaymentMethod::count(),
            'Settings' => Setting::count(),
        ];
    }
}
