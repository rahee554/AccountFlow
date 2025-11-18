<?php

namespace ArtflowStudio\AccountFlow\App\Services;

use App\Models\AccountFlow\Transaction;
use App\Models\AccountFlow\PaymentMethod;
use App\Models\AccountFlow\Account;
use App\Models\AccountFlow\Category;
use App\Models\AccountFlow\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    /**
     * Create a transaction with auto-filled defaults
     *
     * @param array{
     *     amount: float|int,
     *     type: int|string,
     *     payment_method?: int|null,
     *     account_id?: int|null,
     *     category_id?: int|null,
     *     date?: string|\Carbon\Carbon|null,
     *     description?: string|null,
     *     user_id?: int|null
     * } $data
     *
     * @return Transaction
     *
     * @throws \Exception
     */
    public static function create(array $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            // Prepare transaction data
            $normalizedType = self::normalizeTransactionType($data['type']);

            $transactionData = [
                'unique_id' => generateUniqueId(Transaction::class, 'unique_id'),
                'amount' => (float) $data['amount'],
                'type' => $normalizedType,
                'payment_method' => (int) ($data['payment_method'] ?? Setting::defaultPaymentMethodId()),
                'category_id' => (int) ($data['category_id'] ?? self::getDefaultCategoryIdForType($normalizedType)),
                'date' => ($data['date'] ?? null) ? Carbon::parse($data['date']) : Carbon::now(),
                'description' => $data['description'] ?? null,
                'added_by' => $data['user_id'] ?? auth()->id(),
            ];

            // Auto-resolve account from payment method if not provided
            if (empty($data['account_id'])) {
                $paymentMethod = PaymentMethod::find($transactionData['payment_method']);
                $transactionData['account_id'] = $paymentMethod?->account_id ?? Setting::defaultAccountId();
            } else {
                $transactionData['account_id'] = (int) $data['account_id'];
            }

            // Validate required fields
            self::validateTransactionData($transactionData);

            // Create the transaction
            $transaction = Transaction::create($transactionData);

            // Update account balance based on transaction type
            // Type 1 = Income (add to balance)
            // Type 2 = Expense (subtract from balance)
            if ($normalizedType == 1) {
                AccountService::addToBalance($transactionData['account_id'], $transactionData['amount']);
            } elseif ($normalizedType == 2) {
                AccountService::subtractFromBalance($transactionData['account_id'], $transactionData['amount']);
            }

            return $transaction;
        });
    }

    /**
     * Create multiple transactions in a batch
     *
     * @param array<array> $transactions
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function createBatch(array $transactions): \Illuminate\Database\Eloquent\Collection
    {
        return DB::transaction(function () use ($transactions) {
            $created = collect();

            foreach ($transactions as $data) {
                $created->push(self::create($data));
            }

            return $created;
        });
    }

    /**
     * Create an income transaction
     *
     * @param array{
     *     amount: float|int,
     *     payment_method?: int|null,
     *     account_id?: int|null,
     *     category_id?: int|null,
     *     date?: string|\Carbon\Carbon|null,
     *     description?: string|null,
     *     user_id?: int|null
     * } $data
     *
     * @return Transaction
     */
    public static function createIncome(array $data): Transaction
    {
        $data['type'] = 1; // Income type
        return self::create($data);
    }

    /**
     * Create an expense transaction
     *
     * @param array{
     *     amount: float|int,
     *     payment_method?: int|null,
     *     account_id?: int|null,
     *     category_id?: int|null,
     *     date?: string|\Carbon\Carbon|null,
     *     description?: string|null,
     *     user_id?: int|null
     * } $data
     *
     * @return Transaction
     */
    public static function createExpense(array $data): Transaction
    {
        $data['type'] = 2; // Expense type
        return self::create($data);
    }

    /**
     * Update a transaction
     *
     * @param Transaction $transaction
     * @param array $data
     *
     * @return Transaction
     */
    public static function update(Transaction $transaction, array $data): Transaction
    {
        return DB::transaction(function () use ($transaction, $data) {
            // Store original values for balance reversal
            $originalAmount = $transaction->amount;
            $originalType = $transaction->type;
            $originalAccountId = $transaction->account_id;

            // Prepare update data
            $updateData = [];

            if (isset($data['amount'])) {
                $updateData['amount'] = (float) $data['amount'];
            }

            if (isset($data['type'])) {
                $updateData['type'] = self::normalizeTransactionType($data['type']);
            }

            if (isset($data['payment_method'])) {
                $updateData['payment_method'] = (int) $data['payment_method'];

                // Auto-resolve account if payment method changed
                if (!isset($data['account_id'])) {
                    $paymentMethod = PaymentMethod::find($data['payment_method']);
                    $updateData['account_id'] = $paymentMethod?->account_id ?? $transaction->account_id;
                }
            }

            if (isset($data['account_id'])) {
                $updateData['account_id'] = (int) $data['account_id'];
            }

            if (isset($data['category_id'])) {
                $updateData['category_id'] = (int) $data['category_id'];
            }

            if (isset($data['date'])) {
                $updateData['date'] = Carbon::parse($data['date']);
            }

            if (isset($data['description'])) {
                $updateData['description'] = $data['description'];
            }

            if (isset($data['user_id'])) {
                $updateData['user_id'] = (int) $data['user_id'];
            }

            // Validate before updating
            if (!empty($updateData)) {
                self::validateTransactionData(array_merge($transaction->toArray(), $updateData));

                // Check if balance-affecting fields changed
                $amountChanged = isset($updateData['amount']) && $updateData['amount'] != $originalAmount;
                $typeChanged = isset($updateData['type']) && $updateData['type'] != $originalType;
                $accountChanged = isset($updateData['account_id']) && $updateData['account_id'] != $originalAccountId;

                // If balance-affecting fields changed, reverse the old impact
                if ($amountChanged || $typeChanged || $accountChanged) {
                    // Reverse the original transaction impact on the original account
                    if ($originalType == 1) {
                        AccountService::subtractFromBalance($originalAccountId, $originalAmount);
                    } elseif ($originalType == 2) {
                        AccountService::addToBalance($originalAccountId, $originalAmount);
                    }

                    // Apply the new transaction impact on the new account
                    $newType = $updateData['type'] ?? $originalType;
                    $newAmount = $updateData['amount'] ?? $originalAmount;
                    $newAccountId = $updateData['account_id'] ?? $originalAccountId;

                    if ($newType == 1) {
                        AccountService::addToBalance($newAccountId, $newAmount);
                    } elseif ($newType == 2) {
                        AccountService::subtractFromBalance($newAccountId, $newAmount);
                    }
                }

                $transaction->update($updateData);
            }

            return $transaction->fresh();
        });
    }

    /**
     * Delete a transaction
     * Reverses the account balance impact when deleted
     *
     * @param Transaction $transaction
     *
     * @return bool
     */
    public static function delete(Transaction $transaction): bool
    {
        return DB::transaction(function () use ($transaction) {
            // Reverse the transaction impact on the account before deletion
            // This ensures account balance stays accurate
            if ($transaction->type == 1) {
                // Income: subtract from balance
                AccountService::subtractFromBalance($transaction->account_id, $transaction->amount);
            } elseif ($transaction->type == 2) {
                // Expense: add back to balance
                AccountService::addToBalance($transaction->account_id, $transaction->amount);
            }

            return $transaction->delete();
        });
    }

    /**
     * Reverse/void a transaction (create a reversing entry)
     * Creates a reversing transaction with negative amount to offset the original
     *
     * @param Transaction $transaction
     * @param string|null $reason
     *
     * @return Transaction
     */
    public static function reverse(Transaction $transaction, ?string $reason = null): Transaction
    {
        return DB::transaction(function () use ($transaction, $reason) {
            // Create reversing transaction with negative amount
            // This will automatically apply the opposite balance impact through the create method
            $reversingData = [
                'amount' => abs($transaction->amount), // Use positive amount
                'type' => $transaction->type == 1 ? 2 : 1, // Reverse the type (income becomes expense, vice versa)
                'payment_method' => $transaction->payment_method,
                'account_id' => $transaction->account_id,
                'category_id' => $transaction->category_id,
                'date' => Carbon::now(),
                'description' => 'REVERSAL: '.($reason ?? $transaction->description ?? "Reversed transaction #{$transaction->id}"),
                'user_id' => auth()->id(),
            ];

            return self::create($reversingData);
        });
    }

    /**
     * Get the account for a transaction
     * Auto-resolves from payment method if needed
     *
     * @param int|null $accountId
     * @param int|null $paymentMethodId
     *
     * @return Account|null
     */
    public static function resolveAccount(?int $accountId = null, ?int $paymentMethodId = null): ?Account
    {
        // If account ID provided, use it
        if ($accountId) {
            return Account::find($accountId);
        }

        // Try to resolve from payment method
        if ($paymentMethodId) {
            $paymentMethod = PaymentMethod::find($paymentMethodId);
            if ($paymentMethod?->account_id) {
                return Account::find($paymentMethod->account_id);
            }
        }

        // Fall back to default account
        $defaultAccountId = Setting::defaultAccountId();

        return Account::find($defaultAccountId);
    }

    /**
     * Get all active payment methods for selection
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getActivePaymentMethods(): \Illuminate\Database\Eloquent\Collection
    {
        return PaymentMethod::where('status', 1)
            ->with('account')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get all active categories for a transaction type
     *
     * @param int $type 1=income, 2=expense
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getCategoriesForType(int $type): \Illuminate\Database\Eloquent\Collection
    {
        return Category::where('type', $type)
            ->where('status', 1)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get transaction summary/statistics
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @param int|null $accountId
     *
     * @return array
     */
    public static function getSummary(?string $startDate = null, ?string $endDate = null, ?int $accountId = null): array
    {
        $query = Transaction::query();

        if ($startDate) {
            $query->whereDate('date', '>=', Carbon::parse($startDate));
        }

        if ($endDate) {
            $query->whereDate('date', '<=', Carbon::parse($endDate));
        }

        if ($accountId) {
            $query->where('account_id', $accountId);
        }

        $transactions = $query->get();

        return [
            'total_income' => $transactions->where('type', 1)->sum('amount'),
            'total_expense' => $transactions->where('type', 2)->sum('amount'),
            'net' => $transactions->where('type', 1)->sum('amount') - $transactions->where('type', 2)->sum('amount'),
            'count' => $transactions->count(),
            'by_category' => $transactions->groupBy('category_id')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'amount' => $group->sum('amount'),
                ];
            }),
            'by_payment_method' => $transactions->groupBy('payment_method')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'amount' => $group->sum('amount'),
                ];
            }),
        ];
    }

    /**
     * Get default category ID based on transaction type
    /**
     * Get default category ID based on transaction type
     *
     * @param int $type
     *
     * @return int
     */
    protected static function getDefaultCategoryIdForType(int $type): int
    {
        return match ($type) {
            1 => SettingsService::defaultSalesCategoryId(), // Income
            2 => SettingsService::defaultExpenseCategoryId(), // Expense
            default => SettingsService::defaultSalesCategoryId(), // Fallback
        };
    }

    /**
     * Normalize transaction type to integer (1=income, 2=expense)
     *
     * @param int|string $type
     *
     * @return int
     */
    protected static function normalizeTransactionType(int|string $type): int
    {
        if (is_string($type)) {
            return match (strtolower($type)) {
                'income' => 1,
                '1' => 1,
                'expense' => 2,
                '2' => 2,
                default => (int) $type,
            };
        }

        return (int) $type;
    }

    /**
     * Validate transaction data before create/update
     *
     * @param array $data
     *
     * @throws \Exception
     */
    protected static function validateTransactionData(array $data): void
    {
        // Validate amount
        if (empty($data['amount']) || (float) $data['amount'] <= 0) {
            throw new \Exception('Transaction amount must be greater than 0');
        }

        // Validate type
        if (empty($data['type']) || !in_array((int) $data['type'], [1, 2])) {
            throw new \Exception('Transaction type must be 1 (income) or 2 (expense)');
        }

        // Validate account exists
        if (!Account::find($data['account_id'])) {
            throw new \Exception("Account #{$data['account_id']} not found");
        }

        // Validate payment method exists and is active
        $paymentMethod = PaymentMethod::find($data['payment_method']);
        if (!$paymentMethod || $paymentMethod->status !== 1) {
            throw new \Exception("Payment method #{$data['payment_method']} not found or inactive");
        }

        // Validate category if provided
        if (!empty($data['category_id']) && !Category::find($data['category_id'])) {
            throw new \Exception("Category #{$data['category_id']} not found");
        }
    }
}
