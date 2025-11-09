<?php

namespace App\Livewire\Accountflow;

use App\Models\AccountFlow\Account;
use App\Models\AccountFlow\Category;
use App\Models\AccountFlow\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AccountsDashboard extends Component
{
    public $fluid;

    public $selectedPeriod = 'this_month';

    public $customStartDate = null;

    public $customEndDate = null;

    public $recentTransactions;

    public $topCategories;

    public $cashflowMonths;

    public $metrics;

    public $accounts;

    public $budgets;

    public $plannedPayments;

    public $paymentMethods;

    public $previousMetrics = [];

    public function mount()
    {
        $this->fluid = true;

        // initial load using selected period
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

    /**
     * Load all data for a given period key
     */
    protected function loadForPeriod(string $period): void
    {
        // Recent transactions: latest 10 (for context)
        $this->recentTransactions = Transaction::with(['account', 'category', 'paymentMethod'])
            ->orderByDesc('date')
            ->limit(10)
            ->get();

        // Compute date bounds based on period
        $now = Carbon::now();
        $start = null;
        $end = $now->toDateString();

        switch ($period) {
            case 'all_time':
                $start = null;
                break;
            case 'this_year':
                $start = $now->copy()->startOfYear()->toDateString();
                break;
            case 'last_year':
                $start = $now->copy()->subYear()->startOfYear()->toDateString();
                $end = $now->copy()->subYear()->endOfYear()->toDateString();
                break;
            case 'last_month':
                $start = $now->copy()->subMonth()->startOfMonth()->toDateString();
                $end = $now->copy()->subMonth()->endOfMonth()->toDateString();
                break;
            case 'custom':
                if ($this->customStartDate && $this->customEndDate) {
                    $start = $this->customStartDate;
                    $end = $this->customEndDate;
                } else {
                    $start = $now->copy()->startOfMonth()->toDateString();
                }
                break;
            case 'this_month':
            default:
                $start = $now->copy()->startOfMonth()->toDateString();
                break;
        }

        // Build queries with optional start date
        $incomeQuery = Transaction::income();
        $expenseQuery = Transaction::expense();

        if ($start) {
            $incomeQuery = $incomeQuery->whereDate('date', '>=', $start);
            $expenseQuery = $expenseQuery->whereDate('date', '>=', $start);
        }

        if ($period === 'last_year' || $period === 'last_month' || $period === 'custom') {
            $incomeQuery = $incomeQuery->whereDate('date', '<=', $end);
            $expenseQuery = $expenseQuery->whereDate('date', '<=', $end);
        }

        $income = (float) $incomeQuery->sum('amount');
        $expense = (float) $expenseQuery->sum('amount');

        // Calculate previous period metrics for comparison
        $this->calculatePreviousMetrics($period, $start, $end);

        // 6-month and 1-year aggregates (kept for additional cards)
        $since6m = Carbon::now()->subMonths(6)->toDateString();
        $income6 = (float) Transaction::income()->since($since6m)->sum('amount');
        $expense6 = (float) Transaction::expense()->since($since6m)->sum('amount');

        $since1y = Carbon::now()->subYear()->toDateString();
        $income1y = (float) Transaction::income()->since($since1y)->sum('amount');
        $expense1y = (float) Transaction::expense()->since($since1y)->sum('amount');

        $incomeAll = (float) Transaction::income()->sum('amount');
        $expenseAll = (float) Transaction::expense()->sum('amount');

        // total balance across accounts
        $totalBalance = (float) Account::sum('balance');

        $accountHealth = 0;
        $totalActivity = max(1, $income + $expense);
        if ($totalActivity > 0) {
            $accountHealth = (int) round(100 * ($income / $totalActivity));
        }

        $this->metrics = [
            'total_balance' => $totalBalance,
            'period_income' => $income,
            'period_expenses' => $expense,
            'six_month_income' => $income6,
            'six_month_expenses' => $expense6,
            'one_year_income' => $income1y,
            'one_year_expenses' => $expense1y,
            'all_time_income' => $incomeAll,
            'all_time_expenses' => $expenseAll,
            'account_health' => $accountHealth,
            'last_updated' => Transaction::orderByDesc('updated_at')->value('updated_at') ?? Carbon::now(),
        ];

        // Accounts list
        $this->accounts = Account::orderByDesc('balance')->get(['name', 'balance'])->map(fn ($a) => ['name' => $a->name, 'balance' => $a->balance])->toArray();

        // Top expense categories for the same period
        $topQuery = Transaction::select('category_id', DB::raw('SUM(CASE WHEN type IN ("expense","2",2) THEN amount ELSE 0 END) as total_expense'), DB::raw('SUM(CASE WHEN type IN ("income","1",1) THEN amount ELSE 0 END) as total_income'));
        if ($start) {
            $topQuery->whereDate('date', '>=', $start);
        }
        $top = $topQuery->groupBy('category_id')->orderByDesc('total_expense')->limit(5)->get();

        $catIds = $top->pluck('category_id')->filter()->unique()->values()->all();
        $catMap = [];
        if (! empty($catIds)) {
            $catMap = Category::whereIn('id', $catIds)->pluck('name', 'id')->toArray();
        }

        $this->topCategories = $top->map(fn ($r) => [
            'id' => $r->category_id,
            'name' => $catMap[$r->category_id] ?? ('#'.$r->category_id),
            'expense' => (float) $r->total_expense,
            'income' => (float) $r->total_income,
        ]);

        // cashflow: last 6 months (used for trends)
        $startFlows = Carbon::now()->startOfMonth()->subMonths(5)->toDateString();
        $flows = Transaction::select(DB::raw("DATE_FORMAT(date, '%Y-%m') as ym"), DB::raw('SUM(CASE WHEN type IN ("income","1",1) THEN amount ELSE 0 END) as income'), DB::raw('SUM(CASE WHEN type IN ("expense","2",2) THEN amount ELSE 0 END) as expense'))
            ->whereDate('date', '>=', $startFlows)
            ->groupBy('ym')
            ->orderBy('ym')
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

        // Budgets and planned payments (keep lightweight and safe if models exist)
        if (class_exists('\App\\Models\\AccountFlow\\Budget')) {
            try {
                $budgetModel = \App\Models\AccountFlow\Budget::class;
                $this->budgets = $budgetModel::orderBy('end_date')->limit(6)->get()->map(fn ($b) => [
                    'title' => $b->name ?? ($b->title ?? 'Budget'),
                    'allocated' => $b->amount ?? 0,
                    'spent' => $b->spent ?? 0,
                    'ends_at' => $b->end_date ?? null,
                ])->toArray();
            } catch (\Throwable $e) {
                $this->budgets = [];
            }
        } else {
            $this->budgets = [];
        }

        if (class_exists('\App\\Models\\AccountFlow\\PlannedPayment')) {
            try {
                $ppModel = \App\Models\AccountFlow\PlannedPayment::class;
                $this->plannedPayments = $ppModel::whereDate('due_date', '>=', Carbon::now()->toDateString())->orderBy('due_date')->limit(6)->get()->map(fn ($p) => [
                    'title' => $p->title ?? $p->description ?? 'Planned',
                    'amount' => $p->amount ?? 0,
                    'due_date' => $p->due_date ?? null,
                ])->toArray();
            } catch (\Throwable $e) {
                $this->plannedPayments = [];
            }
        } else {
            $this->plannedPayments = [];
        }

    }

    /**
     * Calculate previous period metrics for comparison
     */
    protected function calculatePreviousMetrics(string $period, ?string $currentStart, string $currentEnd): void
    {
        $now = Carbon::now();
        $prevStart = null;
        $prevEnd = null;

        switch ($period) {
            case 'this_month':
                // Compare with last month
                $prevStart = $now->copy()->subMonth()->startOfMonth()->toDateString();
                $prevEnd = $now->copy()->subMonth()->endOfMonth()->toDateString();
                break;
            case 'last_month':
                // Compare with the month before last month
                $prevStart = $now->copy()->subMonths(2)->startOfMonth()->toDateString();
                $prevEnd = $now->copy()->subMonths(2)->endOfMonth()->toDateString();
                break;
            case 'this_year':
                // Compare with last year
                $prevStart = $now->copy()->subYear()->startOfYear()->toDateString();
                $prevEnd = $now->copy()->subYear()->endOfYear()->toDateString();
                break;
            case 'last_year':
                // Compare with the year before last year
                $prevStart = $now->copy()->subYears(2)->startOfYear()->toDateString();
                $prevEnd = $now->copy()->subYears(2)->endOfYear()->toDateString();
                break;
            case 'custom':
                if ($currentStart && $currentEnd) {
                    // Calculate the duration and go back by the same duration
                    $startDate = Carbon::parse($currentStart);
                    $endDate = Carbon::parse($currentEnd);
                    $diffDays = $startDate->diffInDays($endDate) + 1;
                    $prevEnd = $startDate->copy()->subDay()->toDateString();
                    $prevStart = $startDate->copy()->subDays($diffDays)->toDateString();
                }
                break;
            case 'all_time':
            default:
                // No comparison for all_time
                $this->previousMetrics = [];

                return;
        }

        if ($prevStart && $prevEnd) {
            $prevIncome = (float) Transaction::income()
                ->whereDate('date', '>=', $prevStart)
                ->whereDate('date', '<=', $prevEnd)
                ->sum('amount');

            $prevExpense = (float) Transaction::expense()
                ->whereDate('date', '>=', $prevStart)
                ->whereDate('date', '<=', $prevEnd)
                ->sum('amount');

            $this->previousMetrics = [
                'income' => $prevIncome,
                'expenses' => $prevExpense,
            ];
        } else {
            $this->previousMetrics = [];
        }
    }

    public function render()
    {
        $viewpath = config('accountflow.view_path').'livewire.accounts-dashboard';
        $layout = config('accountflow.layout');
        $title = 'Accounts Dashboard | '.config('accountflow.business_name');

        return view($viewpath, [
            'fluid' => $this->fluid,
            'selectedPeriod' => $this->selectedPeriod,
            'recentTransactions' => $this->recentTransactions,
            'topCategories' => $this->topCategories,
            'cashflowMonths' => $this->cashflowMonths,
            'metrics' => $this->metrics,
            'accounts' => $this->accounts,
            'budgets' => $this->budgets ?? [],
            'plannedPayments' => $this->plannedPayments ?? [],
            'previousMetrics' => $this->previousMetrics,
        ])->extends($layout)->section('content')->title($title);
    }
}
