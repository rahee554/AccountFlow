<?php

namespace ArtflowStudio\AccountFlow\Livewire;

use ArtflowStudio\AccountFlow\Concerns\AuthorizesAccountFlow;
use ArtflowStudio\AccountFlow\Enums\Ability;
use ArtflowStudio\AccountFlow\Models\Account;
use ArtflowStudio\AccountFlow\Models\Category;
use ArtflowStudio\AccountFlow\Models\Transaction;
use ArtflowStudio\AccountFlow\Support\SqlDialect;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Throwable;

class AccountsDashboard extends Component
{
    use AuthorizesAccountFlow;

    public bool $fluid = true;

    public string $selectedPeriod = 'this_month';

    public ?string $customStartDate = null;

    public ?string $customEndDate = null;

    public array $recentTransactions = [];

    public array $topCategories = [];

    public array $cashflowMonths = [];

    public array $metrics = [];

    public array $accounts = [];

    public array $previousMetrics = [];

    /** Currency resolved from DB setting or config fallback. */
    public string $currency = 'PKR';

    /** Currency display symbol (e.g. 'Rs. ', '$', '€'). */
    public string $currencySymbol = 'Rs. ';

    public function mount(): void
    {
        $this->authorizeAccountFlow(Ability::ViewDashboard);
        $this->currency = $this->resolveCurrency();
        $this->currencySymbol = $this->resolveCurrencySymbol();
        $this->loadForPeriod($this->selectedPeriod);
    }

    /**
     * Change the selected period (called from UI)
     */
    public function changePeriod(string $period): void
    {
        $this->selectedPeriod = $period;
        $this->customStartDate = null;
        $this->customEndDate = null;
        $this->loadForPeriod($period);
    }

    /**
     * Apply custom date range
     */
    public function applyDateRange(): void
    {
        if ($this->customStartDate && $this->customEndDate) {
            $this->selectedPeriod = 'custom';
            $this->loadForPeriod('custom');
        }
    }

    public function render(): \Illuminate\View\View
    {
        $viewpath = config('accountflow.view_path').'livewire.accounts-dashboard';
        $layout = config('accountflow.layout');
        $title = 'Accounts Dashboard | '.config('accountflow.business_name');

        return view($viewpath, [
            'fluid' => $this->fluid,
            'selectedPeriod' => $this->selectedPeriod,
            'currency' => $this->currency,
            'currencySymbol' => $this->currencySymbol,
            'recentTransactions' => $this->recentTransactions,
            'topCategories' => $this->topCategories,
            'cashflowMonths' => $this->cashflowMonths,
            'metrics' => $this->metrics,
            'accounts' => $this->accounts,
            'previousMetrics' => $this->previousMetrics,
        ])->extends($layout)->section('content')->title($title);
    }

    /** Resolve currency symbol from config currency_symbols map. */
    protected function resolveCurrencySymbol(): string
    {
        $symbols = config('accountflow.currency_symbols', []);

        return $symbols[$this->currency] ?? ($this->currency.' ');
    }

    /** Resolve currency: DB setting first, config fallback. */
    protected function resolveCurrency(): string
    {
        try {
            $dbVal = \ArtflowStudio\AccountFlow\Models\Setting::where('key', 'currency')
                ->where('type', 2)
                ->value('value');

            return $dbVal ?: config('accountflow.currency', 'PKR');
        } catch (Throwable $e) {
            return config('accountflow.currency', 'PKR');
        }
    }

    /**
     * Resolve the start/end date bounds for a given period key.
     *
     * @return array{start: string|null, end: string}
     */
    protected function resolvePeriodDates(string $period): array
    {
        $now = Carbon::now();

        return match ($period) {
            'all_time' => ['start' => null,                                                      'end' => $now->toDateString()],
            'this_year' => ['start' => $now->copy()->startOfYear()->toDateString(),               'end' => $now->toDateString()],
            'last_year' => ['start' => $now->copy()->subYear()->startOfYear()->toDateString(),    'end' => $now->copy()->subYear()->endOfYear()->toDateString()],
            'last_month' => ['start' => $now->copy()->subMonth()->startOfMonth()->toDateString(),  'end' => $now->copy()->subMonth()->endOfMonth()->toDateString()],
            'custom' => $this->customStartDate && $this->customEndDate
                                ? ['start' => $this->customStartDate, 'end' => $this->customEndDate]
                                : ['start' => $now->copy()->startOfMonth()->toDateString(),        'end' => $now->toDateString()],
            default => ['start' => $now->copy()->startOfMonth()->toDateString(),              'end' => $now->toDateString()], // this_month
        };
    }

    /**
     * Load all dashboard data for a given period key.
     * Minimised to 6 queries total: period aggregates, all-time aggregates,
     * accounts, top-categories, cashflow, and recent transactions.
     */
    protected function loadForPeriod(string $period): void
    {
        ['start' => $start, 'end' => $end] = $this->resolvePeriodDates($period);

        // ── 1. Period income/expense in a single conditional-aggregate query ──
        $periodQuery = Transaction::query();

        if ($start) {
            $periodQuery->whereDate('date', '>=', $start);
        }

        // Always apply the upper bound so future-dated rows are excluded
        $periodQuery->whereDate('date', '<=', $end);

        $periodTotals = $periodQuery->selectRaw(
            'SUM(CASE WHEN type = 1 THEN amount ELSE 0 END) as income,
             SUM(CASE WHEN type = 2 THEN amount ELSE 0 END) as expense',
        )->first();

        $income = (float) ($periodTotals->income ?? 0);
        $expense = (float) ($periodTotals->expense ?? 0);

        // ── 2. All-time + rolling window aggregates in one query ──
        $totals = Transaction::selectRaw(
            'SUM(CASE WHEN type = 1 THEN amount ELSE 0 END) as all_income,
             SUM(CASE WHEN type = 2 THEN amount ELSE 0 END) as all_expense,
             SUM(CASE WHEN type = 1 AND date >= ? THEN amount ELSE 0 END) as income_6m,
             SUM(CASE WHEN type = 2 AND date >= ? THEN amount ELSE 0 END) as expense_6m,
             SUM(CASE WHEN type = 1 AND date >= ? THEN amount ELSE 0 END) as income_1y,
             SUM(CASE WHEN type = 2 AND date >= ? THEN amount ELSE 0 END) as expense_1y',
            [
                Carbon::now()->subMonths(6)->toDateString(),
                Carbon::now()->subMonths(6)->toDateString(),
                Carbon::now()->subYear()->toDateString(),
                Carbon::now()->subYear()->toDateString(),
            ],
        )->first();

        // ── 3. Previous-period comparison ──
        $this->calculatePreviousMetrics($period, $start, $end);

        // ── 4. Account balances ──
        $this->accounts = Account::orderByDesc('balance')
            ->get(['id', 'name', 'balance'])
            ->map(fn ($a) => ['name' => $a->name, 'balance' => (float) $a->balance])
            ->toArray();

        $totalBalance = array_sum(array_column($this->accounts, 'balance'));
        $totalActivity = max(1, $income + $expense);
        $accountHealth = (int) round(100 * ($income / $totalActivity));

        $this->metrics = [
            'total_balance' => $totalBalance,
            'period_income' => $income,
            'period_expenses' => $expense,
            'six_month_income' => (float) ($totals->income_6m ?? 0),
            'six_month_expenses' => (float) ($totals->expense_6m ?? 0),
            'one_year_income' => (float) ($totals->income_1y ?? 0),
            'one_year_expenses' => (float) ($totals->expense_1y ?? 0),
            'all_time_income' => (float) ($totals->all_income ?? 0),
            'all_time_expenses' => (float) ($totals->all_expense ?? 0),
            'account_health' => $accountHealth,
            'last_updated' => Transaction::orderByDesc('updated_at')->value('updated_at') ?? Carbon::now(),
        ];

        // ── 5. Top expense categories (with income) ──
        $topQuery = Transaction::select(
            'category_id',
            DB::raw('SUM(CASE WHEN type = 2 THEN amount ELSE 0 END) as total_expense'),
            DB::raw('SUM(CASE WHEN type = 1 THEN amount ELSE 0 END) as total_income'),
        );

        if ($start) {
            $topQuery->whereDate('date', '>=', $start);
        }
        $topQuery->whereDate('date', '<=', $end);

        $top = $topQuery->groupBy('category_id')
            ->orderByDesc('total_expense')
            ->limit(5)
            ->toBase()
            ->get();

        $catIds = $top->pluck('category_id')->filter()->unique()->values()->all();
        $catMap = empty($catIds) ? [] : Category::whereIn('id', $catIds)->pluck('name', 'id')->toArray();

        $totalCatExpense = $top->sum('total_expense') ?: 1;

        $this->topCategories = $top->map(fn ($r) => [
            'id' => $r->category_id,
            'name' => $catMap[$r->category_id] ?? ('#'.$r->category_id),
            'expense' => (float) $r->total_expense,
            'income' => (float) $r->total_income,
            'pct' => round(100 * ($r->total_expense / $totalCatExpense), 1),
        ])->values()->toArray();

        // ── 6. Cashflow: last 6 calendar months ──
        $startFlows = Carbon::now()->startOfMonth()->subMonths(5)->toDateString();
        $flows = Transaction::select(
            DB::raw(SqlDialect::yearMonth('date').' as ym'),
            DB::raw('SUM(CASE WHEN type = 1 THEN amount ELSE 0 END) as income'),
            DB::raw('SUM(CASE WHEN type = 2 THEN amount ELSE 0 END) as expense'),
        )
            ->whereDate('date', '>=', $startFlows)
            ->groupBy('ym')
            ->orderBy('ym')
            ->toBase()
            ->get()
            ->keyBy('ym');

        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = Carbon::now()->startOfMonth()->subMonths($i);
            $ym = $m->format('Y-m');
            $row = $flows->get($ym);
            $incomeM = $row ? (float) $row->income : 0.0;
            $expenseM = $row ? (float) $row->expense : 0.0;
            $months[] = [
                'label' => $m->format('M Y'),
                'income' => $incomeM,
                'expense' => $expenseM,
                'net' => $incomeM - $expenseM,
            ];
        }
        $this->cashflowMonths = $months;

        // ── 7. Recent transactions (eager-loaded, limit 10) ──
        $this->recentTransactions = Transaction::with(['account', 'category', 'paymentMethod'])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->toArray();

    }

    /**
     * Calculate previous-period metrics for percentage comparisons.
     */
    protected function calculatePreviousMetrics(string $period, ?string $currentStart, string $currentEnd): void
    {
        if ($period === 'all_time') {
            $this->previousMetrics = [];

            return;
        }

        $now = Carbon::now();

        [$prevStart, $prevEnd] = match ($period) {
            'this_month' => [$now->copy()->subMonth()->startOfMonth()->toDateString(),   $now->copy()->subMonth()->endOfMonth()->toDateString()],
            'last_month' => [$now->copy()->subMonths(2)->startOfMonth()->toDateString(), $now->copy()->subMonths(2)->endOfMonth()->toDateString()],
            'this_year' => [$now->copy()->subYear()->startOfYear()->toDateString(),     $now->copy()->subYear()->endOfYear()->toDateString()],
            'last_year' => [$now->copy()->subYears(2)->startOfYear()->toDateString(),   $now->copy()->subYears(2)->endOfYear()->toDateString()],
            'custom' => $currentStart && $currentEnd
                                ? (function () use ($currentStart) {
                                    $startDate = Carbon::parse($currentStart);
                                    $prevEnd = $startDate->copy()->subDay()->toDateString();
                                    $prevStart = $startDate->copy()->subDays($startDate->diffInDays(Carbon::parse($currentStart)) + 1)->toDateString();

                                    return [$prevStart, $prevEnd];
                                })()
                                : [null, null],
            default => [null, null],
        };

        if (! $prevStart || ! $prevEnd) {
            $this->previousMetrics = [];

            return;
        }

        $prev = Transaction::selectRaw(
            'SUM(CASE WHEN type = 1 THEN amount ELSE 0 END) as income,
             SUM(CASE WHEN type = 2 THEN amount ELSE 0 END) as expense',
        )
            ->whereDate('date', '>=', $prevStart)
            ->whereDate('date', '<=', $prevEnd)
            ->toBase()
            ->first();

        $this->previousMetrics = [
            'income' => (float) ($prev->income ?? 0),
            'expenses' => (float) ($prev->expense ?? 0),
        ];
    }
}
