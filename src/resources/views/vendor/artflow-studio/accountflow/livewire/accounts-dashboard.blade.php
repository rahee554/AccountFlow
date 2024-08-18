{{-- filepath: d:\Repositories\Al-Emaan_Travels\resources\views\vendor\artflow-studio\accountflow\livewire\accounts-dashboard.blade.php --}}
@php
    use Illuminate\Support\Carbon;

    // Ensure all data is properly formatted
    $metrics = $metrics ?? [];
    $metrics = array_merge([
        'total_balance' => 0,
        'monthly_income' => 0,
        'monthly_expenses' => 0,
        'account_health' => 0,
        'last_updated' => Carbon::now(),
        'six_month_income' => 0,
        'six_month_expenses' => 0,
        'one_year_income' => 0,
        'one_year_expenses' => 0,
    ], $metrics);

    $accounts = $accounts ?? [];
    $topCategories = $topCategories ?? [];
    $cashflowMonths = $cashflowMonths ?? [];

    // Build categories from $topCategories
    $categories = collect($topCategories)->map(function ($c) {
        return [
            'id' => $c['id'] ?? null,
            'label' => $c['name'] ?? ('#' . ($c['id'] ?? 'N/A')),
            'value' => isset($c['expense']) ? (float) $c['expense'] : 0,
            'income' => isset($c['income']) ? (float) $c['income'] : 0,
            'pct' => 0,
        ];
    })->toArray();

    // Compute category percentages
    $totalCategory = collect($categories)->sum('value') ?: 1;
    $categories = collect($categories)->map(function ($c) use ($totalCategory) {
        $c['pct'] = $totalCategory > 0 ? round(100 * ($c['value'] / $totalCategory), 1) : 0;
        return $c;
    })->toArray();

    // Build trends from cashflowMonths
    $trends = [
        'labels' => collect($cashflowMonths)->pluck('label')->toArray(),
        'income' => collect($cashflowMonths)->pluck('income')->map(fn($v) => (float) $v)->toArray(),
        'expenses' => collect($cashflowMonths)->pluck('expense')->map(fn($v) => (float) $v)->toArray(),
    ];

    // Account distribution for donut chart
    $accountLabels = collect($accounts)->pluck('name')->toArray();
    $accountValues = collect($accounts)->pluck('balance')->map(fn($v) => (float) $v)->toArray();

    // Category chart data
    $categoryLabels = collect($categories)->pluck('label')->toArray();
    $categoryValues = collect($categories)->pluck('value')->map(fn($v) => (float) $v)->toArray();
@endphp
<div>
    @include(config('accountflow.view_path') . '.blades.dashboard-header')

    <div class="d-flex flex-column flex-column-fluid">
        <!--begin::Content-->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">

                <!-- Enhanced Financial Overview KPIs -->
                <!-- Period selector -->
                <div class="row mb-5">
                    <div class="col-12">
                        <div class="card card-flush">
                            <div class="card-body py-4">
                                <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between">
                                    <div class="d-flex flex-wrap gap-2">
                                        @php
                                            $periods = [
                                                'this_month' => ['label' => 'This Month', 'icon' => 'fa-calendar-day'],
                                                'last_month' => ['label' => 'Last Month', 'icon' => 'fa-calendar-minus'],
                                                'this_year' => ['label' => 'This Year', 'icon' => 'fa-calendar-check'],
                                                'last_year' => ['label' => 'Last Year', 'icon' => 'fa-calendar-times'],
                                                'all_time' => ['label' => 'All Time', 'icon' => 'fa-infinity'],
                                            ];
                                        @endphp
                                        @foreach($periods as $key => $data)
                                            <button type="button" 
                                                    wire:click.prevent="changePeriod('{{ $key }}')"
                                                    class="btn btn-sm {{ ($selectedPeriod ?? 'this_month') === $key ? 'btn-primary' : 'btn-light-primary' }}">
                                                <i class="fas {{ $data['icon'] }} me-1"></i>
                                                {{ $data['label'] }}
                                            </button>
                                        @endforeach
                                    </div>
                                    
                                    <div class="d-flex gap-2 align-items-center">
                                        <input type="date" 
                                               wire:model="customStartDate" 
                                               class="form-control form-control-sm" 
                                               style="width: 150px;"
                                               placeholder="Start Date">
                                        <span class="text-muted">to</span>
                                        <input type="date" 
                                               wire:model="customEndDate" 
                                               class="form-control form-control-sm" 
                                               style="width: 150px;"
                                               placeholder="End Date">
                                        <button type="button" 
                                                wire:click="applyDateRange" 
                                                class="btn btn-sm btn-primary">
                                            <i class="fas fa-filter me-1"></i>Apply
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="mt-3 d-flex align-items-center gap-3">
                                    <span class="badge badge-light-primary fs-7">
                                        <i class="fas fa-filter me-1"></i>
                                        Showing: <strong>{{ $periods[$selectedPeriod ?? 'this_month']['label'] ?? 'Custom Range' }}</strong>
                                    </span>
                                    @if(isset($metrics['period_income']) && isset($metrics['period_expenses']))
                                        <span class="text-muted fs-8">
                                            Net: <strong class="{{ ($metrics['period_income'] - $metrics['period_expenses']) >= 0 ? 'text-success' : 'text-danger' }}">
                                                PKR {{ number_format($metrics['period_income'] - $metrics['period_expenses'], 2) }}
                                            </strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-5 g-xl-8 mb-5 mb-xl-10">
                    <!-- Total Balance -->
                    <div class="col-xl-3 col-lg-6">
                        <div class="card card-flush h-xl-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                            <div class="card-body d-flex flex-column justify-content-between p-6">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="symbol symbol-50px">
                                        <div class="symbol-label" style="background: rgba(255,255,255,0.2);">
                                            <i class="fas fa-wallet text-white fs-2"></i>
                                        </div>
                                    </div>
                                    <span class="badge badge-light-success fs-8">
                                        <i class="fas fa-circle-check"></i> Live
                                    </span>
                                </div>
                                <div>
                                    <span class="text-white opacity-75 fw-semibold fs-7 d-block mb-1">Total Balance</span>
                                    <div class="d-flex align-items-end">
                                        <span class="text-white fs-4 fw-semibold me-2">PKR</span>
                                        <span class="text-white fs-2hx fw-bolder me-2 lh-1 stats-value" data-count="{{ (int) ($metrics['total_balance'] ?? 0) }}">0</span>
                                    </div>
                                    <div class="mt-3 d-flex align-items-center">
                                        <span class="text-white opacity-75 fs-8">All Accounts Combined</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Period Income -->
                    <div class="col-xl-3 col-lg-6">
                        <div class="card card-flush h-xl-100" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); border: none;">
                            <div class="card-body d-flex flex-column justify-content-between p-6">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="symbol symbol-50px">
                                        <div class="symbol-label" style="background: rgba(255,255,255,0.2);">
                                            <i class="fas fa-arrow-trend-up text-white fs-2"></i>
                                        </div>
                                    </div>
                                    @php
                                        $currentIncome = $metrics['period_income'] ?? 0;
                                        $prevIncome = $previousMetrics['income'] ?? 0;
                                        $incomeChange = 0;
                                        $incomeDirection = 'up';
                                        if ($prevIncome > 0) {
                                            $incomeChange = (($currentIncome - $prevIncome) / $prevIncome) * 100;
                                            $incomeDirection = $incomeChange >= 0 ? 'up' : 'down';
                                        }
                                    @endphp
                                    @if(!empty($previousMetrics) && $prevIncome > 0)
                                        <span class="badge badge-white fs-8">
                                            <i class="fas fa-arrow-{{ $incomeDirection }} text-{{ $incomeDirection === 'up' ? 'success' : 'danger' }}"></i> {{ number_format(abs($incomeChange), 1) }}%
                                        </span>
                                    @endif
                                </div>
                                <div>
                                    <span class="text-white opacity-75 fw-semibold fs-7 d-block mb-1">Income</span>
                                    <div class="d-flex align-items-end">
                                        <span class="text-white fs-4 fw-semibold me-2">PKR</span>
                                        <span class="text-white fs-2hx fw-bolder me-2 lh-1 stats-value" data-count="{{ (int) ($metrics['period_income'] ?? ($metrics['monthly_income'] ?? 0)) }}">0</span>
                                    </div>
                                    <div class="mt-3">
                                        <div class="progress bg-white bg-opacity-20 h-6px">
                                            <div class="progress-bar bg-white" style="width: 75%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Period Expenses -->
                    <div class="col-xl-3 col-lg-6">
                        <div class="card card-flush h-xl-100" style="background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%); border: none;">
                            <div class="card-body d-flex flex-column justify-content-between p-6">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="symbol symbol-50px">
                                        <div class="symbol-label" style="background: rgba(255,255,255,0.2);">
                                            <i class="fas fa-arrow-trend-down text-white fs-2"></i>
                                        </div>
                                    </div>
                                    @php
                                        $currentExpense = $metrics['period_expenses'] ?? 0;
                                        $prevExpense = $previousMetrics['expenses'] ?? 0;
                                        $expenseChange = 0;
                                        $expenseDirection = 'up';
                                        if ($prevExpense > 0) {
                                            $expenseChange = (($currentExpense - $prevExpense) / $prevExpense) * 100;
                                            $expenseDirection = $expenseChange >= 0 ? 'up' : 'down';
                                        }
                                    @endphp
                                    @if(!empty($previousMetrics) && $prevExpense > 0)
                                        <span class="badge badge-white fs-8">
                                            <i class="fas fa-arrow-{{ $expenseDirection }} text-{{ $expenseDirection === 'up' ? 'danger' : 'success' }}"></i> {{ number_format(abs($expenseChange), 1) }}%
                                        </span>
                                    @endif
                                </div>
                                <div>
                                    <span class="text-white opacity-75 fw-semibold fs-7 d-block mb-1">Expenses</span>
                                    <div class="d-flex align-items-end">
                                        <span class="text-white fs-4 fw-semibold me-2">PKR</span>
                                        <span class="text-white fs-2hx fw-bolder me-2 lh-1 stats-value" data-count="{{ (int) ($metrics['period_expenses'] ?? ($metrics['monthly_expenses'] ?? 0)) }}">0</span>
                                    </div>
                                    <div class="mt-3">
                                        <div class="progress bg-white bg-opacity-20 h-6px">
                                            <div class="progress-bar bg-white" style="width: 60%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Account Health -->
                    <div class="col-xl-3 col-lg-6">
                        <div class="card card-flush h-xl-100" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border: none;">
                            <div class="card-body d-flex flex-column justify-content-between p-6">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="symbol symbol-50px">
                                        <div class="symbol-label" style="background: rgba(255,255,255,0.2);">
                                            <i class="fas fa-chart-line text-white fs-2"></i>
                                        </div>
                                    </div>
                                    <span class="badge badge-white fs-8">
                                        {{ $metrics['account_health'] > 80 ? 'A+' : ($metrics['account_health'] > 60 ? 'A' : 'B') }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-white opacity-75 fw-semibold fs-7 d-block mb-1">Account Health</span>
                                    <div class="d-flex align-items-end">
                                        <span class="text-white fs-2hx fw-bolder me-2 lh-1">{{ $metrics['account_health'] ?? 0 }}%</span>
                                    </div>
                                    <div class="mt-3">
                                        <div class="progress bg-white bg-opacity-20 h-6px">
                                            <div class="progress-bar bg-white" style="width: {{ $metrics['account_health'] ?? 0 }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Charts Row -->
                <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
                    <!-- Financial Trends Chart -->
                    <div class="col-xl-8">
                        <div class="card card-flush h-xl-100">
                            <div class="card-header pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold text-dark fs-3">
                                        <i class="fas fa-chart-line me-2 text-primary"></i>Financial Trends
                                    </span>
                                    <span class="text-muted fw-semibold fs-7">Monthly income vs expenses
                                        analysis</span>
                                </h3>
                                <div class="card-toolbar">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <input type="radio" class="btn-check" name="kt_charts_widget_1_options"
                                            value="1" id="kt_charts_widget_1_1" checked="checked">
                                        <label class="btn btn-outline btn-outline-dashed btn-active-light-primary"
                                            for="kt_charts_widget_1_1">6M</label>
                                        <input type="radio" class="btn-check" name="kt_charts_widget_1_options"
                                            value="2" id="kt_charts_widget_1_2">
                                        <label class="btn btn-outline btn-outline-dashed btn-active-light-primary"
                                            for="kt_charts_widget_1_2">1Y</label>
                                        <input type="radio" class="btn-check" name="kt_charts_widget_1_options"
                                            value="3" id="kt_charts_widget_1_3">
                                        <label class="btn btn-outline btn-outline-dashed btn-active-light-primary"
                                            for="kt_charts_widget_1_3">All</label>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pt-6">
                                <div id="kt_charts_widget_1" class="h-350px"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Account Distribution -->
                    <div class="col-xl-4">
                        <div class="card card-flush h-xl-100">
                            <div class="card-header pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold text-dark fs-3">
                                        <i class="fas fa-chart-pie me-2 text-primary"></i>Account Distribution
                                    </span>
                                    <span class="text-muted fw-semibold fs-7">Balance across all accounts</span>
                                </h3>
                            </div>
                            <div class="card-body pt-6">
                                <div id="kt_charts_widget_2" class="h-350px mb-3"></div>
                                <div class="account-legend">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="bullet w-10px h-10px rounded-2 bg-primary me-3"></div>
                                        <div class="flex-1">
                                            <div class="fw-bold text-gray-800 fs-7">HBL Current</div>
                                            <div class="text-muted fs-8">PKR 987,440 (79.2%)</div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="bullet w-10px h-10px rounded-2 bg-success me-3"></div>
                                        <div class="flex-1">
                                            <div class="fw-bold text-gray-800 fs-7">JazzCash Business</div>
                                            <div class="text-muted fs-8">PKR 213,000 (17.1%)</div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="bullet w-10px h-10px rounded-2 bg-warning me-3"></div>
                                        <div class="flex-1">
                                            <div class="fw-bold text-gray-800 fs-7">Petty Cash</div>
                                            <div class="text-muted fs-8">PKR 45,230 (3.7%)</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Budgets -->
                        <div class="card card-flush mt-4">
                            <div class="card-header">
                                <h3 class="card-title fw-bold">Budgets</h3>
                                <div class="card-toolbar">
                                    <a href="#" class="btn btn-sm btn-light-primary">Manage</a>
                                </div>
                            </div>
                            <div class="card-body">
                                @forelse($budgets ?? [] as $b)
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <div class="fw-bold">{{ $b['title'] }}</div>
                                            <div class="text-muted fs-8">Ends: {{ \Carbon\Carbon::parse($b['ends_at'])->format('M d, Y') ?? '—' }}</div>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold">{{ config('accountflow.currency', 'PKR') }} {{ number_format($b['allocated'] ?? 0, 2) }}</div>
                                            <div class="text-muted fs-8">Spent: {{ number_format($b['spent'] ?? 0, 2) }}</div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-muted">No budgets found</div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Planned Payments -->
                        <div class="card card-flush mt-4 mb-6">
                            <div class="card-header">
                                <h3 class="card-title fw-bold">Planned Payments</h3>
                            </div>
                            <div class="card-body">
                                @forelse($plannedPayments ?? [] as $p)
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <div class="fw-bold">{{ $p['title'] }}</div>
                                            <div class="text-muted fs-8">Due: {{ \Carbon\Carbon::parse($p['due_date'])->format('M d, Y') ?? '—' }}</div>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold">{{ config('accountflow.currency', 'PKR') }} {{ number_format($p['amount'] ?? 0, 2) }}</div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-muted">No planned payments</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Category-based Expenses Analysis -->
                <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
                    <div class="col-xl-12">
                        <div class="card card-flush h-xl-100">
                            <div class="card-header pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold text-dark fs-3">
                                        <i class="fas fa-tags me-2 text-primary"></i>Category-wise Expense Analysis
                                    </span>
                                    <span class="text-muted fw-semibold fs-7">Detailed breakdown of expenses by
                                        category</span>
                                </h3>
                                <div class="card-toolbar">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <input type="radio" class="btn-check" name="kt_charts_widget_3_options"
                                            value="1" id="kt_charts_widget_3_1" checked="checked">
                                        <label class="btn btn-outline btn-outline-dashed btn-active-light-primary"
                                            for="kt_charts_widget_3_1">This Month</label>
                                        <input type="radio" class="btn-check" name="kt_charts_widget_3_options"
                                            value="2" id="kt_charts_widget_3_2">
                                        <label class="btn btn-outline btn-outline-dashed btn-active-light-primary"
                                            for="kt_charts_widget_3_2">Last Month</label>
                                        <input type="radio" class="btn-check" name="kt_charts_widget_3_options"
                                            value="3" id="kt_charts_widget_3_3">
                                        <label class="btn btn-outline btn-outline-dashed btn-active-light-primary"
                                            for="kt_charts_widget_3_3">YTD</label>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pt-6">
                                <div class="row">
                                    <div class="col-xl-8">
                                        <div id="kt_charts_widget_3" class="h-400px"></div>
                                    </div>
                                    <div class="col-xl-4">
                                        <div class="category-breakdown">
                                            <div class="category-item">
                                                <div class="d-flex align-items-center justify-content-between mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="symbol symbol-35px me-3">
                                                            <div class="symbol-label bg-light-primary">
                                                                <i class="fas fa-building text-primary fs-6"></i>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold text-gray-800 fs-7">Office Expenses
                                                            </div>
                                                            <div class="text-muted fs-8">Rent, utilities, maintenance
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="text-end">
                                                        <div class="fw-bold text-gray-800 fs-7">PKR 85,000</div>
                                                        <div class="text-muted fs-8">29.5%</div>
                                                    </div>
                                                </div>
                                                <div class="progress h-6px bg-light-primary">
                                                    <div class="progress-bar bg-primary" style="width: 29.5%"></div>
                                                </div>
                                            </div>

                                            <div class="category-item mt-6">
                                                <div class="d-flex align-items-center justify-content-between mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="symbol symbol-35px me-3">
                                                            <div class="symbol-label bg-light-success">
                                                                <i class="fas fa-car text-success fs-6"></i>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold text-gray-800 fs-7">Transportation
                                                            </div>
                                                            <div class="text-muted fs-8">Fuel, maintenance, travel
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="text-end">
                                                        <div class="fw-bold text-gray-800 fs-7">PKR 45,000</div>
                                                        <div class="text-muted fs-8">15.6%</div>
                                                    </div>
                                                </div>
                                                <div class="progress h-6px bg-light-success">
                                                    <div class="progress-bar bg-success" style="width: 15.6%"></div>
                                                </div>
                                            </div>

                                            <div class="category-item mt-6">
                                                <div class="d-flex align-items-center justify-content-between mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="symbol symbol-35px me-3">
                                                            <div class="symbol-label bg-light-warning">
                                                                <i class="fas fa-utensils text-warning fs-6"></i>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold text-gray-800 fs-7">Food & Dining</div>
                                                            <div class="text-muted fs-8">Meals, entertainment</div>
                                                        </div>
                                                    </div>
                                                    <div class="text-end">
                                                        <div class="fw-bold text-gray-800 fs-7">PKR 35,000</div>
                                                        <div class="text-muted fs-8">12.2%</div>
                                                    </div>
                                                </div>
                                                <div class="progress h-6px bg-light-warning">
                                                    <div class="progress-bar bg-warning" style="width: 12.2%"></div>
                                                </div>
                                            </div>

                                            <div class="category-item mt-6">
                                                <div class="d-flex align-items-center justify-content-between mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="symbol symbol-35px me-3">
                                                            <div class="symbol-label bg-light-danger">
                                                                <i class="fas fa-shopping-cart text-danger fs-6"></i>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold text-gray-800 fs-7">Supplies</div>
                                                            <div class="text-muted fs-8">Office supplies, equipment
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="text-end">
                                                        <div class="fw-bold text-gray-800 fs-7">PKR 28,000</div>
                                                        <div class="text-muted fs-8">9.7%</div>
                                                    </div>
                                                </div>
                                                <div class="progress h-6px bg-light-danger">
                                                    <div class="progress-bar bg-danger" style="width: 9.7%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Data Tables Row -->
                <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
                    <!-- Recent Transactions -->
                    <div class="col-xl-8">
                        <div class="card card-flush h-xl-100">
                            <div class="card-header pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold text-dark fs-3">
                                        <i class="fas fa-clock me-2 text-primary"></i>Recent Transactions
                                    </span>
                                    <span class="text-muted fw-semibold fs-7">Latest financial activities</span>
                                </h3>
                                <div class="card-toolbar">
                                    <div class="d-flex align-items-center gap-2">
                                        <select class="form-select form-select-sm" style="width: 140px;">
                                            <option>All Accounts</option>
                                            <option>HBL Current</option>
                                            <option>JazzCash</option>
                                            <option>Petty Cash</option>
                                        </select>
                                        <a href="{{ route('accountflow::transactions') }}"
                                            class="btn btn-sm btn-light-primary">
                                            <i class="fas fa-eye"></i>View All
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pt-2">
                                <div class="table-responsive">
                                    <table class="table table-row-dashed align-middle gs-0 gy-4 my-0">
                                        <thead>
                                            <tr class="fs-7 fw-bold text-gray-500 border-bottom-0">
                                                <th class="ps-0 min-w-200px">Transaction</th>
                                                <th class="min-w-120px">Category</th>
                                                <th class="min-w-120px">Amount</th>
                                                <th class="min-w-100px">Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recentTransactions as $tx)
                                                @php
                                                    $sign = ($tx->type == 'expense' || $tx->type == '2' || $tx->type == 2) ? '-' : '+';
                                                    $amountClass = $sign === '-' ? 'text-danger' : 'text-success';
                                                @endphp
                                                <tr>
                                                    <td class="ps-0">
                                                        <div class="d-flex align-items-center">
                                                            <div class="symbol symbol-30px me-5">
                                                                <div class="symbol-label bg-light-{{ $sign === '-' ? 'danger' : 'success' }}">
                                                                    <i class="fas {{ $sign === '-' ? 'fa-arrow-down text-danger' : 'fa-arrow-up text-success' }} fs-2"></i>
                                                                </div>
                                                            </div>
                                                            <div class="d-flex justify-content-start flex-column">
                                                                <a href="#" class="text-gray-800 fw-bold text-hover-primary fs-6">{{ Str::limit($tx->description ?? ($tx->category?->name ?? 'Transaction'), 60) }}</a>
                                                                <span class="text-muted fw-semibold d-block fs-7">{{ $tx->unique_id ?? '' }}</span>
                                                            </div>
                                                        </div>
                                                    </td>
 
                                                    <td>
                                                        <span class="badge badge-light-{{ $sign === '-' ? 'danger' : 'success' }}">{{ $tx->category?->name ?? '—' }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="{{ $amountClass }} fw-bold d-block fs-6">{{ $sign }}{{ config('accountflow.currency', 'PKR') }} {{ number_format($tx->amount, 2) }}</span>
                                                    </td>
                                                    <td>
                                <span class="text-gray-800 fw-bold d-block fs-7">{{ \Carbon\Carbon::parse($tx->date)->format('M d, Y') }}</span>
                                                    </td>
                                                   
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Enhanced Account Summary -->
                    <div class="col-xl-4">
                        <div class="card card-flush h-xl-100">
                            <div class="card-header pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold text-dark fs-3">
                                        <i class="fas fa-university me-2 text-primary"></i>Account Summary
                                    </span>
                                    <span class="text-muted fw-semibold fs-7">Current balances & activity</span>
                                </h3>
                                <div class="card-toolbar">
                                    <a href="{{ route('accountflow::accounts') }}" class="btn btn-sm btn-light-primary">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="card-body pt-2">
                                <div class="d-flex flex-column">
                                    @forelse($accounts as $index => $account)
                                        @php
                                            $colors = ['primary', 'success', 'warning'];
                                            $icons = ['fa-university', 'fa-mobile-alt', 'fa-money-bill'];
                                            $color = $colors[$index % 3] ?? 'info';
                                            $icon = $icons[$index % 3] ?? 'fa-bank';
                                            $totalBalance = collect($accounts)->sum('balance') ?: 1;
                                            $percentage = ($account['balance'] / $totalBalance) * 100;
                                        @endphp
                                        <div class="account-summary-item">
                                            <div class="d-flex align-items-center border-1 border-gray-200 border-dashed rounded p-4 {{ $index < count($accounts) - 1 ? 'mb-4' : '' }}">
                                                <div class="symbol symbol-50px me-4">
                                                    <div class="symbol-label bg-light-{{ $color }}">
                                                        <i class="fas {{ $icon }} text-{{ $color }} fs-2"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                                                        <div class="d-flex flex-column">
                                                            <div class="d-flex align-items-center mb-1">
                                                                <span class="text-gray-900 fw-bold fs-6 me-2">{{ $account['name'] ?? 'Account' }}</span>
                                                                @if($index === 0)
                                                                    <span class="badge badge-light-{{ $color }} fs-8">Primary</span>
                                                                @endif
                                                            </div>
                                                            <span class="text-muted fw-semibold fs-8">{{ config('accountflow.currency', 'PKR') }} Account</span>
                                                        </div>
                                                        <div class="d-flex flex-column text-end">
                                                            <span class="text-gray-900 fw-bold fs-5">{{ config('accountflow.currency', 'PKR') }} {{ number_format($account['balance'] ?? 0, 2) }}</span>
                                                            <span class="text-success fw-semibold fs-8">+{{ rand(1, 5) }}.{{ rand(0, 9) }}%</span>
                                                        </div>
                                                    </div>
                                                    <div class="progress h-6px bg-light-{{ $color }}">
                                                        <div class="progress-bar bg-{{ $color }}" style="width: {{ $percentage }}%"></div>
                                                    </div>
                                                    <div class="d-flex justify-content-between mt-2">
                                                        <span class="text-muted fs-8">Active</span>
                                                        <span class="text-muted fs-8">{{ round($percentage) }}% of total</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center py-5">
                                            <i class="fas fa-inbox text-muted fs-1 mb-3"></i>
                                            <p class="text-muted">No accounts found</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Analytics Row -->
                <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
                    <!-- Cash Flow Analysis -->
                    <div class="col-xl-6">
                        <div class="card card-flush h-xl-100">
                            <div class="card-header pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold text-dark fs-3">
                                        <i class="fas fa-water me-2 text-primary"></i>Cash Flow Analysis
                                    </span>
                                    <span class="text-muted fw-semibold fs-7">Monthly cash flow patterns</span>
                                </h3>
                            </div>
                            <div class="card-body pt-6">
                                <div id="cashFlowChart" class="h-300px"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Expense Categories -->
                    <div class="col-xl-6">
                        <div class="card card-flush h-xl-100">
                            <div class="card-header pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold text-dark fs-3">
                                        <i class="fas fa-fire me-2 text-primary"></i>Top Expense Categories
                                    </span>
                                    <span class="text-muted fw-semibold fs-7">This month's spending breakdown</span>
                                </h3>
                            </div>
                            <div class="card-body pt-6">
                                <div class="expense-categories">
                                    @forelse($topCategories as $index => $cat)
                                        @php
                                            $colors = ['primary', 'success', 'warning', 'danger', 'info'];
                                            $icons = ['fa-building', 'fa-car', 'fa-utensils', 'fa-shopping-cart', 'fa-lightbulb'];
                                            $color = $colors[$index % 5] ?? 'info';
                                            $icon = $icons[$index % 5] ?? 'fa-tag';
                                        @endphp
                                        <div class="expense-category-item d-flex align-items-center justify-content-between {{ $index < count($topCategories) - 1 ? 'mb-4' : '' }}">
                                            <div class="d-flex align-items-center">
                                                <div class="symbol symbol-40px me-3">
                                                    <div class="symbol-label bg-light-{{ $color }}">
                                                        <i class="fas {{ $icon }} text-{{ $color }}"></i>
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-gray-800">{{ $cat['name'] ?? 'Category' }}</div>
                                                    <div class="text-muted fs-7">{{ rand(5, 100) }} transactions</div>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <div class="fw-bold text-gray-800">{{ config('accountflow.currency', 'PKR') }} {{ number_format($cat['expense'] ?? 0, 2) }}</div>
                                                <div class="text-{{ $color }} fs-7">{{ $cat['pct'] ?? 0 }}%</div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center py-5">
                                            <i class="fas fa-inbox text-muted fs-1 mb-3"></i>
                                            <p class="text-muted">No expense categories found</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

               
            </div>
        </div>
    </div>




    @push('scripts')
        <script>
            // Shared chart instances for cleanup
            let chartInstances = [];

            function destroyCharts() {
                chartInstances.forEach(chart => {
                    if (chart && typeof chart.destroy === 'function') {
                        chart.destroy();
                    }
                });
                chartInstances = [];
            }

            function initChartsAndCounters() {
                // Destroy old charts
                destroyCharts();

                // Counter animation
                (function initCounters() {
                    const counters = document.querySelectorAll('.stats-value[data-count]');
                    counters.forEach(counter => {
                        const target = Number(counter.dataset.count) || 0;
                        let current = 0;
                        const duration = 900;
                        const stepTime = Math.max(16, Math.floor(duration / (target || 1)));
                        const inc = Math.max(1, Math.floor(target / (duration / stepTime)));
                        const t = setInterval(() => {
                            current += inc;
                            if (current >= target) {
                                counter.textContent = new Intl.NumberFormat().format(target);
                                clearInterval(t);
                                return;
                            }
                            counter.textContent = new Intl.NumberFormat().format(current);
                        }, stepTime);
                    });
                })();

                // Verify ApexCharts is loaded
                if (typeof ApexCharts === 'undefined') {
                    console.warn('ApexCharts not loaded yet');
                    return;
                }

                // Financial Trends - area chart (income vs expenses) - ENHANCED
                (function financialTrends() {
                    const el = document.querySelector('#kt_charts_widget_1');
                    if (!el) return;

                    const options = {
                        chart: { 
                            type: 'area', 
                            height: 350, 
                            toolbar: { show: false }, 
                            zoom: { enabled: false },
                            animations: {
                                enabled: true,
                                easing: 'easeinout',
                                speed: 800,
                                animateGradually: {
                                    enabled: true,
                                    delay: 150
                                }
                            }
                        },
                        series: [
                            { name: 'Income', data: @json($trends['income']) },
                            { name: 'Expenses', data: @json($trends['expenses']) }
                        ],
                        xaxis: { 
                            categories: @json($trends['labels']),
                            labels: {
                                style: {
                                    colors: '#a1a5b7',
                                    fontSize: '12px'
                                }
                            },
                            axisBorder: { show: false },
                            axisTicks: { show: false }
                        },
                        yaxis: {
                            labels: {
                                style: {
                                    colors: '#a1a5b7',
                                    fontSize: '12px'
                                },
                                formatter: v => 'PKR ' + new Intl.NumberFormat().format(v)
                            }
                        },
                        colors: ['#11998e', '#eb3349'],
                        stroke: { curve: 'smooth', width: 3 },
                        markers: {
                            size: 5,
                            colors: ['#11998e', '#eb3349'],
                            strokeColors: '#fff',
                            strokeWidth: 2,
                            hover: { size: 7 }
                        },
                        fill: { 
                            type: 'gradient', 
                            gradient: { 
                                shadeIntensity: 1, 
                                opacityFrom: 0.45, 
                                opacityTo: 0.05,
                                stops: [20, 100]
                            } 
                        },
                        grid: {
                            borderColor: '#f1f1f2',
                            strokeDashArray: 4,
                            yaxis: { lines: { show: true } },
                            padding: { top: 0, right: 0, bottom: 0, left: 10 }
                        },
                        tooltip: { 
                            shared: true, 
                            intersect: false,
                            theme: 'dark',
                            y: { formatter: v => 'PKR ' + new Intl.NumberFormat().format(v) },
                            marker: { show: true }
                        },
                        legend: { 
                            show: true, 
                            position: 'top',
                            horizontalAlign: 'left',
                            offsetX: 0,
                            labels: { colors: '#7e8299' },
                            markers: { width: 10, height: 10, radius: 10 }
                        }
                    };

                    const chart = new ApexCharts(el, options);
                    chart.render();
                    chartInstances.push(chart);
                })();

                // Account Distribution - donut chart - ENHANCED
                (function accountDistribution() {
                    const el = document.querySelector('#kt_charts_widget_2');
                    if (!el) return;

                    const options = {
                        chart: { 
                            type: 'donut', 
                            height: 320,
                            animations: {
                                enabled: true,
                                easing: 'easeinout',
                                speed: 800,
                                animateGradually: {
                                    enabled: true,
                                    delay: 150
                                }
                            }
                        },
                        series: @json($accountValues),
                        labels: @json($accountLabels),
                        colors: ['#667eea', '#11998e', '#ffc107', '#eb3349'],
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '65%',
                                    labels: {
                                        show: true,
                                        name: {
                                            show: true,
                                            fontSize: '14px',
                                            fontWeight: 600,
                                            color: '#7e8299'
                                        },
                                        value: {
                                            show: true,
                                            fontSize: '18px',
                                            fontWeight: 700,
                                            color: '#181c32',
                                            formatter: v => 'PKR ' + new Intl.NumberFormat().format(v)
                                        },
                                        total: {
                                            show: true,
                                            label: 'Total Balance',
                                            fontSize: '14px',
                                            fontWeight: 600,
                                            color: '#7e8299',
                                            formatter: function (w) {
                                                const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                                return 'PKR ' + new Intl.NumberFormat().format(total);
                                            }
                                        }
                                    }
                                }
                            }
                        },
                        dataLabels: {
                            enabled: true,
                            formatter: function(val) {
                                return val.toFixed(1) + '%';
                            },
                            style: {
                                fontSize: '12px',
                                fontWeight: 600
                            },
                            dropShadow: { enabled: false }
                        },
                        legend: { 
                            position: 'bottom',
                            fontSize: '13px',
                            fontWeight: 500,
                            labels: { colors: '#7e8299' },
                            markers: { width: 12, height: 12, radius: 12 },
                            itemMargin: { horizontal: 10, vertical: 5 }
                        },
                        tooltip: { 
                            theme: 'dark',
                            y: { formatter: v => 'PKR ' + new Intl.NumberFormat().format(v) } 
                        },
                        responsive: [
                            { 
                                breakpoint: 768, 
                                options: { 
                                    chart: { height: 260 }, 
                                    legend: { position: 'bottom' } 
                                } 
                            }
                        ]
                    };

                    const chart = new ApexCharts(el, options);
                    chart.render();
                    chartInstances.push(chart);
                })();

                // Category-wise bar chart (horizontal) - ENHANCED
                (function categoryBar() {
                    const el = document.querySelector('#kt_charts_widget_3');
                    if (!el) return;

                    const options = {
                        chart: { 
                            type: 'bar', 
                            height: 400, 
                            toolbar: { show: false },
                            animations: {
                                enabled: true,
                                easing: 'easeinout',
                                speed: 800,
                                animateGradually: {
                                    enabled: true,
                                    delay: 150
                                }
                            }
                        },
                        series: [{ name: 'Amount', data: @json($categoryValues) }],
                        plotOptions: { 
                            bar: { 
                                horizontal: true, 
                                barHeight: '60%',
                                borderRadius: 8,
                                distributed: true,
                                dataLabels: {
                                    position: 'bottom'
                                }
                            } 
                        },
                        dataLabels: { 
                            enabled: true,
                            textAnchor: 'start',
                            offsetX: 5,
                            formatter: v => 'PKR ' + new Intl.NumberFormat().format(v),
                            style: {
                                fontSize: '11px',
                                fontWeight: 600,
                                colors: ['#fff']
                            }
                        },
                        xaxis: { 
                            labels: { 
                                formatter: v => new Intl.NumberFormat().format(v),
                                style: {
                                    colors: '#a1a5b7',
                                    fontSize: '12px'
                                }
                            },
                            axisBorder: { show: false },
                            axisTicks: { show: false }
                        },
                        yaxis: { 
                            labels: { 
                                style: { 
                                    fontSize: '13px',
                                    fontWeight: 600,
                                    colors: '#181c32'
                                } 
                            } 
                        },
                        colors: ['#eb3349', '#f45c43', '#ff6f61', '#ff8573', '#ff9b85'],
                        grid: {
                            borderColor: '#f1f1f2',
                            strokeDashArray: 4,
                            xaxis: { lines: { show: true } },
                            yaxis: { lines: { show: false } },
                            padding: { top: 0, right: 20, bottom: 0, left: 10 }
                        },
                        tooltip: { 
                            theme: 'dark',
                            y: { formatter: v => 'PKR ' + new Intl.NumberFormat().format(v) } 
                        },
                        legend: { show: false }
                    };

                    const chart = new ApexCharts(el, options);
                    chart.render();
                    chartInstances.push(chart);
                })();

                // Cash Flow spark area - ENHANCED
                (function cashFlow() {
                    const el = document.querySelector('#cashFlowChart');
                    if (!el) return;

                    const labels = @json($trends['labels']);
                    const net = @json($trends['income']).map((inc, idx) => inc - @json($trends['expenses'])[idx]);
                    const hasNegative = net.some(v => v < 0);

                    const options = {
                        chart: { 
                            type: 'area', 
                            height: 300, 
                            sparkline: { enabled: true },
                            animations: {
                                enabled: true,
                                easing: 'easeinout',
                                speed: 800
                            }
                        },
                        series: [{ name: 'Net Flow', data: net }],
                        stroke: { curve: 'smooth', width: 3 },
                        fill: { 
                            type: 'gradient',
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.45,
                                opacityTo: 0.05,
                                stops: [0, 100]
                            }
                        },
                        colors: [hasNegative ? '#eb3349' : '#11998e'],
                        markers: {
                            size: 4,
                            colors: [hasNegative ? '#eb3349' : '#11998e'],
                            strokeColors: '#fff',
                            strokeWidth: 2,
                            hover: { size: 6 }
                        },
                        tooltip: { 
                            theme: 'dark',
                            x: { formatter: i => labels[i] }, 
                            y: { formatter: v => 'PKR ' + new Intl.NumberFormat().format(v) },
                            marker: { show: true }
                        }
                    };

                    const chart = new ApexCharts(el, options);
                    chart.render();
                    chartInstances.push(chart);
                })();

                // Card entrance animations
                (function animateCards() {
                    const cards = document.querySelectorAll('.card');
                    cards.forEach((card, i) => {
                        card.style.opacity = 0;
                        card.style.transform = 'translateY(10px)';
                        setTimeout(() => {
                            card.style.transition = 'all 400ms ease';
                            card.style.opacity = 1;
                            card.style.transform = 'translateY(0)';
                        }, i * 70);
                    });
                })();
            }

            // Initialize on both Livewire lifecycle events
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initChartsAndCounters);
            } else {
                initChartsAndCounters();
            }
            // Re-initialize after Livewire navigation
            if (typeof window.Livewire !== 'undefined') {
                document.addEventListener('livewire:navigated', initChartsAndCounters);
                document.addEventListener('livewire:init', initChartsAndCounters);
            }
        </script>
    @endpush
</div>