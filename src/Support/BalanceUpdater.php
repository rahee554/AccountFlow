<?php

namespace ArtflowStudio\AccountFlow\Support;

use ArtflowStudio\AccountFlow\Enums\TransactionType;
use ArtflowStudio\AccountFlow\Events\AccountBalanceChanged;
use ArtflowStudio\AccountFlow\Exceptions\RecordNotFoundException;
use ArtflowStudio\AccountFlow\Models\Account;
use Illuminate\Support\Facades\DB;

/**
 * The only place an account balance is written.
 *
 * Every mutation selects the row `FOR UPDATE` inside a transaction, so two
 * concurrent requests serialise instead of racing. 0.2.x did a plain
 * read-modify-write (`$account->balance += $value; $account->save();`), which
 * loses one of any two overlapping updates and silently corrupts the balance.
 */
final class BalanceUpdater
{
    /**
     * Apply a transaction's effect to its account.
     */
    public function apply(int $accountId, float $amount, TransactionType $type): Account
    {
        return $this->adjust($accountId, $amount * $type->sign());
    }

    /**
     * Undo a transaction's effect.
     */
    public function reverse(int $accountId, float $amount, TransactionType $type): Account
    {
        return $this->adjust($accountId, -1 * $amount * $type->sign());
    }

    /**
     * Move an account balance by a signed delta, under a row lock.
     */
    public function adjust(int $accountId, float $delta): Account
    {
        return DB::transaction(function () use ($accountId, $delta): Account {
            $account = Account::query()->lockForUpdate()->find($accountId);

            if ($account === null) {
                throw RecordNotFoundException::account($accountId);
            }

            $from = (float) $account->balance;
            $to = $from + $delta;

            $account->setAttribute('balance', $to);
            $account->save();

            if ($delta !== 0.0) {
                AccountBalanceChanged::dispatch($account, $from, $to);
            }

            return $account;
        });
    }

    /**
     * Set a balance outright, under a row lock.
     */
    public function set(int $accountId, float $balance): Account
    {
        return DB::transaction(function () use ($accountId, $balance): Account {
            $account = Account::query()->lockForUpdate()->find($accountId);

            if ($account === null) {
                throw RecordNotFoundException::account($accountId);
            }

            $from = (float) $account->balance;

            $account->setAttribute('balance', $balance);
            $account->save();

            if ($from !== $balance) {
                AccountBalanceChanged::dispatch($account, $from, $balance);
            }

            return $account;
        });
    }

    /**
     * Recompute a balance from the ledger and store it.
     *
     * The repair path for accounts whose stored balance has drifted.
     */
    public function recalculate(int $accountId): float
    {
        return DB::transaction(function () use ($accountId): float {
            $account = Account::query()->lockForUpdate()->find($accountId);

            if ($account === null) {
                throw RecordNotFoundException::account($accountId);
            }

            $balance = $account->calculatedBalance();
            $from = (float) $account->balance;

            $account->setAttribute('balance', $balance);
            $account->save();

            if ($from !== $balance) {
                AccountBalanceChanged::dispatch($account, $from, $balance);
            }

            return $balance;
        });
    }
}
