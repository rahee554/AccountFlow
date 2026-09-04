<?php

namespace ArtflowStudio\AccountFlow\Services;

use ArtflowStudio\AccountFlow\Enums\CategoryType;
use ArtflowStudio\AccountFlow\Enums\TransactionType;
use ArtflowStudio\AccountFlow\Exceptions\AccountFlowException;
use ArtflowStudio\AccountFlow\Models\Account;
use ArtflowStudio\AccountFlow\Models\Category;
use ArtflowStudio\AccountFlow\Models\PaymentMethod;
use ArtflowStudio\AccountFlow\Models\Setting;
use ArtflowStudio\AccountFlow\Models\Transaction;
use ArtflowStudio\AccountFlow\Models\Transfer;

/**
 * The plain-language front door.
 *
 * Everything a normal user does is "money came in" or "money went out". They
 * do not know what a ledger is, and should never have to look up an account id
 * or decide which category id to pass. This service takes names, creates what
 * is missing, and hands the work to the services that do the accounting.
 *
 * The precise services are still there for application code that needs them —
 * this is a shortcut, not a replacement.
 *
 * @example
 * AccountFlow::money()->received(1500, 'Sale to Ali');
 * AccountFlow::money()->spent(250, 'Fuel', 'Transport');
 * AccountFlow::money()->spent(900, 'Office rent', 'Rentals', account: 'Bank Account');
 * AccountFlow::money()->moved(500, from: 'Cash Account', to: 'Bank Account');
 * AccountFlow::money()->balance('Cash Account');
 */
class MoneyService
{
    public function __construct(
        private readonly TransactionService $transactions = new TransactionService,
        private readonly TransferService $transfers = new TransferService,
    ) {}

    /**
     * Money came in.
     *
     * Only the amount is required. The category is created if it does not
     * exist; the account and payment method fall back to your defaults.
     *
     * @param string|int|null $category Name or id — a name that does not exist is created
     * @param string|int|null $account Name or id
     * @param string|int|null $paymentMethod Name or id
     */
    public function received(
        float $amount,
        ?string $description = null,
        string|int|null $category = null,
        string|int|null $account = null,
        string|int|null $paymentMethod = null,
        mixed $date = null,
    ): Transaction {
        return $this->record(TransactionType::Income, $amount, $description, $category, $account, $paymentMethod, $date);
    }

    /**
     * Money went out.
     *
     * @param string|int|null $category Name or id — a name that does not exist is created
     * @param string|int|null $account Name or id
     * @param string|int|null $paymentMethod Name or id
     */
    public function spent(
        float $amount,
        ?string $description = null,
        string|int|null $category = null,
        string|int|null $account = null,
        string|int|null $paymentMethod = null,
        mixed $date = null,
    ): Transaction {
        return $this->record(TransactionType::Expense, $amount, $description, $category, $account, $paymentMethod, $date);
    }

    /**
     * Money moved between two of your own accounts.
     *
     * The user thinks "I moved 500 from Cash to Bank". Underneath this is a
     * transfer with a ledger entry on each side, kept out of profit & loss.
     *
     * @param string|int $from Name or id
     * @param string|int $to Name or id
     *
     * @throws AccountFlowException
     */
    public function moved(
        float $amount,
        string|int $from,
        string|int $to,
        ?string $description = null,
        mixed $date = null,
    ): Transfer {
        return $this->transfers->create([
            'amount' => $amount,
            'from_account' => $this->accountId($from, required: true),
            'to_account' => $this->accountId($to, required: true),
            'description' => $description,
            'date' => $date,
        ]);
    }

    /**
     * What is in an account right now. Omit the name for every account.
     *
     * @return float|array<string,float>
     */
    public function balance(string|int|null $account = null): float|array
    {
        if ($account !== null) {
            $id = $this->accountId($account, required: true);

            return round((float) Account::whereKey($id)->value('balance'), 2);
        }

        return Account::query()
            ->active()
            ->orderBy('name')
            ->pluck('balance', 'name')
            ->map(fn ($balance): float => round((float) $balance, 2))
            ->all();
    }

    /**
     * Total money held across active accounts.
     */
    public function total(): float
    {
        return round((float) Account::query()->active()->sum('balance'), 2);
    }

    /**
     * A plain summary for a period: in, out, and what is left.
     *
     * @return array{received: float, spent: float, difference: float, entries: int}
     */
    public function summary(mixed $from = null, mixed $to = null): array
    {
        $summary = $this->transactions->getSummary(
            $from !== null ? (string) $from : null,
            $to !== null ? (string) $to : null,
        );

        return [
            'received' => $summary['total_income'],
            'spent' => $summary['total_expense'],
            'difference' => $summary['net'],
            'entries' => $summary['count'],
        ];
    }

    /**
     * Undo an entry — the honest way, leaving a trail.
     *
     * Reverses rather than deletes, so the original stays visible and the
     * books still explain themselves.
     */
    public function undo(Transaction $transaction, ?string $reason = null): Transaction
    {
        return $this->transactions->reverse($transaction, $reason);
    }

    /**
     * @throws AccountFlowException
     */
    private function record(
        TransactionType $type,
        float $amount,
        ?string $description,
        string|int|null $category,
        string|int|null $account,
        string|int|null $paymentMethod,
        mixed $date,
    ): Transaction {
        return $this->transactions->create([
            'type' => $type,
            'amount' => $amount,
            'description' => $description,
            'category_id' => $this->categoryId($category, $type),
            'account_id' => $this->accountId($account),
            'payment_method' => $this->paymentMethodId($paymentMethod),
            'date' => $date,
        ]);
    }

    /**
     * Resolve an account by id or name.
     *
     * @throws AccountFlowException
     */
    private function accountId(string|int|null $account, bool $required = false): ?int
    {
        if ($account === null) {
            return null;
        }

        if (is_int($account) || ctype_digit((string) $account)) {
            return (int) $account;
        }

        $id = Account::query()->where('name', $account)->value('id');

        if ($id === null && $required) {
            $known = Account::query()->orderBy('name')->pluck('name')->implode(', ');

            throw new AccountFlowException(
                "There is no account called [{$account}].".($known !== '' ? " Try one of: {$known}." : ''),
            );
        }

        return $id !== null ? (int) $id : null;
    }

    /**
     * Resolve a payment method by id or name; unknown names are ignored so a
     * typo never blocks recording money.
     */
    private function paymentMethodId(string|int|null $method): ?int
    {
        if ($method === null) {
            return null;
        }

        if (is_int($method) || ctype_digit((string) $method)) {
            return (int) $method;
        }

        $id = PaymentMethod::query()->where('name', $method)->value('id');

        return $id !== null ? (int) $id : null;
    }

    /**
     * Resolve a category by id or name, creating it when the name is new.
     *
     * Making the user pre-create a category before they can record a expense
     * is exactly the kind of friction this package exists to remove. Set
     * `accountflow.auto_create_categories` to false to require existing ones.
     */
    private function categoryId(string|int|null $category, TransactionType $type): ?int
    {
        if ($category === null) {
            return null;   // TransactionService falls back to the configured default
        }

        if (is_int($category) || ctype_digit((string) $category)) {
            return (int) $category;
        }

        $categoryType = $type === TransactionType::Income
            ? CategoryType::Income
            : CategoryType::Expense;

        $existing = Category::query()
            ->where('name', $category)
            ->where('type', $categoryType->value)
            ->value('id');

        if ($existing !== null) {
            return (int) $existing;
        }

        if (! config('accountflow.auto_create_categories', true)) {
            return $type === TransactionType::Income
                ? Setting::defaultSalesCategoryId()
                : Setting::defaultExpenseCategoryId();
        }

        // New categories hang off the default parent for their direction, so
        // the tree stays tidy without the user thinking about hierarchy.
        $parentId = Category::query()
            ->where('type', $categoryType->value)
            ->whereNull('parent_id')
            ->value('id');

        return (int) Category::create([
            'name' => (string) $category,
            'type' => $categoryType->value,
            'parent_id' => $parentId,
            'privacy' => 2,
            'status' => 1,
        ])->id;
    }
}
