<?php

namespace ArtflowStudio\AccountFlow\Services;

use ArtflowStudio\AccountFlow\Enums\CategoryType;
use ArtflowStudio\AccountFlow\Enums\TransactionType;
use ArtflowStudio\AccountFlow\Events\TransferCreated;
use ArtflowStudio\AccountFlow\Events\TransferDeleted;
use ArtflowStudio\AccountFlow\Exceptions\AccountFlowException;
use ArtflowStudio\AccountFlow\Exceptions\RecordNotFoundException;
use ArtflowStudio\AccountFlow\Models\Account;
use ArtflowStudio\AccountFlow\Models\Category;
use ArtflowStudio\AccountFlow\Models\Transaction;
use ArtflowStudio\AccountFlow\Models\Transfer;
use ArtflowStudio\AccountFlow\Support\BalanceUpdater;
use ArtflowStudio\AccountFlow\Support\UniqueId;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;

/**
 * Moving money between two accounts.
 *
 * A transfer posts **two linked ledger entries** — an expense on the source
 * account and an income on the destination — so balances move and
 * `recalculateAll()` can rebuild them from the ledger.
 *
 * 0.2.x wrote a row to `ac_transfers` and nothing else. Because balances are
 * derived from `ac_transactions`, the money never moved; worse, the screen then
 * called `recalculateAll()`, which rebuilt balances from a ledger that had no
 * record of the transfer, so any earlier manual correction was erased too.
 *
 * Both legs carry `transfer_id`, which is how reports exclude them from profit
 * & loss: a transfer is not revenue or a cost, it is cash changing hands.
 *
 * @example
 * AccountFlow::transfers()->create([
 *     'amount' => 500, 'from_account' => 1, 'to_account' => 2,
 *     'description' => 'Bank to petty cash',
 * ]);
 */
class TransferService
{
    public function __construct(
        private readonly BalanceUpdater $balances = new BalanceUpdater,
    ) {}

    /**
     * @param  array{
     *     amount: float|int,
     *     from_account: int,
     *     to_account: int,
     *     description?: string|null,
     *     date?: string|DateTimeInterface|null,
     *     created_by?: int|null
     * }  $data
     *
     * @throws AccountFlowException
     * @throws RecordNotFoundException
     */
    public function create(array $data): Transfer
    {
        $amount = (float) ($data['amount'] ?? 0);

        if ($amount <= 0) {
            throw new AccountFlowException('Transfer amount must be greater than 0.');
        }

        $fromId = (int) ($data['from_account'] ?? 0);
        $toId = (int) ($data['to_account'] ?? 0);

        if ($fromId === $toId) {
            throw new AccountFlowException('A transfer needs two different accounts.');
        }

        $from = Account::find($fromId) ?? throw RecordNotFoundException::account($fromId);
        $to = Account::find($toId) ?? throw RecordNotFoundException::account($toId);

        if ((float) $from->balance < $amount && ! config('accountflow.allow_negative_balance', false)) {
            throw new AccountFlowException(
                "Account [{$from->name}] has {$from->balance} available, which is less than {$amount}.",
            );
        }

        $date = isset($data['date']) ? Carbon::parse($data['date']) : Carbon::now();
        $description = $data['description'] ?? "Transfer: {$from->name} to {$to->name}";
        $userId = $data['created_by'] ?? auth()->id();

        return DB::transaction(function () use ($amount, $from, $to, $date, $description, $userId): Transfer {
            $transfer = Transfer::create([
                'unique_id' => UniqueId::for(Transfer::class),
                'amount' => $amount,
                'from_account' => $from->id,
                'to_account' => $to->id,
                'description' => $description,
                'date' => $date,
                'created_by' => $userId,
            ]);

            $categoryId = $this->transferCategoryId();

            $out = $this->postLeg($transfer, $from->id, $amount, TransactionType::Expense, $categoryId, $date, "{$description} (out)", $userId);
            $in = $this->postLeg($transfer, $to->id, $amount, TransactionType::Income, $categoryId, $date, "{$description} (in)", $userId);

            $transfer->forceFill([
                'from_trx_id' => $out->id,
                'to_trx_id' => $in->id,
            ])->save();

            $this->balances->apply($from->id, $amount, TransactionType::Expense);
            $this->balances->apply($to->id, $amount, TransactionType::Income);

            TransferCreated::dispatch($transfer);

            return $transfer->fresh();
        });
    }

    /**
     * Change a transfer, reposting both ledger legs.
     *
     * The legs are unwound and re-posted rather than edited in place, so the
     * balances always match what the ledger says.
     *
     * @param array<string,mixed> $data
     *
     * @throws AccountFlowException
     * @throws RecordNotFoundException
     */
    public function update(Transfer $transfer, array $data): Transfer
    {
        return DB::transaction(function () use ($transfer, $data): Transfer {
            $merged = [
                'amount' => $data['amount'] ?? $transfer->amount,
                'from_account' => $data['from_account'] ?? $transfer->from_account,
                'to_account' => $data['to_account'] ?? $transfer->to_account,
                'description' => $data['description'] ?? $transfer->description,
                'date' => $data['date'] ?? $transfer->date,
                'created_by' => $transfer->created_by,
            ];

            // Unwind the existing legs, then re-post from scratch.
            $this->unwind($transfer);

            $replacement = $this->create($merged);

            $transfer->delete();

            return $replacement;
        });
    }

    /**
     * Delete a transfer and unwind both ledger legs.
     */
    public function delete(Transfer $transfer): bool
    {
        return DB::transaction(function () use ($transfer): bool {
            $amount = (float) $transfer->amount;

            $this->unwind($transfer);

            $deleted = (bool) $transfer->delete();

            if ($deleted) {
                TransferDeleted::dispatch($transfer, $amount);
            }

            return $deleted;
        });
    }

    /**
     * Repost the ledger legs for transfers created before this was fixed.
     *
     * Transfers written by 0.2.x have no ledger entries at all, so their money
     * never moved. Returns the number of transfers repaired.
     */
    public function backfillLedgerEntries(): int
    {
        $repaired = 0;

        Transfer::query()
            ->whereNull('from_trx_id')
            ->orWhereNull('to_trx_id')
            ->chunkById(100, function ($transfers) use (&$repaired): void {
                foreach ($transfers as $transfer) {
                    $this->repost($transfer);
                    $repaired++;
                }
            });

        return $repaired;
    }

    /**
     * Remove a transfer's ledger legs and undo their effect on both balances.
     */
    private function unwind(Transfer $transfer): void
    {
        foreach ($transfer->legs() as $leg) {
            $type = $leg->transactionType();

            if ($type !== null) {
                $this->balances->reverse((int) $leg->account_id, (float) $leg->amount, $type);
            }

            $leg->delete();
        }
    }

    /**
     * Post the missing legs for one legacy transfer.
     */
    private function repost(Transfer $transfer): void
    {
        DB::transaction(function () use ($transfer): void {
            $amount = (float) $transfer->amount;
            $categoryId = $this->transferCategoryId();
            $date = $transfer->date ?? Carbon::now();
            $description = $transfer->description ?? 'Transfer';

            if ($transfer->from_trx_id === null) {
                $out = $this->postLeg($transfer, (int) $transfer->from_account, $amount, TransactionType::Expense, $categoryId, $date, "{$description} (out)", $transfer->created_by);
                $transfer->forceFill(['from_trx_id' => $out->id])->save();
                $this->balances->apply((int) $transfer->from_account, $amount, TransactionType::Expense);
            }

            if ($transfer->to_trx_id === null) {
                $in = $this->postLeg($transfer, (int) $transfer->to_account, $amount, TransactionType::Income, $categoryId, $date, "{$description} (in)", $transfer->created_by);
                $transfer->forceFill(['to_trx_id' => $in->id])->save();
                $this->balances->apply((int) $transfer->to_account, $amount, TransactionType::Income);
            }
        });
    }

    private function postLeg(
        Transfer $transfer,
        int $accountId,
        float $amount,
        TransactionType $type,
        int $categoryId,
        mixed $date,
        string $description,
        ?int $userId,
    ): Transaction {
        return Transaction::create([
            'unique_id' => UniqueId::for(Transaction::class),
            'amount' => $amount,
            'type' => $type->value,
            'account_id' => $accountId,
            'category_id' => $categoryId,
            'payment_method' => null,
            'date' => $date,
            'description' => $description,
            'added_by' => $userId,
            'transfer_id' => $transfer->id,
        ]);
    }

    /**
     * The category transfer legs are booked under.
     *
     * Configurable via `accountflow.transfer_category_id`; otherwise a
     * dedicated "Transfers" category is created once and reused. A category is
     * required because `ac_transactions.category_id` is NOT NULL.
     */
    private function transferCategoryId(): int
    {
        $configured = config('accountflow.transfer_category_id');

        if ($configured !== null && Category::whereKey($configured)->exists()) {
            return (int) $configured;
        }

        return (int) Category::firstOrCreate(
            ['name' => 'Transfers', 'type' => CategoryType::Expense->value, 'parent_id' => null],
            ['privacy' => 1, 'status' => 1, 'icon' => 'initiate_money_transfer.svg'],
        )->id;
    }
}
