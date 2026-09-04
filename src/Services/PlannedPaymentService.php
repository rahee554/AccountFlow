<?php

namespace ArtflowStudio\AccountFlow\Services;

use ArtflowStudio\AccountFlow\Enums\ScheduleType;
use ArtflowStudio\AccountFlow\Enums\TransactionType;
use ArtflowStudio\AccountFlow\Models\PlannedPayment;
use ArtflowStudio\AccountFlow\Models\Transaction;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Posting scheduled payments into the ledger.
 *
 * `ac_planned_payments` carries `auto_post`, `last_run_date` and
 * `next_run_date` — a schedule that nothing ever ran. You could create a
 * planned payment in 0.2.x and it would sit there forever: no command, job or
 * service ever turned one into a transaction.
 *
 * Run it from the scheduler:
 *
 *     Schedule::command('accountflow:post-planned-payments')->dailyAt('01:00');
 *
 * Posting is idempotent per due date: `last_run_date` advances only after a
 * transaction is committed, so a re-run on the same day posts nothing twice.
 */
class PlannedPaymentService
{
    public function __construct(
        private readonly TransactionService $transactions = new TransactionService,
    ) {}

    /**
     * Every auto-posting payment that is due on or before $asOf.
     *
     * @return Collection<int,PlannedPayment>
     */
    public function due(?CarbonInterface $asOf = null): Collection
    {
        $asOf ??= Carbon::now();

        return PlannedPayment::query()
            ->where('auto_post', true)
            ->where(function ($query) use ($asOf) {
                $query->whereNull('next_run_date')->orWhereDate('next_run_date', '<=', $asOf->toDateString());
            })
            ->where(function ($query) use ($asOf) {
                $query->whereNull('start_date')->orWhereDate('start_date', '<=', $asOf->toDateString());
            })
            ->where(function ($query) use ($asOf) {
                $query->whereNull('end_date')->orWhereDate('end_date', '>=', $asOf->toDateString());
            })
            ->get();
    }

    /**
     * Post everything due, advancing each schedule.
     *
     * @return array{posted: int, skipped: int, transactions: list<int>}
     */
    public function postDue(?CarbonInterface $asOf = null): array
    {
        $asOf ??= Carbon::now();
        $posted = 0;
        $skipped = 0;
        $ids = [];

        foreach ($this->due($asOf) as $payment) {
            $transaction = $this->post($payment, $asOf);

            if ($transaction === null) {
                $skipped++;

                continue;
            }

            $posted++;
            $ids[] = (int) $transaction->id;
        }

        return ['posted' => $posted, 'skipped' => $skipped, 'transactions' => $ids];
    }

    /**
     * Post one planned payment and move its schedule forward.
     *
     * Returns null when the payment is not due yet, is switched off, or has
     * already run for this due date.
     */
    public function post(PlannedPayment $payment, ?CarbonInterface $asOf = null): ?Transaction
    {
        $asOf ??= Carbon::now();
        $dueDate = Carbon::parse($payment->next_run_date ?? $payment->start_date ?? $asOf);

        // Posting advances the schedule, so a second call lands on the *next*
        // occurrence. Without this check it would post that one early.
        if ($dueDate->startOfDay()->greaterThan(Carbon::parse($asOf)->startOfDay())) {
            return null;
        }

        if (! $payment->auto_post) {
            return null;
        }

        if ($this->alreadyPosted($payment, $dueDate)) {
            return null;
        }

        return DB::transaction(function () use ($payment, $dueDate): Transaction {
            $transaction = $this->transactions->create([
                'type' => TransactionType::Expense,
                'amount' => (float) $payment->amount,
                'category_id' => $payment->category_id,
                'date' => $dueDate,
                'description' => $payment->description ?: $payment->name,
            ]);

            $next = $this->schedule($payment)->next(Carbon::parse($dueDate));

            $payment->forceFill([
                'last_run_date' => Carbon::parse($dueDate)->toDateString(),
                'next_run_date' => $next?->toDateString(),
                // A one-off payment stops posting once it has run.
                'auto_post' => $next !== null ? $payment->auto_post : false,
            ])->save();

            return $transaction;
        });
    }

    /**
     * The next date a payment is due, without posting anything.
     */
    public function nextDueDate(PlannedPayment $payment): ?CarbonInterface
    {
        $from = $payment->next_run_date ?? $payment->start_date;

        return $from === null ? null : Carbon::parse($from);
    }

    private function schedule(PlannedPayment $payment): ScheduleType
    {
        return ScheduleType::tryFrom((string) $payment->schedule_type) ?? ScheduleType::Once;
    }

    /**
     * Has this payment already produced a transaction for this due date?
     *
     * Guards against a double run on the same day.
     */
    private function alreadyPosted(PlannedPayment $payment, mixed $dueDate): bool
    {
        if ($payment->last_run_date === null) {
            return false;
        }

        return Carbon::parse($payment->last_run_date)->isSameDay(Carbon::parse($dueDate));
    }
}
