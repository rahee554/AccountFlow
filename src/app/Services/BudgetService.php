<?php

namespace ArtflowStudio\AccountFlow\App\Services;

use App\Models\AccountFlow\Budget;
use App\Models\AccountFlow\Category;
use App\Models\AccountFlow\Account;
use App\Models\AccountFlow\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

/**
 * BudgetService - Budget Management
 *
 * Handles all budget operations including:
 * - Creating and updating budgets
 * - Budget tracking and spending analysis
 * - Variance reporting (actual vs budgeted)
 * - Budget alerts and threshold management
 *
 * @example
 * // Create budget
 * $budget = BudgetService::create([
 *     'account_id' => 1,
 *     'category_id' => 5,
 *     'amount' => 5000,
 *     'period' => 'monthly'
 * ]);
 *
 * // Get budget with spending analysis
 * $analysis = BudgetService::analyze($budget->id);
 */
class BudgetService
{
    /**
     * Create a new budget
     *
     * @param array{
     *     account_id: int,
     *     category_id: int,
     *     amount: float,
     *     period?: string, // daily, weekly, monthly, yearly
     *     start_date?: string,
     *     end_date?: string,
     *     alert_threshold?: int, // percentage 50-100
     *     notes?: string,
     *     status?: int // 1=active, 2=inactive
     * } $data
     *
     * @return Budget
     *
     * @throws \Exception
     */
    public static function create(array $data): Budget
    {
        return DB::transaction(function () use ($data) {
            // Validate required fields
            if (empty($data['account_id'])) {
                throw new \Exception('Account ID is required');
            }

            if (empty($data['category_id'])) {
                throw new \Exception('Category ID is required');
            }

            if (empty($data['amount']) || (float) $data['amount'] <= 0) {
                throw new \Exception('Budget amount must be greater than 0');
            }

            // Validate account exists
            $account = Account::find($data['account_id']);
            if (!$account) {
                throw new \Exception("Account #{$data['account_id']} not found");
            }

            // Validate category exists
            $category = Category::find($data['category_id']);
            if (!$category) {
                throw new \Exception("Category #{$data['category_id']} not found");
            }

            // Prepare budget data
            $budgetData = [
                'account_id' => (int) $data['account_id'],
                'category_id' => (int) $data['category_id'],
                'amount' => (float) $data['amount'],
                'period' => $data['period'] ?? 'monthly',
                'start_date' => $data['start_date'] ? Carbon::parse($data['start_date']) : Carbon::now(),
                'end_date' => $data['end_date'] ? Carbon::parse($data['end_date']) : null,
                'alert_threshold' => (int) ($data['alert_threshold'] ?? 80), // 80% by default
                'notes' => $data['notes'] ?? null,
                'status' => (int) ($data['status'] ?? 1),
                'created_by' => auth()->id(),
            ];

            return Budget::create($budgetData);
        });
    }

    /**
     * Update a budget
     *
     * @param Budget $budget
     * @param array $data
     *
     * @return Budget
     */
    public static function update(Budget $budget, array $data): Budget
    {
        return DB::transaction(function () use ($budget, $data) {
            $updateData = [];

            if (isset($data['amount'])) {
                if ((float) $data['amount'] <= 0) {
                    throw new \Exception('Budget amount must be greater than 0');
                }
                $updateData['amount'] = (float) $data['amount'];
            }

            if (isset($data['period'])) {
                $updateData['period'] = $data['period'];
            }

            if (isset($data['alert_threshold'])) {
                $threshold = (int) $data['alert_threshold'];
                if ($threshold < 0 || $threshold > 100) {
                    throw new \Exception('Alert threshold must be between 0 and 100');
                }
                $updateData['alert_threshold'] = $threshold;
            }

            if (isset($data['notes'])) {
                $updateData['notes'] = $data['notes'];
            }

            if (isset($data['status'])) {
                $updateData['status'] = (int) $data['status'];
            }

            if (!empty($updateData)) {
                $budget->update($updateData);
            }

            return $budget->fresh();
        });
    }

    /**
     * Get active budgets for an account
     *
     * @param int $accountId
     *
     * @return Collection
     */
    public static function getActive(int $accountId): Collection
    {
        return Budget::where('account_id', $accountId)
            ->where('status', 1)
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Get all budgets for an account
     *
     * @param int $accountId
     *
     * @return Collection
     */
    public static function getAll(int $accountId): Collection
    {
        return Budget::where('account_id', $accountId)
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Analyze budget spending
     *
     * Returns comprehensive spending analysis with variance
     *
     * @param int $budgetId
     *
     * @return array
     */
    public static function analyze(int $budgetId): array
    {
        $budget = Budget::find($budgetId);

        if (!$budget) {
            throw new \Exception("Budget #{$budgetId} not found");
        }

        // Get spending for the budget period
        $spent = Transaction::where('account_id', $budget->account_id)
            ->where('category_id', $budget->category_id)
            ->where('type', 2) // Expenses only
            ->whereBetween('date', [
                $budget->start_date,
                $budget->end_date ?? Carbon::now(),
            ])
            ->sum('amount');

        $spent = (float) $spent;
        $budgeted = (float) $budget->amount;
        $remaining = $budgeted - $spent;
        $percentageUsed = $budgeted > 0 ? ($spent / $budgeted) * 100 : 0;
        $variance = $spent - $budgeted; // Negative = under budget, Positive = over budget
        $isAlert = $percentageUsed >= $budget->alert_threshold;

        return [
            'budget_id' => $budget->id,
            'budgeted' => $budgeted,
            'spent' => $spent,
            'remaining' => $remaining,
            'percentage_used' => round($percentageUsed, 2),
            'variance' => $variance,
            'is_over_budget' => $spent > $budgeted,
            'is_alert' => $isAlert,
            'alert_threshold' => $budget->alert_threshold,
            'status' => $budget->status === 1 ? 'active' : 'inactive',
            'period' => $budget->period,
            'category_name' => $budget->category?->name,
        ];
    }

    /**
     * Get spending transactions for a budget
     *
     * @param int $budgetId
     * @param int $limit
     *
     * @return Collection
     */
    public static function getTransactions(int $budgetId, int $limit = 50): Collection
    {
        $budget = Budget::find($budgetId);

        if (!$budget) {
            throw new \Exception("Budget #{$budgetId} not found");
        }

        return Transaction::where('account_id', $budget->account_id)
            ->where('category_id', $budget->category_id)
            ->where('type', 2) // Expenses
            ->whereBetween('date', [
                $budget->start_date,
                $budget->end_date ?? Carbon::now(),
            ])
            ->latest('date')
            ->limit($limit)
            ->get();
    }

    /**
     * Get budgets that are over threshold
     *
     * @param int $accountId
     *
     * @return array
     */
    public static function getAlertsForAccount(int $accountId): array
    {
        $budgets = self::getActive($accountId);
        $alerts = [];

        foreach ($budgets as $budget) {
            $analysis = self::analyze($budget->id);

            if ($analysis['is_alert']) {
                $alerts[] = $analysis;
            }
        }

        return $alerts;
    }

    /**
     * Activate a budget
     *
     * @param Budget $budget
     *
     * @return Budget
     */
    public static function activate(Budget $budget): Budget
    {
        return self::update($budget, ['status' => 1]);
    }

    /**
     * Deactivate a budget
     *
     * @param Budget $budget
     *
     * @return Budget
     */
    public static function deactivate(Budget $budget): Budget
    {
        return self::update($budget, ['status' => 2]);
    }

    /**
     * Delete a budget
     *
     * @param Budget $budget
     *
     * @return bool
     */
    public static function delete(Budget $budget): bool
    {
        return DB::transaction(function () use ($budget) {
            return $budget->delete();
        });
    }
}
