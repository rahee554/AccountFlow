<?php

namespace ArtflowStudio\AccountFlow\App\Services;

use App\Models\AccountFlow\Transaction;
use App\Models\AccountFlow\Account;
use App\Models\AccountFlow\Category;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * ReportService - Financial Reporting
 *
 * Generates comprehensive financial reports including:
 * - Income/Expense analysis by period
 * - Cash flow reports
 * - Trial balance
 * - Profit & Loss statements
 * - Category-wise breakdowns
 *
 * @example
 * // Get income/expense report
 * $report = ReportService::incomeExpenseReport('2024-01-01', '2024-12-31');
 *
 * // Get profit and loss
 * $pl = ReportService::profitAndLoss('2024-01-01', '2024-12-31');
 */
class ReportService
{
    /**
     * Get income and expense report
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @param int|null $accountId
     *
     * @return array
     */
    public static function incomeExpenseReport(
        ?string $startDate = null,
        ?string $endDate = null,
        ?int $accountId = null
    ): array {
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

        $incomeByCategory = $transactions->where('type', 1)
            ->groupBy('category_id')
            ->map(function ($group) {
                return [
                    'category_id' => $group->first()->category_id,
                    'category_name' => $group->first()->category?->name,
                    'amount' => (float) $group->sum('amount'),
                    'count' => $group->count(),
                ];
            })
            ->values();

        $expenseByCategory = $transactions->where('type', 2)
            ->groupBy('category_id')
            ->map(function ($group) {
                return [
                    'category_id' => $group->first()->category_id,
                    'category_name' => $group->first()->category?->name,
                    'amount' => (float) $group->sum('amount'),
                    'count' => $group->count(),
                ];
            })
            ->values();

        $totalIncome = (float) $transactions->where('type', 1)->sum('amount');
        $totalExpense = (float) $transactions->where('type', 2)->sum('amount');
        $netIncome = $totalIncome - $totalExpense;

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'summary' => [
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'net_income' => $netIncome,
                'transaction_count' => $transactions->count(),
            ],
            'income_by_category' => $incomeByCategory->toArray(),
            'expense_by_category' => $expenseByCategory->toArray(),
            'total_income_amount' => $totalIncome,
            'total_expense_amount' => $totalExpense,
        ];
    }

    /**
     * Get profit and loss statement
     *
     * @param string|null $startDate
     * @param string|null $endDate
     *
     * @return array
     */
    public static function profitAndLoss(
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $report = self::incomeExpenseReport($startDate, $endDate);

        return [
            'period' => $report['period'],
            'revenue' => $report['summary']['total_income'],
            'expenses' => $report['summary']['total_expense'],
            'profit' => $report['summary']['net_income'],
            'profit_margin' => $report['summary']['total_income'] > 0
                ? round(($report['summary']['net_income'] / $report['summary']['total_income']) * 100, 2)
                : 0,
            'revenue_breakdown' => $report['income_by_category'],
            'expense_breakdown' => $report['expense_by_category'],
        ];
    }

    /**
     * Get cash flow report
     *
     * @param string|null $startDate
     * @param string|null $endDate
     *
     * @return array
     */
    public static function cashFlowReport(
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $query = Transaction::query();

        if ($startDate) {
            $query->whereDate('date', '>=', Carbon::parse($startDate));
        }

        if ($endDate) {
            $query->whereDate('date', '<=', Carbon::parse($endDate));
        }

        $transactions = $query->get();

        // Group by month
        $byMonth = [];
        foreach ($transactions as $transaction) {
            $month = $transaction->date->format('Y-m');

            if (!isset($byMonth[$month])) {
                $byMonth[$month] = [
                    'month' => $month,
                    'inflows' => 0,
                    'outflows' => 0,
                ];
            }

            if ($transaction->type === 1) {
                $byMonth[$month]['inflows'] += $transaction->amount;
            } else {
                $byMonth[$month]['outflows'] += $transaction->amount;
            }
        }

        // Calculate net cash flow
        foreach ($byMonth as &$month) {
            $month['inflows'] = (float) $month['inflows'];
            $month['outflows'] = (float) $month['outflows'];
            $month['net_cash_flow'] = (float) ($month['inflows'] - $month['outflows']);
        }

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'by_month' => array_values($byMonth),
            'total_inflows' => (float) $transactions->where('type', 1)->sum('amount'),
            'total_outflows' => (float) $transactions->where('type', 2)->sum('amount'),
        ];
    }

    /**
     * Get account balance report
     *
     * @return array
     */
    public static function balanceReport(): array
    {
        $accounts = Account::where('active', true)
            ->orderBy('name')
            ->get();

        $accountBalances = $accounts->map(function ($account) {
            return [
                'account_id' => $account->id,
                'account_name' => $account->name,
                'balance' => (float) $account->balance,
                'opening_balance' => (float) $account->opening_balance,
            ];
        })->toArray();

        $totalAssets = (float) $accounts->sum('balance');

        return [
            'accounts' => $accountBalances,
            'total_balance' => $totalAssets,
            'report_date' => Carbon::now()->format('Y-m-d'),
        ];
    }

    /**
     * Get transaction summary by payment method
     *
     * @param string|null $startDate
     * @param string|null $endDate
     *
     * @return array
     */
    public static function byPaymentMethod(
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $query = Transaction::query();

        if ($startDate) {
            $query->whereDate('date', '>=', Carbon::parse($startDate));
        }

        if ($endDate) {
            $query->whereDate('date', '<=', Carbon::parse($endDate));
        }

        $transactions = $query->get();

        $byMethod = $transactions->groupBy('payment_method')
            ->map(function ($group) {
                $method = $group->first()->paymentMethod;

                return [
                    'payment_method_id' => $method->id,
                    'payment_method_name' => $method->name,
                    'total_transactions' => $group->count(),
                    'total_amount' => (float) $group->sum('amount'),
                    'income' => (float) $group->where('type', 1)->sum('amount'),
                    'expenses' => (float) $group->where('type', 2)->sum('amount'),
                ];
            })
            ->values();

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'by_payment_method' => $byMethod->toArray(),
        ];
    }

    /**
     * Get daily summary report
     *
     * @param string|null $startDate
     * @param string|null $endDate
     *
     * @return array
     */
    public static function dailySummary(
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $query = Transaction::query();

        if ($startDate) {
            $query->whereDate('date', '>=', Carbon::parse($startDate));
        }

        if ($endDate) {
            $query->whereDate('date', '<=', Carbon::parse($endDate));
        }

        $transactions = $query->get();

        $byDay = $transactions->groupBy(function ($transaction) {
            return $transaction->date->format('Y-m-d');
        })->map(function ($group) {
            return [
                'date' => $group->first()->date->format('Y-m-d'),
                'income' => (float) $group->where('type', 1)->sum('amount'),
                'expenses' => (float) $group->where('type', 2)->sum('amount'),
                'net' => (float) ($group->where('type', 1)->sum('amount') - $group->where('type', 2)->sum('amount')),
                'transaction_count' => $group->count(),
            ];
        })->values();

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'daily_summary' => $byDay->toArray(),
        ];
    }

    /**
     * Get category performance report
     *
     * @param string|null $startDate
     * @param string|null $endDate
     *
     * @return array
     */
    public static function categoryPerformance(
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $query = Transaction::query();

        if ($startDate) {
            $query->whereDate('date', '>=', Carbon::parse($startDate));
        }

        if ($endDate) {
            $query->whereDate('date', '<=', Carbon::parse($endDate));
        }

        $transactions = $query->get();

        $byCategory = $transactions->groupBy('category_id')
            ->map(function ($group) {
                $category = $group->first()->category;

                return [
                    'category_id' => $category->id,
                    'category_name' => $category->name,
                    'category_type' => $category->type === 1 ? 'Income' : 'Expense',
                    'total_transactions' => $group->count(),
                    'total_amount' => (float) $group->sum('amount'),
                    'average_transaction' => (float) ($group->count() > 0 ? $group->sum('amount') / $group->count() : 0),
                ];
            })
            ->sortByDesc('total_amount')
            ->values();

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'by_category' => $byCategory->toArray(),
        ];
    }
}
