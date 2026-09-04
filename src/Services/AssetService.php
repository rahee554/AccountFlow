<?php

namespace ArtflowStudio\AccountFlow\Services;

use ArtflowStudio\AccountFlow\Enums\TransactionType;
use ArtflowStudio\AccountFlow\Exceptions\AccountFlowException;
use ArtflowStudio\AccountFlow\Exceptions\RecordNotFoundException;
use ArtflowStudio\AccountFlow\Models\Asset;
use ArtflowStudio\AccountFlow\Models\AssetTransaction;
use ArtflowStudio\AccountFlow\Models\Transaction;
use ArtflowStudio\AccountFlow\Support\UniqueId;
use Illuminate\Support\Facades\DB;

/**
 * Assets, and the money spent on or received for them.
 *
 * `CreateAssetTransaction` used to build `new Transaction(...)->save()`
 * directly, bypassing TransactionService — so the row was written but the
 * account balance never moved. Buying a laptop recorded the purchase and left
 * the cash sitting in the account.
 *
 * Every posting here goes through TransactionService, which is the only write
 * path that updates balances, validates and raises events.
 *
 * @example
 * AccountFlow::assets()->purchase($assetId, 80000, ['account_id' => 1]);
 * AccountFlow::assets()->sell($assetId, 45000);
 */
class AssetService
{
    public function __construct(
        private readonly TransactionService $transactions = new TransactionService,
    ) {}

    /**
     * Record money spent on an asset — a purchase or an improvement.
     *
     * @param array<string,mixed> $attributes account_id, date, description, payment_method
     *
     * @throws AccountFlowException
     * @throws RecordNotFoundException
     */
    public function purchase(int $assetId, float $amount, array $attributes = []): AssetTransaction
    {
        return $this->post($assetId, $amount, TransactionType::Expense, $attributes);
    }

    /**
     * Record money received for an asset — a disposal.
     *
     * @param array<string,mixed> $attributes
     *
     * @throws AccountFlowException
     * @throws RecordNotFoundException
     */
    public function sell(int $assetId, float $amount, array $attributes = []): AssetTransaction
    {
        $assetTransaction = $this->post($assetId, $amount, TransactionType::Income, $attributes);

        // 3 = sold out, per the status comment on ac_assets.
        Asset::whereKey($assetId)->update(['status' => 3]);

        return $assetTransaction;
    }

    /**
     * Total spent on an asset, less anything received for it.
     */
    public function netCost(int $assetId): float
    {
        $totals = Transaction::query()
            ->whereIn('id', AssetTransaction::where('asset_id', $assetId)->select('trx_id'))
            ->toBase()
            ->selectRaw('COALESCE(SUM(CASE WHEN type = ? THEN amount ELSE 0 END), 0) as spent', [TransactionType::Expense->value])
            ->selectRaw('COALESCE(SUM(CASE WHEN type = ? THEN amount ELSE 0 END), 0) as received', [TransactionType::Income->value])
            ->first();

        return (float) ($totals->spent ?? 0) - (float) ($totals->received ?? 0);
    }

    /**
     * Remove an asset posting and unwind its effect on the balance.
     */
    public function deleteTransaction(AssetTransaction $assetTransaction): bool
    {
        return DB::transaction(function () use ($assetTransaction): bool {
            $transaction = $assetTransaction->transaction;

            if ($transaction !== null) {
                $this->transactions->delete($transaction);
            }

            return (bool) $assetTransaction->delete();
        });
    }

    /**
     * @param array<string,mixed> $attributes
     *
     * @throws AccountFlowException
     * @throws RecordNotFoundException
     */
    private function post(int $assetId, float $amount, TransactionType $type, array $attributes): AssetTransaction
    {
        if ($amount <= 0) {
            throw new AccountFlowException('An asset amount must be greater than 0.');
        }

        $asset = Asset::with('category')->find($assetId)
            ?? throw new RecordNotFoundException("Asset #{$assetId} was not found.");

        return DB::transaction(function () use ($asset, $amount, $type, $attributes): AssetTransaction {
            $transaction = $this->transactions->create([
                'type' => $type,
                'amount' => $amount,
                // The asset's own category, when it has one; otherwise the
                // configured default for this direction.
                'category_id' => $attributes['category_id'] ?? $asset->category_id,
                'account_id' => $attributes['account_id'] ?? null,
                'payment_method' => $attributes['payment_method'] ?? null,
                'date' => $attributes['date'] ?? null,
                'description' => $attributes['description'] ?? "Asset: {$asset->name}",
            ]);

            return AssetTransaction::create([
                'unique_id' => UniqueId::for(AssetTransaction::class),
                'asset_id' => $asset->id,
                'trx_id' => $transaction->id,
            ]);
        });
    }
}
