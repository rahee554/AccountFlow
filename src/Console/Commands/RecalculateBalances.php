<?php

namespace ArtflowStudio\AccountFlow\Console\Commands;

use ArtflowStudio\AccountFlow\Models\Account;
use ArtflowStudio\AccountFlow\Services\AccountService;
use Illuminate\Console\Command;

/**
 * Rebuilds every stored account balance from the ledger.
 *
 * `accounts.balance` is a running total kept up to date as transactions are
 * written. If anything ever wrote a transaction without going through
 * TransactionService — the asset screen used to — the stored figure drifts
 * away from what the transactions actually say. This puts it back.
 *
 * Safe to run at any time: the ledger is the source of truth, and the result
 * is the same however many times you run it.
 */
class RecalculateBalances extends Command
{
    protected $signature = 'accountflow:recalculate-balances
                            {--dry-run : Show what would change without writing}
                            {--force : Skip the confirmation prompt}';

    protected $description = 'Rebuild stored account balances from the transaction ledger';

    public function handle(AccountService $accounts): int
    {
        $changes = [];

        foreach (Account::all() as $account) {
            $stored = round((float) $account->balance, 2);
            $ledger = round($account->calculatedBalance(), 2);

            if (abs($stored - $ledger) >= 0.01) {
                $changes[] = [
                    'id' => (int) $account->id,
                    'name' => (string) $account->name,
                    'from' => $stored,
                    'to' => $ledger,
                ];
            }
        }

        if ($changes === []) {
            $this->components->info('Every balance already matches the ledger. Nothing to do.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->components->warn(sprintf('%d account balance(s) disagree with the ledger.', count($changes)));
        $this->newLine();

        $this->table(
            ['#', 'Account', 'Stored', 'Ledger says', 'Change'],
            array_map(fn (array $c): array => [
                $c['id'],
                $c['name'],
                number_format($c['from'], 2),
                number_format($c['to'], 2),
                sprintf('%+.2f', $c['to'] - $c['from']),
            ], $changes),
        );

        if ($this->option('dry-run')) {
            $this->newLine();
            $this->components->warn('Dry run — nothing was written.');

            return self::SUCCESS;
        }

        $this->newLine();

        if (! $this->option('force') && ! $this->confirm('Rebuild these balances from the ledger?', true)) {
            $this->components->info('Aborted.');

            return self::SUCCESS;
        }

        $accounts->recalculateAll();

        $this->components->info(sprintf('Rebuilt %d balance(s).', count($changes)));

        return self::SUCCESS;
    }
}
