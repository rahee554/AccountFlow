<?php

namespace ArtflowStudio\AccountFlow\Services;

use ArtflowStudio\AccountFlow\Enums\EquityTransactionType;
use ArtflowStudio\AccountFlow\Enums\TransactionType;
use ArtflowStudio\AccountFlow\Exceptions\AccountFlowException;
use ArtflowStudio\AccountFlow\Exceptions\RecordNotFoundException;
use ArtflowStudio\AccountFlow\Models\EquityPartner;
use ArtflowStudio\AccountFlow\Models\EquityTransaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Partner capital in and out.
 *
 * There was no way to record an equity movement at all: the
 * `CreateEquityTransaction` component had only `mount()` and `render()` — no
 * save method — so a partner could invest 200,000 and nothing would happen
 * anywhere. The equity list could delete rows it had no way to create.
 *
 * A contribution brings cash in and increases the partner's equity; a
 * withdrawal does the reverse. Profit and loss shares move equity without
 * moving cash, so they post no ledger entry.
 *
 * @example
 * AccountFlow::equity()->contribute($partnerId, 200000);
 * AccountFlow::equity()->withdraw($partnerId, 50000);
 * AccountFlow::equity()->shareProfit($partnerId, 30000);
 */
class EquityService
{
    public function __construct(
        private readonly TransactionService $transactions = new TransactionService,
    ) {}

    /**
     * Capital in: cash increases, so does the partner's equity.
     *
     * @param array<string,mixed> $attributes
     *
     * @throws AccountFlowException
     * @throws RecordNotFoundException
     */
    public function contribute(int $partnerId, float $amount, array $attributes = []): EquityTransaction
    {
        return $this->record($partnerId, $amount, EquityTransactionType::Contribution, $attributes);
    }

    /**
     * Capital out: cash decreases, so does the partner's equity.
     *
     * @param array<string,mixed> $attributes
     *
     * @throws AccountFlowException
     * @throws RecordNotFoundException
     */
    public function withdraw(int $partnerId, float $amount, array $attributes = []): EquityTransaction
    {
        return $this->record($partnerId, $amount, EquityTransactionType::Withdrawal, $attributes);
    }

    /**
     * Allocate profit to a partner. Moves equity, not cash.
     *
     * @param array<string,mixed> $attributes
     *
     * @throws AccountFlowException
     * @throws RecordNotFoundException
     */
    public function shareProfit(int $partnerId, float $amount, array $attributes = []): EquityTransaction
    {
        return $this->record($partnerId, $amount, EquityTransactionType::ProfitShare, $attributes);
    }

    /**
     * Allocate a loss to a partner. Moves equity, not cash.
     *
     * @param array<string,mixed> $attributes
     *
     * @throws AccountFlowException
     * @throws RecordNotFoundException
     */
    public function shareLoss(int $partnerId, float $amount, array $attributes = []): EquityTransaction
    {
        return $this->record($partnerId, $amount, EquityTransactionType::LossShare, $attributes);
    }

    /**
     * Recompute a partner's equity from their movements.
     */
    public function recalculateEquity(int $partnerId): float
    {
        $partner = EquityPartner::find($partnerId)
            ?? throw new RecordNotFoundException("Equity partner #{$partnerId} was not found.");

        $equity = 0.0;

        foreach (EquityTransaction::where('partner_id', $partnerId)->get() as $movement) {
            $equity += $movement->signedAmount();
        }

        $partner->forceFill(['current_equity' => $equity])->save();

        return $equity;
    }

    /**
     * @return Collection<int,EquityTransaction>
     */
    public function forPartner(int $partnerId): Collection
    {
        return EquityTransaction::query()
            ->where('partner_id', $partnerId)
            ->with('transaction')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @return array<string,mixed>
     */
    public function summary(int $partnerId): array
    {
        $movements = EquityTransaction::where('partner_id', $partnerId)->get();

        $total = fn (EquityTransactionType $type): float => (float) $movements
            ->where('type', $type->value)
            ->sum('amount');

        return [
            'partner_id' => $partnerId,
            'contributions' => $total(EquityTransactionType::Contribution),
            'withdrawals' => $total(EquityTransactionType::Withdrawal),
            'profit_share' => $total(EquityTransactionType::ProfitShare),
            'loss_share' => $total(EquityTransactionType::LossShare),
            'current_equity' => (float) (EquityPartner::whereKey($partnerId)->value('current_equity') ?? 0),
        ];
    }

    /**
     * Remove an equity movement, unwinding any cash it moved.
     */
    public function delete(EquityTransaction $movement): bool
    {
        return DB::transaction(function () use ($movement): bool {
            $partnerId = (int) $movement->partner_id;
            $transaction = $movement->transaction;

            if ($transaction !== null) {
                $this->transactions->delete($transaction);
            }

            $deleted = (bool) $movement->delete();

            if ($deleted && $partnerId > 0) {
                $this->recalculateEquity($partnerId);
            }

            return $deleted;
        });
    }

    /**
     * @param array<string,mixed> $attributes
     *
     * @throws AccountFlowException
     * @throws RecordNotFoundException
     */
    private function record(
        int $partnerId,
        float $amount,
        EquityTransactionType $type,
        array $attributes,
    ): EquityTransaction {
        if ($amount <= 0) {
            throw new AccountFlowException('An equity amount must be greater than 0.');
        }

        $partner = EquityPartner::find($partnerId)
            ?? throw new RecordNotFoundException("Equity partner #{$partnerId} was not found.");

        return DB::transaction(function () use ($partner, $amount, $type, $attributes): EquityTransaction {
            $transactionId = null;

            // Contributions and withdrawals move real cash. Profit and loss
            // shares only reallocate equity, so they post nothing to the ledger.
            $cashDirection = match ($type) {
                EquityTransactionType::Contribution => TransactionType::Income,
                EquityTransactionType::Withdrawal => TransactionType::Expense,
                default => null,
            };

            if ($cashDirection !== null) {
                $transactionId = $this->transactions->create([
                    'type' => $cashDirection,
                    'amount' => $amount,
                    'account_id' => $attributes['account_id'] ?? null,
                    'category_id' => $attributes['category_id'] ?? null,
                    'payment_method' => $attributes['payment_method'] ?? null,
                    'date' => $attributes['date'] ?? null,
                    'description' => $attributes['description'] ?? "{$type->label()}: {$partner->name}",
                ])->id;
            }

            $movement = EquityTransaction::create([
                'partner_id' => $partner->id,
                'trx_id' => $transactionId,
                'type' => $type->value,
                'amount' => $amount,
                'description' => $attributes['description'] ?? $type->label(),
            ]);

            $this->recalculateEquity((int) $partner->id);

            return $movement;
        });
    }
}
