<?php

namespace ArtflowStudio\AccountFlow\Services;

use ArtflowStudio\AccountFlow\Enums\TransactionType;
use ArtflowStudio\AccountFlow\Exceptions\AccountFlowException;
use ArtflowStudio\AccountFlow\Exceptions\RecordNotFoundException;
use ArtflowStudio\AccountFlow\Models\Account;
use ArtflowStudio\AccountFlow\Models\Budget;
use ArtflowStudio\AccountFlow\Models\Category;
use ArtflowStudio\AccountFlow\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Budgets and spending analysis.
 *
 * Rewritten against the actual `ac_budgets` table. The 0.2.x version was
 * written for a schema that does not exist: it read and wrote `start_date`,
 * `end_date`, `alert_threshold` and `status`, none of which are columns. That
 * made `analyze()` run `whereBetween('date', [null, now()])`, compare a
 * percentage against `null` (so every budget "alerted"), and report every
 * budget as inactive. It went unnoticed only because nothing ever called this
 * service.
 *
 * The real columns are: account_id, category_id, amount, period ('monthly' |
 * 'yearly'), year, month, description, created_by. A budget's window is derived
 * from period + year + month, which is what the UI collects.
 *
 * @example
 * $budget   = Accountflow::budgets()->create([
 *     'account_id' => 1, 'category_id' => 5, 'amount' => 5000,
 *     'period' => 'monthly', 'year' => 2026, 'month' => 9,
 * ]);
 * $analysis = Accountflow::budgets()->analyze($budget->id);
 */
class BudgetService
{
    /**
     * Percentage of a budget that counts as an alert.
     */
    public const DEFAULT_ALERT_THRESHOLD = 80;

    /**
     * @param  array{
     *     account_id?: int|null,
     *     category_id?: int|null,
     *     amount: float|int,
     *     period?: string,
     *     year?: int|null,
     *     month?: int|null,
     *     description?: string|null,
     *     created_by?: int|null
     * }  $data
     *
     * @throws AccountFlowException
     * @throws RecordNotFoundException
     */
    public function create(array $data): Budget
    {
        $amount = (float) ($data['amount'] ?? 0);

        if ($amount <= 0) {
            throw new AccountFlowException('Budget amount must be greater than 0.');
        }

        $period = $this->normalizePeriod($data['period'] ?? 'monthly');

        if (! empty($data['account_id']) && ! Account::whereKey($data['account_id'])->exists()) {
            throw RecordNotFoundException::account($data['account_id']);
        }

        if (! empty($data['category_id']) && ! Category::whereKey($data['category_id'])->exists()) {
            throw RecordNotFoundException::category($data['category_id']);
        }

        return Budget::create([
            'account_id' => $data['account_id'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'amount' => $amount,
            'period' => $period,
            'year' => $data['year'] ?? Carbon::now()->year,
            'month' => $period === 'monthly' ? ($data['month'] ?? Carbon::now()->month) : null,
            'description' => $data['description'] ?? null,
            'created_by' => $data['created_by'] ?? auth()->id(),
        ]);
    }

    /**
     * @param array<string,mixed> $data
     *
     * @throws AccountFlowException
     */
    public function update(Budget $budget, array $data): Budget
    {
        $changes = [];

        if (array_key_exists('amount', $data)) {
            $amount = (float) $data['amount'];

            if ($amount <= 0) {
                throw new AccountFlowException('Budget amount must be greater than 0.');
            }

            $changes['amount'] = $amount;
        }

        if (array_key_exists('period', $data)) {
            $changes['period'] = $this->normalizePeriod($data['period']);
        }

        foreach (['account_id', 'category_id', 'year', 'month'] as $key) {
            if (array_key_exists($key, $data)) {
                $changes[$key] = $data[$key] !== null ? (int) $data[$key] : null;
            }
        }

        if (array_key_exists('description', $data)) {
            $changes['description'] = $data['description'];
        }

        if ($changes !== []) {
            $budget->update($changes);
        }

        return $budget->fresh();
    }

    /**
     * Budgets covering the current period for an account.
     *
     * There is no `status` column, so "active" means the budget's own window
     * contains today.
     *
     * @return Collection<int,Budget>
     */
    public function getActive(int $accountId): Collection
    {
        $now = Carbon::now();

        return Budget::query()
            ->where('account_id', $accountId)
            ->where('year', $now->year)
            ->where(function ($query) use ($now) {
                $query->whereNull('month')->orWhere('month', $now->month);
            })
            ->with(['category', 'account'])
            ->get();
    }

    /**
     * @return Collection<int,Budget>
     */
    public function getAll(int $accountId): Collection
    {
        return Budget::query()
            ->where('account_id', $accountId)
            ->with(['category', 'account'])
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get();
    }

    /**
     * Budgeted versus actual for one budget.
     *
     * @return array<string,mixed>
     *
     * @throws RecordNotFoundException
     */
    public function analyze(int $budgetId, ?int $alertThreshold = null): array
    {
        $budget = Budget::with('category')->find($budgetId)
            ?? throw new RecordNotFoundException("Budget #{$budgetId} was not found.");

        $threshold = $alertThreshold ?? self::DEFAULT_ALERT_THRESHOLD;
        [$from, $to] = $this->window($budget);

        $spent = (float) Transaction::query()
            ->when($budget->account_id, fn ($q) => $q->where('account_id', $budget->account_id))
            ->when($budget->category_id, fn ($q) => $q->where('category_id', $budget->category_id))
            ->where('type', TransactionType::Expense->value)
            ->whereBetween('date', [$from, $to])
            ->sum('amount');

        $budgeted = (float) $budget->amount;
        $percentageUsed = $budgeted > 0 ? ($spent / $budgeted) * 100 : 0.0;

        return [
            'budget_id' => (int) $budget->id,
            'period' => $budget->period,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'budgeted' => $budgeted,
            'spent' => $spent,
            'remaining' => $budgeted - $spent,
            'percentage_used' => round($percentageUsed, 2),
            'variance' => $spent - $budgeted,
            'is_over_budget' => $spent > $budgeted,
            'is_alert' => $percentageUsed >= $threshold,
            'alert_threshold' => $threshold,
            'category_name' => $budget->category?->name,
        ];
    }

    /**
     * The expense transactions counted against a budget.
     *
     * @return Collection<int,Transaction>
     *
     * @throws RecordNotFoundException
     */
    public function getTransactions(int $budgetId, int $limit = 50): Collection
    {
        $budget = Budget::find($budgetId)
            ?? throw new RecordNotFoundException("Budget #{$budgetId} was not found.");

        [$from, $to] = $this->window($budget);

        return Transaction::query()
            ->when($budget->account_id, fn ($q) => $q->where('account_id', $budget->account_id))
            ->when($budget->category_id, fn ($q) => $q->where('category_id', $budget->category_id))
            ->where('type', TransactionType::Expense->value)
            ->whereBetween('date', [$from, $to])
            ->with(['category', 'paymentMethod'])
            ->orderByDesc('date')
            ->limit($limit)
            ->get();
    }

    /**
     * Every current budget for an account that has crossed its threshold.
     *
     * @return list<array<string,mixed>>
     */
    public function getAlertsForAccount(int $accountId, ?int $alertThreshold = null): array
    {
        $alerts = [];

        foreach ($this->getActive($accountId) as $budget) {
            $analysis = $this->analyze((int) $budget->id, $alertThreshold);

            if ($analysis['is_alert']) {
                $alerts[] = $analysis;
            }
        }

        return $alerts;
    }

    public function delete(Budget $budget): bool
    {
        return DB::transaction(fn (): bool => (bool) $budget->delete());
    }

    /**
     * The date window a budget covers, from its period, year and month.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function window(Budget $budget): array
    {
        $year = (int) ($budget->year ?: Carbon::now()->year);

        if ($budget->period === 'yearly' || $budget->month === null) {
            return [
                Carbon::create($year, 1, 1)->startOfDay(),
                Carbon::create($year, 12, 31)->endOfDay(),
            ];
        }

        $start = Carbon::create($year, (int) $budget->month, 1)->startOfDay();

        return [$start, $start->copy()->endOfMonth()->endOfDay()];
    }

    /**
     * @throws AccountFlowException
     */
    private function normalizePeriod(string $period): string
    {
        $period = strtolower(trim($period));

        if (! in_array($period, ['monthly', 'yearly'], true)) {
            throw new AccountFlowException(
                "Budget period must be 'monthly' or 'yearly', got [{$period}].",
            );
        }

        return $period;
    }
}
