<?php

namespace ArtflowStudio\AccountFlow\App\Services;

use App\Models\AccountFlow\Account;
use App\Models\AccountFlow\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

/**
 * AccountService - Account Management
 *
 * Handles all account-related operations including:
 * - Creating and updating accounts
 * - Balance calculations and tracking
 * - Account status management
 * - Account statistics and analytics
 *
 * @example
 * // Create account
 * $account = AccountService::create([
 *     'name' => 'Checking Account',
 *     'description' => 'Main business account',
 *     'opening_balance' => 5000
 * ]);
 *
 * // Get account balance
 * $balance = AccountService::getBalance($account->id);
 *
 * // Get account transactions
 * $transactions = AccountService::getTransactions($account->id, $startDate, $endDate);
 */
class AccountService
{
    /**
     * Create a new account
     *
     * @param array{
     *     name: string,
     *     description?: string,
     *     opening_balance?: float,
     *     active?: bool
     * } $data
     *
     * @return Account
     *
     * @throws \Exception
     */
    public static function create(array $data): Account
    {
        return DB::transaction(function () use ($data) {
            // Validate required fields
            if (empty($data['name'])) {
                throw new \Exception('Account name is required');
            }

            // Prepare account data
            $accountData = [
                'name' => trim($data['name']),
                'description' => $data['description'] ?? null,
                'opening_balance' => (float) ($data['opening_balance'] ?? 0),
                'balance' => (float) ($data['opening_balance'] ?? 0),
                'active' => (bool) ($data['active'] ?? true),
            ];

            // Create account
            $account = Account::create($accountData);

            return $account;
        });
    }

    /**
     * Update an account
     *
     * @param Account $account
     * @param array $data
     *
     * @return Account
     */
    public static function update(Account $account, array $data): Account
    {
        return DB::transaction(function () use ($account, $data) {
            $updateData = [];

            if (isset($data['name'])) {
                $updateData['name'] = trim($data['name']);
            }

            if (isset($data['description'])) {
                $updateData['description'] = $data['description'];
            }

            if (isset($data['active'])) {
                $updateData['active'] = (bool) $data['active'];
            }

            if (!empty($updateData)) {
                $account->update($updateData);
            }

            return $account->fresh();
        });
    }

    /**
     * Get current account balance
     *
     * @param int $accountId
     *
     * @return float
     */
    public static function getBalance(int $accountId): float
    {
        $account = Account::find($accountId);

        if (!$account) {
            throw new \Exception("Account #{$accountId} not found");
        }

        return (float) $account->balance;
    }

    /**
     * Recalculate account balance from transactions
     *
     * @param int $accountId
     *
     * @return float
     */
    public static function recalculateBalance(int $accountId): float
    {
        return DB::transaction(function () use ($accountId) {
            $account = Account::find($accountId);

            if (!$account) {
                throw new \Exception("Account #{$accountId} not found");
            }

            // Get opening balance
            $balance = $account->opening_balance ?? 0;

            // Add all income transactions
            $income = Transaction::where('account_id', $accountId)
                ->where('type', 1)
                ->sum('amount');

            // Subtract all expense transactions
            $expenses = Transaction::where('account_id', $accountId)
                ->where('type', 2)
                ->sum('amount');

            $balance += $income - $expenses;

            // Update account
            $account->update(['balance' => $balance]);

            return (float) $balance;
        });
    }

    /**
     * Get transactions for an account
     *
     * @param int $accountId
     * @param string|null $startDate
     * @param string|null $endDate
     * @param int $limit
     *
     * @return Collection
     */
    public static function getTransactions(
        int $accountId,
        ?string $startDate = null,
        ?string $endDate = null,
        int $limit = 50
    ): Collection {
        $query = Transaction::where('account_id', $accountId);

        if ($startDate) {
            $query->whereDate('date', '>=', Carbon::parse($startDate));
        }

        if ($endDate) {
            $query->whereDate('date', '<=', Carbon::parse($endDate));
        }

        return $query->latest('date')->limit($limit)->get();
    }

    /**
     * Get account statistics
     *
     * @param int $accountId
     * @param string|null $startDate
     * @param string|null $endDate
     *
     * @return array
     */
    public static function getStatistics(
        int $accountId,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $query = Transaction::where('account_id', $accountId);

        if ($startDate) {
            $query->whereDate('date', '>=', Carbon::parse($startDate));
        }

        if ($endDate) {
            $query->whereDate('date', '<=', Carbon::parse($endDate));
        }

        $transactions = $query->get();

        $income = $transactions->where('type', 1)->sum('amount');
        $expenses = $transactions->where('type', 2)->sum('amount');

        return [
            'total_income' => (float) $income,
            'total_expenses' => (float) $expenses,
            'net' => (float) ($income - $expenses),
            'transaction_count' => $transactions->count(),
            'average_transaction' => $transactions->count() > 0 ? (float) ($transactions->sum('amount') / $transactions->count()) : 0,
            'by_category' => $transactions->groupBy('category_id')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'amount' => (float) $group->sum('amount'),
                ];
            }),
        ];
    }

    /**
     * Get all accounts
     *
     * @param bool $onlyActive
     *
     * @return Collection
     */
    public static function getAll(bool $onlyActive = true): Collection
    {
        $query = Account::query();

        if ($onlyActive) {
            $query->where('active', true);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Deactivate an account
     *
     * @param Account $account
     *
     * @return Account
     */
    public static function deactivate(Account $account): Account
    {
        return self::update($account, ['active' => false]);
    }

    /**
     * Activate an account
     *
     * @param Account $account
     *
     * @return Account
     */
    public static function activate(Account $account): Account
    {
        return self::update($account, ['active' => true]);
    }

    /**
     * Delete an account
     * Only if it has no transactions
     *
     * @param Account $account
     *
     * @return bool
     *
     * @throws \Exception
     */
    public static function delete(Account $account): bool
    {
        return DB::transaction(function () use ($account) {
            // Check if account has transactions
            if ($account->transactions()->exists()) {
                throw new \Exception('Cannot delete account with existing transactions');
            }

            return $account->delete();
        });
    }

    /**
     * Update balance for all accounts based on their transactions
     *
     * Recalculates balance for all accounts by summing:
     * - Income transactions (type=1)
     * - Expense transactions (type=2)
     * - Transfers in/out
     *
     * Formula: Opening Balance + Income + Transfers In - (Expenses + Transfers Out)
     *
     * @return void
     */
    public static function updateAllAccountBalances(): void
    {
        DB::transaction(function () {
            $accounts = Account::all();

            foreach ($accounts as $account) {
                self::recalculateBalance($account->id);
            }
        });
    }

    /**
     * Add a value to an account's balance
     *
     * @param int $accountId
     * @param float $value
     *
     * @return Account|null
     */
    public static function addToBalance(int $accountId, float $value): ?Account
    {
        return DB::transaction(function () use ($accountId, $value) {
            $account = Account::find($accountId);

            if (!$account) {
                return null;
            }

            $account->balance += $value;
            $account->save();

            return $account->fresh();
        });
    }

    /**
     * Subtract a value from an account's balance
     *
     * @param int $accountId
     * @param float $value
     *
     * @return Account|null
     */
    public static function subtractFromBalance(int $accountId, float $value): ?Account
    {
        return DB::transaction(function () use ($accountId, $value) {
            $account = Account::find($accountId);

            if (!$account) {
                return null;
            }

            $account->balance -= $value;
            $account->save();

            return $account->fresh();
        });
    }
}

