<?php

namespace ArtflowStudio\AccountFlow\Services;

use ArtflowStudio\AccountFlow\Enums\TransactionType;
use ArtflowStudio\AccountFlow\Events\TransactionCreated;
use ArtflowStudio\AccountFlow\Events\TransactionDeleted;
use ArtflowStudio\AccountFlow\Events\TransactionReversed;
use ArtflowStudio\AccountFlow\Events\TransactionUpdated;
use ArtflowStudio\AccountFlow\Exceptions\InvalidTransactionException;
use ArtflowStudio\AccountFlow\Exceptions\RecordNotFoundException;
use ArtflowStudio\AccountFlow\Models\Account;
use ArtflowStudio\AccountFlow\Models\Category;
use ArtflowStudio\AccountFlow\Models\PaymentMethod;
use ArtflowStudio\AccountFlow\Models\Setting;
use ArtflowStudio\AccountFlow\Models\Transaction;
use ArtflowStudio\AccountFlow\Support\BalanceUpdater;
use ArtflowStudio\AccountFlow\Support\UniqueId;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Creating, changing and reversing ledger entries.
 *
 * `create()` is the only method that inserts — `income()`, `expense()` and
 * `createBatch()` all route through it, so validation, balance handling and
 * events cannot be bypassed.
 *
 * @example
 * AccountFlow::transactions()->income(1500, 'Invoice #221 payment');
 * AccountFlow::transactions()->expense(250, 'Office supplies');
 * AccountFlow::transactions()->create(['type' => TransactionType::Income, 'amount' => 1500]);
 */
class TransactionService
{
    public function __construct(
        private readonly BalanceUpdater $balances = new BalanceUpdater,
    ) {}

    /**
     * Record an income transaction.
     *
     * @param array<string,mixed> $attributes Anything create() accepts.
     */
    public function income(float $amount, ?string $description = null, array $attributes = []): Transaction
    {
        return $this->create($attributes + [
            'type' => TransactionType::Income,
            'amount' => $amount,
            'description' => $description,
        ]);
    }

    /**
     * Record an expense transaction.
     *
     * @param array<string,mixed> $attributes Anything create() accepts.
     */
    public function expense(float $amount, ?string $description = null, array $attributes = []): Transaction
    {
        return $this->create($attributes + [
            'type' => TransactionType::Expense,
            'amount' => $amount,
            'description' => $description,
        ]);
    }

    /**
     * Alias kept for 0.2.x callers.
     *
     * @param array<string,mixed> $data
     */
    public function createIncome(array $data): Transaction
    {
        return $this->create($data + ['type' => TransactionType::Income]);
    }

    /**
     * Alias kept for 0.2.x callers.
     *
     * @param array<string,mixed> $data
     */
    public function createExpense(array $data): Transaction
    {
        return $this->create($data + ['type' => TransactionType::Expense]);
    }

    /**
     * The single write path.
     *
     * Everything but `amount` and `type` is optional: the account resolves from
     * the payment method, and the category and payment method fall back to the
     * configured defaults.
     *
     * @param  array{
     *     amount: float|int,
     *     type: TransactionType|int|string,
     *     payment_method?: int|null,
     *     account_id?: int|null,
     *     category_id?: int|null,
     *     date?: string|DateTimeInterface|null,
     *     description?: string|null,
     *     user_id?: int|null,
     *     invoice_id?: int|null
     * }  $data
     *
     * @throws InvalidTransactionException
     * @throws RecordNotFoundException
     */
    public function create(array $data): Transaction
    {
        $type = TransactionType::tryParse($data['type'] ?? null)
            ?? throw InvalidTransactionException::unknownType($data['type'] ?? null);

        $amount = (float) ($data['amount'] ?? 0);

        if ($amount <= 0) {
            throw InvalidTransactionException::amountNotPositive($data['amount'] ?? null);
        }

        return DB::transaction(function () use ($data, $type, $amount): Transaction {
            $paymentMethodId = $data['payment_method'] ?? Setting::defaultPaymentMethodId();
            $accountId = $data['account_id'] ?? null;

            if (empty($accountId)) {
                $accountId = PaymentMethod::find($paymentMethodId)?->account_id
                    ?? Setting::defaultAccountId();
            }

            $attributes = [
                'unique_id' => UniqueId::for(Transaction::class),
                'amount' => $amount,
                'type' => $type->value,
                'account_id' => (int) $accountId,
                'payment_method' => $paymentMethodId !== null ? (int) $paymentMethodId : null,
                'category_id' => (int) ($data['category_id'] ?? $this->defaultCategoryId($type)),
                'date' => isset($data['date']) ? Carbon::parse($data['date']) : Carbon::now(),
                'description' => $data['description'] ?? null,
                'invoice_id' => $data['invoice_id'] ?? null,
                // The column is `added_by`. 0.2.x wrote `user_id` on the update
                // path, where Eloquent silently discarded it.
                'added_by' => $data['user_id'] ?? $data['added_by'] ?? auth()->id(),
            ];

            $this->validate($attributes);

            $transaction = Transaction::create($attributes);

            $this->balances->apply((int) $transaction->account_id, $amount, $type);

            TransactionCreated::dispatch($transaction);

            return $transaction;
        });
    }

    /**
     * Create many transactions atomically — all succeed or none do.
     *
     * @param array<int,array<string,mixed>> $transactions
     *
     * @return Collection<int,Transaction>
     */
    public function createBatch(array $transactions): Collection
    {
        return DB::transaction(function () use ($transactions): Collection {
            $created = new Collection;

            foreach ($transactions as $data) {
                $created->push($this->create($data));
            }

            return $created;
        });
    }

    /**
     * Update a transaction, re-basing the account balance when the amount,
     * type or account changes.
     *
     * @param array<string,mixed> $data
     */
    public function update(Transaction $transaction, array $data): Transaction
    {
        return DB::transaction(function () use ($transaction, $data): Transaction {
            $before = $transaction->getOriginal();

            $originalType = $transaction->transactionType() ?? TransactionType::Income;
            $originalAmount = (float) $transaction->amount;
            $originalAccountId = (int) $transaction->account_id;

            $changes = [];

            if (array_key_exists('amount', $data)) {
                $amount = (float) $data['amount'];

                if ($amount <= 0) {
                    throw InvalidTransactionException::amountNotPositive($data['amount']);
                }

                $changes['amount'] = $amount;
            }

            if (array_key_exists('type', $data)) {
                $changes['type'] = (TransactionType::tryParse($data['type'])
                    ?? throw InvalidTransactionException::unknownType($data['type']))->value;
            }

            if (array_key_exists('payment_method', $data)) {
                $changes['payment_method'] = (int) $data['payment_method'];

                if (! array_key_exists('account_id', $data)) {
                    $changes['account_id'] = PaymentMethod::find($data['payment_method'])?->account_id
                        ?? $transaction->account_id;
                }
            }

            foreach (['account_id', 'category_id', 'invoice_id'] as $key) {
                if (array_key_exists($key, $data)) {
                    $changes[$key] = $data[$key] !== null ? (int) $data[$key] : null;
                }
            }

            if (array_key_exists('date', $data)) {
                $changes['date'] = Carbon::parse($data['date']);
            }

            if (array_key_exists('description', $data)) {
                $changes['description'] = $data['description'];
            }

            // `user_id` is accepted for convenience, but the column is `added_by`.
            if (array_key_exists('user_id', $data)) {
                $changes['added_by'] = (int) $data['user_id'];
            }

            if ($changes === []) {
                return $transaction;
            }

            $this->validate(array_merge($transaction->getAttributes(), $changes));

            $newType = TransactionType::from($changes['type'] ?? $originalType->value);
            $newAmount = (float) ($changes['amount'] ?? $originalAmount);
            $newAccountId = (int) ($changes['account_id'] ?? $originalAccountId);

            $balanceAffected = $newAmount !== $originalAmount
                || $newType !== $originalType
                || $newAccountId !== $originalAccountId;

            if ($balanceAffected) {
                $this->balances->reverse($originalAccountId, $originalAmount, $originalType);
                $this->balances->apply($newAccountId, $newAmount, $newType);
            }

            $transaction->update($changes);

            TransactionUpdated::dispatch($transaction, $before);

            return $transaction->fresh();
        });
    }

    /**
     * Delete a transaction and undo its effect on the account balance.
     */
    public function delete(Transaction $transaction): bool
    {
        return DB::transaction(function () use ($transaction): bool {
            $type = $transaction->transactionType();

            if ($type !== null) {
                $this->balances->reverse(
                    (int) $transaction->account_id,
                    (float) $transaction->amount,
                    $type,
                );
            }

            $deleted = (bool) $transaction->delete();

            if ($deleted) {
                TransactionDeleted::dispatch($transaction);
            }

            return $deleted;
        });
    }

    /**
     * Reverse a transaction with a contra entry.
     *
     * The reversal keeps the *same* type and carries a negative amount, linked
     * back to the original, so profit & loss nets to zero. 0.2.x created an
     * opposite-type transaction instead, which made a reversed sale appear as
     * an expense and distorted every report.
     *
     * @throws InvalidTransactionException
     */
    public function reverse(Transaction $transaction, ?string $reason = null): Transaction
    {
        if ($transaction->isReversed()) {
            throw InvalidTransactionException::alreadyReversed((int) $transaction->id);
        }

        if ($transaction->isReversal()) {
            throw InvalidTransactionException::cannotReverseAReversal((int) $transaction->id);
        }

        return DB::transaction(function () use ($transaction, $reason): Transaction {
            $type = $transaction->transactionType() ?? TransactionType::Income;
            $amount = (float) $transaction->amount;

            $reversal = Transaction::create([
                'unique_id' => UniqueId::for(Transaction::class),
                'amount' => -1 * $amount,
                'type' => $type->value,
                'account_id' => $transaction->account_id,
                'category_id' => $transaction->category_id,
                'payment_method' => $transaction->payment_method,
                'date' => Carbon::now(),
                'description' => 'REVERSAL: '.($reason ?? $transaction->description ?? "transaction #{$transaction->id}"),
                'added_by' => auth()->id(),
                'reversal_of_id' => $transaction->id,
            ]);

            // Undo the original's effect on the balance.
            $this->balances->reverse((int) $transaction->account_id, $amount, $type);

            $transaction->forceFill(['reversed_at' => Carbon::now()])->save();

            TransactionReversed::dispatch($transaction, $reversal, $reason);

            return $reversal;
        });
    }

    /**
     * Resolve the account for a transaction: explicit id, then the payment
     * method's account, then the configured default.
     */
    public function resolveAccount(?int $accountId = null, ?int $paymentMethodId = null): ?Account
    {
        if ($accountId !== null) {
            /** @var Account|null $account */
            $account = Account::query()->find($accountId);

            return $account;
        }

        if ($paymentMethodId !== null) {
            $account = PaymentMethod::find($paymentMethodId)?->account;

            if ($account !== null) {
                return $account;
            }
        }

        /** @var Account|null $fallback */
        $fallback = Account::query()->find(Setting::defaultAccountId());

        return $fallback;
    }

    /**
     * @return Collection<int,PaymentMethod>
     */
    public function getActivePaymentMethods(): Collection
    {
        return PaymentMethod::query()->active()->with('account')->orderBy('name')->get();
    }

    /**
     * @return Collection<int,Category>
     */
    public function getCategoriesForType(TransactionType|int $type): Collection
    {
        $value = $type instanceof TransactionType ? $type->value : $type;

        return Category::query()->where('type', $value)->active()->orderBy('name')->get();
    }

    /**
     * Totals for a period, aggregated in SQL.
     *
     * 0.2.x loaded every matching row into memory and summed in PHP, which does
     * not survive a real ledger.
     *
     * @return array{total_income: float, total_expense: float, net: float, count: int}
     */
    public function getSummary(?string $startDate = null, ?string $endDate = null, ?int $accountId = null): array
    {
        $totals = Transaction::query()
            // Transfer legs move cash between the user's own accounts, so they
            // are not money in or money out. ReportService excludes them too.
            ->excludingTransfers()
            ->between($startDate, $endDate)
            ->when($accountId, fn ($query) => $query->where('account_id', $accountId))
            ->selectRaw('COALESCE(SUM(CASE WHEN type = ? THEN amount ELSE 0 END), 0) as total_income', [TransactionType::Income->value])
            ->selectRaw('COALESCE(SUM(CASE WHEN type = ? THEN amount ELSE 0 END), 0) as total_expense', [TransactionType::Expense->value])
            ->selectRaw('COUNT(*) as row_count')
            ->first();

        $income = (float) ($totals->total_income ?? 0);
        $expense = (float) ($totals->total_expense ?? 0);

        return [
            'total_income' => $income,
            'total_expense' => $expense,
            'net' => $income - $expense,
            'count' => (int) ($totals->row_count ?? 0),
        ];
    }

    /**
     * @param array<string,mixed> $attributes
     *
     * @throws InvalidTransactionException
     * @throws RecordNotFoundException
     */
    private function validate(array $attributes): void
    {
        // A reversal legitimately carries a negative amount; only zero is invalid.
        if (! isset($attributes['amount']) || (float) $attributes['amount'] === 0.0) {
            throw InvalidTransactionException::amountNotPositive($attributes['amount'] ?? null);
        }

        if (TransactionType::tryParse($attributes['type'] ?? null) === null) {
            throw InvalidTransactionException::unknownType($attributes['type'] ?? null);
        }

        if (! Account::whereKey($attributes['account_id'] ?? null)->exists()) {
            throw RecordNotFoundException::account($attributes['account_id'] ?? 'null');
        }

        $paymentMethodId = $attributes['payment_method'] ?? null;

        if ($paymentMethodId !== null) {
            $method = PaymentMethod::find($paymentMethodId);

            if ($method === null || ! $method->isActive()) {
                throw RecordNotFoundException::paymentMethod($paymentMethodId);
            }
        }

        $categoryId = $attributes['category_id'] ?? null;

        if (! empty($categoryId) && ! Category::whereKey($categoryId)->exists()) {
            throw RecordNotFoundException::category($categoryId);
        }
    }

    private function defaultCategoryId(TransactionType $type): int
    {
        return $type === TransactionType::Income
            ? Setting::defaultSalesCategoryId()
            : Setting::defaultExpenseCategoryId();
    }
}
