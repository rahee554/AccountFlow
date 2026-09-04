<?php

namespace ArtflowStudio\AccountFlow\Console\Commands;

use ArtflowStudio\AccountFlow\Services\PlannedPaymentService;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Posts scheduled payments into the ledger.
 *
 * `ac_planned_payments` has carried `auto_post`, `last_run_date` and
 * `next_run_date` since 0.2.x, but nothing ever ran them — a planned payment
 * sat in the table forever and never became a transaction.
 *
 * Add to your scheduler:
 *
 *     Schedule::command('accountflow:post-planned-payments')->dailyAt('01:00');
 */
class PostPlannedPayments extends Command
{
    protected $signature = 'accountflow:post-planned-payments
                            {--date= : Post as if today were this date (Y-m-d)}
                            {--dry-run : List what would post without writing anything}';

    protected $description = 'Post any planned payments that are due';

    public function handle(PlannedPaymentService $payments): int
    {
        $asOf = $this->option('date') ? Carbon::parse((string) $this->option('date')) : Carbon::now();

        $due = $payments->due($asOf);

        if ($due->isEmpty()) {
            $this->components->info("Nothing due as of {$asOf->toDateString()}.");

            return self::SUCCESS;
        }

        $this->components->info(sprintf('%d planned payment(s) due as of %s.', $due->count(), $asOf->toDateString()));
        $this->newLine();

        $this->table(
            ['#', 'Name', 'Amount', 'Schedule', 'Due'],
            $due->map(fn ($payment): array => [
                $payment->id,
                $payment->name,
                number_format((float) $payment->amount, 2),
                $payment->schedule()->label(),
                $payment->next_run_date?->toDateString() ?? $payment->start_date?->toDateString() ?? '—',
            ])->all(),
        );

        if ($this->option('dry-run')) {
            $this->newLine();
            $this->components->warn('Dry run — nothing was posted.');

            return self::SUCCESS;
        }

        $result = $payments->postDue($asOf);

        $this->newLine();
        $this->components->info(sprintf(
            'Posted %d transaction(s); skipped %d already posted for their due date.',
            $result['posted'],
            $result['skipped'],
        ));

        return self::SUCCESS;
    }
}
