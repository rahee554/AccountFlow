<?php

namespace ArtflowStudio\AccountFlow\Console\Commands;

use ArtflowStudio\AccountFlow\Models\Transfer;
use ArtflowStudio\AccountFlow\Services\TransferService;
use Illuminate\Console\Command;

/**
 * Posts ledger entries for transfers created before 0.3.0.
 *
 * A transfer used to write a row to `ac_transfers` and nothing else. Balances
 * come from `ac_transactions`, so the money never moved. This posts the missing
 * pair of entries for each unposted transfer and corrects both balances.
 *
 * Run it once, after migrating.
 */
class BackfillTransfers extends Command
{
    protected $signature = 'accountflow:backfill-transfers
                            {--dry-run : Report what would be posted without writing anything}';

    protected $description = 'Post ledger entries for transfers that never moved money';

    public function handle(TransferService $transfers): int
    {
        $unposted = Transfer::query()
            ->whereNull('from_trx_id')
            ->orWhereNull('to_trx_id')
            ->get();

        if ($unposted->isEmpty()) {
            $this->components->info('Every transfer is already posted to the ledger.');

            return self::SUCCESS;
        }

        $this->components->warn(sprintf(
            '%d transfer(s) have no ledger entries — their money never moved.',
            $unposted->count(),
        ));
        $this->newLine();

        $this->table(
            ['#', 'Ref', 'Amount', 'From', 'To', 'Date'],
            $unposted->map(fn (Transfer $transfer): array => [
                $transfer->id,
                $transfer->unique_id,
                number_format((float) $transfer->amount, 2),
                $transfer->from_account,
                $transfer->to_account,
                $transfer->date?->toDateString() ?? '—',
            ])->all(),
        );

        if ($this->option('dry-run')) {
            $this->newLine();
            $this->components->warn('Dry run — nothing was posted.');

            return self::SUCCESS;
        }

        $this->newLine();

        if (! $this->confirm('Post the missing entries and correct the affected balances?', true)) {
            $this->components->info('Aborted.');

            return self::SUCCESS;
        }

        $repaired = $transfers->backfillLedgerEntries();

        $this->components->info("Posted ledger entries for {$repaired} transfer(s).");

        return self::SUCCESS;
    }
}
