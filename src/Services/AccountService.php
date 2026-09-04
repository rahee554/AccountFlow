<?php

namespace ArtflowStudio\AccountFlow\Services;

use ArtflowStudio\AccountFlow\Enums\TransactionType;
use ArtflowStudio\AccountFlow\Exceptions\AccountFlowException;
use ArtflowStudio\AccountFlow\Exceptions\RecordNotFoundException;
use ArtflowStudio\AccountFlow\Models\Account;
use ArtflowStudio\AccountFlow\Models\Transaction;
use ArtflowStudio\AccountFlow\Support\BalanceUpdater;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Accounts and their balances.
 *
 * Every balance write goes through BalanceUpdater, which holds a row lock —
 * this class never touches `balance` directly.
 *
 * @example
 * AccountFlow::accounts()->create(['name' => 'Petty Cash', 'opening_balance' => 5000]);
 * AccountFlow::accounts()->getBalance($id);
 * AccountFlow::accounts()->recalculateBalance($id);
 */
class AccountService
{
    public function __construct(
        private readonly BalanceUpdater $balances = new BalanceUpdater,
    ) {}

    /**
     * @param array{name: string, description?: string|null, opening_balance?: float, active?: bool} $data
     *
     * @throws AccountFlowException
     */
    public function create(array $data): Account
    {
        if (empty($data['name'])) {
            throw new AccountFlowException('Account name is required.');
        }

        $opening = (float) ($data['opening_balance'] ?? 0);

        return Account::create([
            'name' => trim((string) $data['name']),
            'description' => $data['description'] ?? null,
            'opening_balance' => $opening,
            // A new account starts at its opening balance. This used to be
            // dropped entirely because `opening_balance` was missing from
            // Account::$fillable.
            'balance' => $opening,
            'active' => (bool) ($data['active'] ?? true),
        ]);
    }

    /**
     * @param array<string,mixed> $data
     */
    public function update(Account $account, array $data): Account
    {
        $changes = [];

        if (array_key_exists('name', $data)) {
            $changes['name'] = trim((string) $data['name']);
        }

        if (array_key_exists('description', $data)) {
            $changes['description'] = $data['description'];
        }

        if (array_key_exists('active', $data)) {
            $changes['active'] = (bool) $data['active'];
        }

        // `opening_balance` shifts every later balance, so re-derive rather
        // than letting the stored balance drift out of step with the ledger.
        if (array_key_exists('opening_balance', $data)) {
            $changes['opening_balance'] = (float) $data['opening_balance'];
        }

        if ($changes !== []) {
            $account->update($changes);

            if (array_key_exists('opening_balance', $changes)) {
                $this->balances->recalculate((int) $account->id);
            }
        }

        return $account->fresh();
    }

    /**
     * @throws RecordNotFoundException
     */
    public function getBalance(int $accountId): float
    {
        $balance = Account::whereKey($accountId)->value('balance');

        if ($balance === null && ! Account::whereKey($accountId)->exists()) {
            throw RecordNotFoundException::account($accountId);
        }

        return (float) $balance;
    }

    /**
     * Recompute a stored balance from the ledger.
     */
    public function recalculateBalance(int $accountId): float
    {
        return $this->balances->recalculate($accountId);
    }

    /**
     * Recompute every account's balance. Returns account id => balance.
     *
     * @return array<int,float>
     */
    public function recalculateAll(): array
    {
        $results = [];

        Account::query()->select('id')->chunkById(100, function (Collection $accounts) use (&$results): void {
            foreach ($accounts as $account) {
                $results[(int) $account->id] = $this->balances->recalculate((int) $account->id);
            }
        });

        return $results;
    }

    /**
     * Alias of recalculateAll(), kept because 0.2.x exposed this name and the
     * transfer screen still calls it.
     */
    public function updateAllAccountBalances(): void
    {
        $this->recalculateAll();
    }

    public function addToBalance(int $accountId, float $value): ?Account
    {
        return $this->balances->adjust($accountId, $value);
    }

    public function subtractFromBalance(int $accountId, float $value): ?Account
    {
        return $this->balances->adjust($accountId, -$value);
    }

    public function activate(Account $account): Account
    {
        $account->update(['active' => true]);

        return $account->fresh();
    }

    public function deactivate(Account $account): Account
    {
        $account->update(['active' => false]);

        return $account->fresh();
    }

    /**
     * @return Collection<int,Account>
     */
    public function getActive(): Collection
    {
        return Account::query()->active()->orderBy('name')->get();
    }

    /**
     * @return Collection<int,Account>
     */
    public function getAll(): Collection
    {
        return Account::query()->orderBy('name')->get();
    }

    /**
     * Ledger entries for an account, newest first.
     *
     * @return Collection<int,Transaction>
     */
    public function getTransactions(int $accountId, ?string $from = null, ?string $to = null): Collection
    {
        return Transaction::query()
            ->forAccount($accountId)
            ->between($from, $to)
            ->with(['category', 'paymentMethod'])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Per-account totals for a period, aggregated in SQL.
     *
     * @return array{opening_balance: float, income: float, expense: float, net: float, balance: float}
     *
     * @throws RecordNotFoundException
     */
    public function getStatistics(int $accountId, ?string $from = null, ?string $to = null): array
    {
        $account = Account::find($accountId) ?? throw RecordNotFoundException::account($accountId);

        $totals = Transaction::query()
            ->forAccount($accountId)
            ->between($from, $to)
            ->selectRaw('COALESCE(SUM(CASE WHEN type = ? THEN amount ELSE 0 END), 0) as income', [TransactionType::Income->value])
            ->selectRaw('COALESCE(SUM(CASE WHEN type = ? THEN amount ELSE 0 END), 0) as expense', [TransactionType::Expense->value])
            ->first();

        $income = (float) ($totals->income ?? 0);
        $expense = (float) ($totals->expense ?? 0);

        return [
            'opening_balance' => (float) $account->opening_balance,
            'income' => $income,
            'expense' => $expense,
            'net' => $income - $expense,
            'balance' => (float) $account->balance,
        ];
    }

    /**
     * Total held across all active accounts.
     */
    public function totalBalance(): float
    {
        return (float) Account::query()->active()->sum('balance');
    }

    /**
     * Delete an account, refusing while transactions still reference it.
     *
     * @throws AccountFlowException
     */
    public function delete(Account $account): bool
    {
        return DB::transaction(function () use ($account): bool {
            if ($account->transactions()->exists()) {
                throw new AccountFlowException(
                    "Account #{$account->id} still has transactions and cannot be deleted. Deactivate it instead.",
                );
            }

            return (bool) $account->delete();
        });
    }
}
