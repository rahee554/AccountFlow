<?php

namespace ArtflowStudio\AccountFlow\Services;

use ArtflowStudio\AccountFlow\Enums\TransactionType;
use ArtflowStudio\AccountFlow\Models\Account;
use ArtflowStudio\AccountFlow\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Financial reporting.
 *
 * Everything here aggregates in SQL. 0.2.x called `->get()` on the whole
 * transactions table for every report and then grouped and summed in PHP,
 * with an N+1 on `category` and `paymentMethod` inside each map — which does
 * not survive a real ledger.
 *
 * Rows with a null category or payment method are grouped under
 * "Uncategorised" / "Unassigned" rather than dereferenced. 0.2.x wrote
 * `$group->first()->paymentMethod->id`, so one uncategorised transaction was
 * enough to fatal the entire report.
 */
class ReportService
{
    /**
     * Income and expense totals, plus a per-category breakdown.
     *
     * @return array{
     *     period: array{start_date: string|null, end_date: string|null},
     *     summary: array{total_income: float, total_expense: float, net_income: float, transaction_count: int},
     *     income_by_category: list<array<string,mixed>>,
     *     expense_by_category: list<array<string,mixed>>,
     *     total_income_amount: float,
     *     total_expense_amount: float
     * }
     */
    public function incomeExpenseReport(?string $startDate = null, ?string $endDate = null, ?int $accountId = null): array
    {
        $totals = $this->baseQuery($startDate, $endDate, $accountId)->toBase()
            ->selectRaw($this->sumCase(TransactionType::Income).' as total_income')
            ->selectRaw($this->sumCase(TransactionType::Expense).' as total_expense')
            ->selectRaw('COUNT(*) as row_count')
            ->first();

        $income = (float) ($totals->total_income ?? 0);
        $expense = (float) ($totals->total_expense ?? 0);

        return [
            'period' => ['start_date' => $startDate, 'end_date' => $endDate],
            'summary' => [
                'total_income' => $income,
                'total_expense' => $expense,
                'net_income' => $income - $expense,
                'transaction_count' => (int) ($totals->row_count ?? 0),
            ],
            'income_by_category' => $this->byCategory(TransactionType::Income, $startDate, $endDate, $accountId),
            'expense_by_category' => $this->byCategory(TransactionType::Expense, $startDate, $endDate, $accountId),
            'total_income_amount' => $income,
            'total_expense_amount' => $expense,
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public function profitAndLoss(?string $startDate = null, ?string $endDate = null, ?int $accountId = null): array
    {
        $report = $this->incomeExpenseReport($startDate, $endDate, $accountId);

        $revenue = $report['summary']['total_income'];
        $profit = $report['summary']['net_income'];

        return [
            'period' => $report['period'],
            'revenue' => $revenue,
            'expenses' => $report['summary']['total_expense'],
            'profit' => $profit,
            'profit_margin' => $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0.0,
            'revenue_breakdown' => $report['income_by_category'],
            'expense_breakdown' => $report['expense_by_category'],
        ];
    }

    /**
     * Monthly inflow/outflow, grouped in SQL and portable across drivers.
     *
     * @return array<string,mixed>
     */
    public function cashFlowReport(?string $startDate = null, ?string $endDate = null, ?int $accountId = null): array
    {
        $rows = $this->baseQuery($startDate, $endDate, $accountId)->toBase()
            ->selectRaw($this->monthExpression().' as period')
            ->selectRaw($this->sumCase(TransactionType::Income).' as inflows')
            ->selectRaw($this->sumCase(TransactionType::Expense).' as outflows')
            ->groupBy(DB::raw($this->monthExpression()))
            ->orderBy('period')
            ->get();

        $byMonth = $rows->map(fn ($row): array => [
            'month' => (string) $row->period,
            'inflows' => (float) $row->inflows,
            'outflows' => (float) $row->outflows,
            'net_cash_flow' => (float) $row->inflows - (float) $row->outflows,
        ])->all();

        return [
            'period' => ['start_date' => $startDate, 'end_date' => $endDate],
            'by_month' => $byMonth,
            'total_inflows' => array_sum(array_column($byMonth, 'inflows')),
            'total_outflows' => array_sum(array_column($byMonth, 'outflows')),
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public function balanceReport(): array
    {
        $accounts = Account::query()
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'balance', 'opening_balance']);

        return [
            'accounts' => $accounts->map(fn (Account $account): array => [
                'account_id' => (int) $account->id,
                'account_name' => (string) $account->name,
                'balance' => (float) $account->balance,
                'opening_balance' => (float) $account->opening_balance,
            ])->all(),
            'total_balance' => (float) $accounts->sum('balance'),
            'report_date' => Carbon::now()->toDateString(),
        ];
    }

    /**
     * Totals per payment method. Transactions with no method are reported
     * under "Unassigned" instead of crashing the report.
     *
     * @return array<string,mixed>
     */
    public function byPaymentMethod(?string $startDate = null, ?string $endDate = null, ?int $accountId = null): array
    {
        $rows = $this->baseQuery($startDate, $endDate, $accountId)->toBase()
            ->leftJoin('ac_payment_methods', 'ac_payment_methods.id', '=', 'ac_transactions.payment_method')
            ->selectRaw('ac_transactions.payment_method as payment_method_id')
            ->selectRaw('ac_payment_methods.name as payment_method_name')
            ->selectRaw('COUNT(*) as total_transactions')
            ->selectRaw('COALESCE(SUM(ac_transactions.amount), 0) as total_amount')
            ->selectRaw($this->sumCase(TransactionType::Income, 'ac_transactions').' as income')
            ->selectRaw($this->sumCase(TransactionType::Expense, 'ac_transactions').' as expenses')
            ->groupBy('ac_transactions.payment_method', 'ac_payment_methods.name')
            ->get();

        return [
            'period' => ['start_date' => $startDate, 'end_date' => $endDate],
            'by_payment_method' => $rows->map(fn ($row): array => [
                'payment_method_id' => $row->payment_method_id !== null ? (int) $row->payment_method_id : null,
                'payment_method_name' => $row->payment_method_name ?? 'Unassigned',
                'total_transactions' => (int) $row->total_transactions,
                'total_amount' => (float) $row->total_amount,
                'income' => (float) $row->income,
                'expenses' => (float) $row->expenses,
            ])->all(),
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public function dailySummary(?string $startDate = null, ?string $endDate = null, ?int $accountId = null): array
    {
        $rows = $this->baseQuery($startDate, $endDate, $accountId)->toBase()
            ->selectRaw('date')
            ->selectRaw($this->sumCase(TransactionType::Income).' as income')
            ->selectRaw($this->sumCase(TransactionType::Expense).' as expenses')
            ->selectRaw('COUNT(*) as transaction_count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'period' => ['start_date' => $startDate, 'end_date' => $endDate],
            'daily_summary' => $rows->map(fn ($row): array => [
                'date' => Carbon::parse($row->date)->toDateString(),
                'income' => (float) $row->income,
                'expenses' => (float) $row->expenses,
                'net' => (float) $row->income - (float) $row->expenses,
                'transaction_count' => (int) $row->transaction_count,
            ])->all(),
        ];
    }

    /**
     * Totals per category. Uncategorised rows are reported, not fatal.
     *
     * @return array<string,mixed>
     */
    public function categoryPerformance(?string $startDate = null, ?string $endDate = null, ?int $accountId = null): array
    {
        $rows = $this->baseQuery($startDate, $endDate, $accountId)->toBase()
            ->leftJoin('ac_categories', 'ac_categories.id', '=', 'ac_transactions.category_id')
            ->selectRaw('ac_transactions.category_id')
            ->selectRaw('ac_categories.name as category_name')
            ->selectRaw('ac_categories.type as category_type')
            ->selectRaw('COUNT(*) as total_transactions')
            ->selectRaw('COALESCE(SUM(ac_transactions.amount), 0) as total_amount')
            ->groupBy('ac_transactions.category_id', 'ac_categories.name', 'ac_categories.type')
            ->orderByDesc('total_amount')
            ->get();

        return [
            'period' => ['start_date' => $startDate, 'end_date' => $endDate],
            'by_category' => $rows->map(function ($row): array {
                $count = (int) $row->total_transactions;
                $amount = (float) $row->total_amount;

                return [
                    'category_id' => $row->category_id !== null ? (int) $row->category_id : null,
                    'category_name' => $row->category_name ?? 'Uncategorised',
                    'category_type' => match ((int) ($row->category_type ?? 0)) {
                        TransactionType::Income->value => 'Income',
                        TransactionType::Expense->value => 'Expense',
                        default => 'Unknown',
                    },
                    'total_transactions' => $count,
                    'total_amount' => $amount,
                    'average_transaction' => $count > 0 ? round($amount / $count, 2) : 0.0,
                ];
            })->all(),
        ];
    }

    /**
     * A running-balance ledger for one account.
     *
     * @return array<string,mixed>
     */
    public function ledger(int $accountId, ?string $startDate = null, ?string $endDate = null): array
    {
        $account = Account::find($accountId);

        $opening = (float) ($account?->opening_balance ?? 0);

        // Everything before the window contributes to the opening balance.
        if ($startDate !== null) {
            $prior = Transaction::query()
                ->forAccount($accountId)
                ->whereDate('date', '<', $startDate)
                ->toBase()
                ->selectRaw($this->sumCase(TransactionType::Income).' as income')
                ->selectRaw($this->sumCase(TransactionType::Expense).' as expense')
                ->first();

            $opening += (float) ($prior->income ?? 0) - (float) ($prior->expense ?? 0);
        }

        $running = $opening;

        $entries = Transaction::query()
            ->forAccount($accountId)
            ->between($startDate, $endDate)
            ->with(['category:id,name', 'paymentMethod:id,name'])
            ->orderBy('date')
            ->orderBy('id')
            ->get()
            ->map(function (Transaction $transaction) use (&$running): array {
                $signed = $transaction->signedAmount();
                $running += $signed;

                return [
                    'id' => (int) $transaction->id,
                    'date' => $transaction->date?->toDateString(),
                    'description' => $transaction->description,
                    'category' => $transaction->category?->name ?? 'Uncategorised',
                    'payment_method' => $transaction->paymentMethod?->name ?? 'Unassigned',
                    'debit' => $transaction->isExpense() ? abs((float) $transaction->amount) : 0.0,
                    'credit' => $transaction->isIncome() ? abs((float) $transaction->amount) : 0.0,
                    'signed_amount' => $signed,
                    'balance' => round($running, 2),
                ];
            })
            ->all();

        return [
            'account' => $account?->only(['id', 'name']),
            'period' => ['start_date' => $startDate, 'end_date' => $endDate],
            'opening_balance' => round($opening, 2),
            'closing_balance' => round($running, 2),
            'entries' => $entries,
        ];
    }

    /**
     * @return list<array<string,mixed>>
     */
    private function byCategory(TransactionType $type, ?string $startDate, ?string $endDate, ?int $accountId): array
    {
        return $this->baseQuery($startDate, $endDate, $accountId)->toBase()
            ->leftJoin('ac_categories', 'ac_categories.id', '=', 'ac_transactions.category_id')
            ->where('ac_transactions.type', $type->value)
            ->selectRaw('ac_transactions.category_id')
            ->selectRaw('ac_categories.name as category_name')
            ->selectRaw('COALESCE(SUM(ac_transactions.amount), 0) as amount')
            ->selectRaw('COUNT(*) as row_count')
            ->groupBy('ac_transactions.category_id', 'ac_categories.name')
            ->orderByDesc('amount')
            ->get()
            ->map(fn ($row): array => [
                'category_id' => $row->category_id !== null ? (int) $row->category_id : null,
                'category_name' => $row->category_name ?? 'Uncategorised',
                'amount' => (float) $row->amount,
                'count' => (int) $row->row_count,
            ])
            ->all();
    }

    /**
     * @return Builder<Transaction>
     */
    private function baseQuery(?string $startDate, ?string $endDate, ?int $accountId = null): Builder
    {
        return Transaction::query()
            // Transfer legs move cash between your own accounts, so they belong
            // in a balance but never in revenue or costs.
            ->whereNull('ac_transactions.transfer_id')
            ->when($startDate, fn (Builder $q) => $q->whereDate('ac_transactions.date', '>=', Carbon::parse($startDate)))
            ->when($endDate, fn (Builder $q) => $q->whereDate('ac_transactions.date', '<=', Carbon::parse($endDate)))
            ->when($accountId, fn (Builder $q) => $q->where('ac_transactions.account_id', $accountId));
    }

    /**
     * Conditional sum for one transaction type.
     *
     * Uses a plain integer comparison. 0.2.x wrote `type IN ("income","1",1)`
     * with double-quoted literals, which MySQL tolerates but PostgreSQL and
     * SQLite read as identifiers — one of the reasons the package could not be
     * tested against SQLite.
     */
    private function sumCase(TransactionType $type, ?string $table = null): string
    {
        $column = $table !== null ? "{$table}.amount" : 'amount';
        $typeColumn = $table !== null ? "{$table}.type" : 'type';

        return "COALESCE(SUM(CASE WHEN {$typeColumn} = {$type->value} THEN {$column} ELSE 0 END), 0)";
    }

    /**
     * Driver-portable "year-month" expression.
     *
     * `DATE_FORMAT()` is MySQL-only; SQLite and PostgreSQL need their own.
     */
    private function monthExpression(): string|Expression
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => "strftime('%Y-%m', date)",
            'pgsql' => "to_char(date, 'YYYY-MM')",
            'sqlsrv' => "FORMAT(date, 'yyyy-MM')",
            default => "DATE_FORMAT(date, '%Y-%m')",
        };
    }
}
