@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    $currency = $currency ?? config('accountflow.currency', 'PKR');
    $currencySymbol = $currencySymbol ?? config('accountflow.currency_symbols.' . $currency, $currency . ' ');

    $metrics = array_merge(
        [
            'total_balance' => 0,
            'period_income' => 0,
            'period_expenses' => 0,
            'account_health' => 0,
            'last_updated' => Carbon::now(),
            'six_month_income' => 0,
            'six_month_expenses' => 0,
        ],
        $metrics ?? [],
    );

    $accounts = $accounts ?? [];
    $topCategories = $topCategories ?? [];
    $cashflowMonths = $cashflowMonths ?? [];
    $previousMetrics = $previousMetrics ?? [];
    $recentTransactions = $recentTransactions ?? [];

    $netPosition = (float) $metrics['period_income'] - (float) $metrics['period_expenses'];

    $trends = [
        'labels' => collect($cashflowMonths)->pluck('label')->toArray(),
        'income' => collect($cashflowMonths)->pluck('income')->map(fn($v) => (float) $v)->toArray(),
        'expenses' => collect($cashflowMonths)->pluck('expense')->map(fn($v) => (float) $v)->toArray(),
    ];

    $accountLabels = collect($accounts)->pluck('name')->toArray();
    $accountValues = collect($accounts)->pluck('balance')->map(fn($v) => (float) $v)->toArray();
    $totalAccBal = max(1, array_sum($accountValues));

    $prevIncome = (float) ($previousMetrics['income'] ?? 0);
    $prevExpenses = (float) ($previousMetrics['expenses'] ?? 0);
    $incomeChange = $prevIncome > 0 ? (($metrics['period_income'] - $prevIncome) / $prevIncome) * 100 : null;
    $expenseChange = $prevExpenses > 0 ? (($metrics['period_expenses'] - $prevExpenses) / $prevExpenses) * 100 : null;

    $periods = [
        'this_month' => 'This Month',
        'last_month' => 'Last Month',
        'this_year' => 'This Year',
        'last_year' => 'Last Year',
        'all_time' => 'All Time',
    ];
@endphp

<div>
    @include(config('accountflow.view_path') . 'blades.dashboard-header')

    <div class="app-content flex-column-fluid py-6">
        <div class="app-container container-xxl">

            {{-- Period Selector --}}
            <div class="card card-flush mb-6">
                <div class="card-body py-4 px-6">
                    <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between">
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($periods as $key => $label)
                                <button type="button" wire:click.prevent="changePeriod('{{ $key }}')"
                                    class="btn btn-sm {{ ($selectedPeriod ?? 'this_month') === $key ? 'btn-primary' : 'btn-light' }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <input type="date" wire:model="customStartDate" class="form-control form-control-sm"
                                style="width:140px">
                            <span class="text-muted small">–</span>
                            <input type="date" wire:model="customEndDate" class="form-control form-control-sm"
                                style="width:140px">
                            <button type="button" wire:click="applyDateRange"
                                class="btn btn-sm btn-primary">Apply</button>
                        </div>
                    </div>
                    <div class="mt-3 d-flex align-items-center gap-3">
                        <span class="badge badge-light-primary fw-semibold">
                            {{ $periods[$selectedPeriod ?? 'this_month'] ?? 'Custom Range' }}
                        </span>
                        <span class="text-muted fs-7">
                            Net:
                            <strong class="{{ $netPosition >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $currencySymbol }}{{ number_format($netPosition, 2) }}
                            </strong>
                        </span>
                    </div>
                </div>
            </div>

            {{-- KPI Row --}}
            <div class="row g-5 mb-6">

                {{-- Total Balance --}}
                <div class="col-xl-3 col-md-6">
                    <div class="card card-flush h-100 border-top border-4 border-primary">
                        <div class="card-body p-6">
                            <div class="d-flex align-items-center justify-content-between mb-5">
                                <div class="symbol symbol-40px">
                                    <div class="symbol-label bg-light-primary">
                                        <i class="fas fa-wallet text-primary fs-4"></i>
                                    </div>
                                </div>
                                <span class="badge badge-light-primary fs-8">All Accounts</span>
                            </div>
                            <div class="text-muted fw-semibold fs-7 mb-1">Total Balance</div>
                            <div class="fw-bolder text-gray-900 fs-2hx lh-1">
                                <span
                                    class="fs-5 fw-semibold me-1">{{ $currencySymbol }}</span>{{ number_format($metrics['total_balance'], 0) }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Period Income --}}
                <div class="col-xl-3 col-md-6">
                    <div class="card card-flush h-100 border-top border-4 border-success">
                        <div class="card-body p-6">
                            <div class="d-flex align-items-center justify-content-between mb-5">
                                <div class="symbol symbol-40px">
                                    <div class="symbol-label bg-light-success">
                                        <i class="fas fa-arrow-trend-up text-success fs-4"></i>
                                    </div>
                                </div>
                                @if ($incomeChange !== null)
                                    <span
                                        class="badge {{ $incomeChange >= 0 ? 'badge-light-success' : 'badge-light-danger' }} fs-8">
                                        <i
                                            class="fas fa-arrow-{{ $incomeChange >= 0 ? 'up' : 'down' }} me-1"></i>{{ number_format(abs($incomeChange), 1) }}%
                                    </span>
                                @endif
                            </div>
                            <div class="text-muted fw-semibold fs-7 mb-1">Income</div>
                            <div class="fw-bolder text-gray-900 fs-2hx lh-1">
                                <span
                                    class="fs-5 fw-semibold me-1">{{ $currencySymbol }}</span>{{ number_format($metrics['period_income'], 0) }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Period Expenses --}}
                <div class="col-xl-3 col-md-6">
                    <div class="card card-flush h-100 border-top border-4 border-danger">
                        <div class="card-body p-6">
                            <div class="d-flex align-items-center justify-content-between mb-5">
                                <div class="symbol symbol-40px">
                                    <div class="symbol-label bg-light-danger">
                                        <i class="fas fa-arrow-trend-down text-danger fs-4"></i>
                                    </div>
                                </div>
                                @if ($expenseChange !== null)
                                    <span
                                        class="badge {{ $expenseChange <= 0 ? 'badge-light-success' : 'badge-light-danger' }} fs-8">
                                        <i
                                            class="fas fa-arrow-{{ $expenseChange >= 0 ? 'up' : 'down' }} me-1"></i>{{ number_format(abs($expenseChange), 1) }}%
                                    </span>
                                @endif
                            </div>
                            <div class="text-muted fw-semibold fs-7 mb-1">Expenses</div>
                            <div class="fw-bolder text-gray-900 fs-2hx lh-1">
                                <span
                                    class="fs-5 fw-semibold me-1">{{ $currencySymbol }}</span>{{ number_format($metrics['period_expenses'], 0) }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Net Position --}}
                <div class="col-xl-3 col-md-6">
                    <div
                        class="card card-flush h-100 border-top border-4 {{ $netPosition >= 0 ? 'border-info' : 'border-warning' }}">
                        <div class="card-body p-6">
                            <div class="d-flex align-items-center justify-content-between mb-5">
                                <div class="symbol symbol-40px">
                                    <div class="symbol-label bg-light-info">
                                        <i class="fas fa-scale-balanced text-info fs-4"></i>
                                    </div>
                                </div>
                                <span
                                    class="badge {{ $netPosition >= 0 ? 'badge-light-success' : 'badge-light-warning' }} fs-8">
                                    {{ $netPosition >= 0 ? 'Surplus' : 'Deficit' }}
                                </span>
                            </div>
                            <div class="text-muted fw-semibold fs-7 mb-1">Net Position</div>
                            <div
                                class="fw-bolder {{ $netPosition >= 0 ? 'text-success' : 'text-danger' }} fs-2hx lh-1">
                                @if ($netPosition < 0)
                                    <span class="fs-5 me-1">-</span>
                                @endif
                                <span
                                    class="fs-5 fw-semibold me-1">{{ $currencySymbol }}</span>{{ number_format(abs($netPosition), 0) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Charts Row --}}
            <div class="row g-5 mb-6">

                {{-- Financial Trends --}}
                <div class="col-xl-8">
                    <div class="card card-flush h-100">
                        <div class="card-header pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-dark">Financial Trends</span>
                                <span class="text-muted fw-semibold fs-7 mt-1">6-month income vs expenses</span>
                            </h3>
                        </div>
                        <div class="card-body pt-4 pb-4">
                            <div id="af_trends_chart" class="h-250px"></div>
                        </div>
                    </div>
                </div>

                {{-- Account Balances --}}
                <div class="col-xl-4">
                    <div class="card card-flush h-100">
                        <div class="card-header pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-dark">Account Balances</span>
                                <span class="text-muted fw-semibold fs-7 mt-1">Distribution by account</span>
                            </h3>
                            <div class="card-toolbar">
                                <a href="{{ route('accountflow::accounts') }}" class="btn btn-sm btn-icon btn-light">
                                    <i class="fas fa-arrow-up-right-from-square fs-7"></i>
                                </a>
                            </div>
                        </div>
                        <div class="card-body pt-2 pb-4">
                            <div id="af_accounts_chart" class="h-160px mb-4"></div>
                            @php $acColors = ['primary','success','warning','danger','info']; @endphp
                            @forelse($accounts as $ai => $acct)
                                @php
                                    $lc = $acColors[$ai % count($acColors)];
                                    $pct =
                                        $totalAccBal > 0
                                            ? round(100 * ((float) $acct['balance'] / $totalAccBal), 1)
                                            : 0;
                                @endphp
                                <div class="d-flex align-items-center {{ $ai > 0 ? 'mt-3' : '' }}">
                                    <div
                                        class="bullet w-8px h-8px rounded-2 bg-{{ $lc }} me-3 flex-shrink-0">
                                    </div>
                                    <div class="flex-grow-1 d-flex justify-content-between align-items-center">
                                        <span class="fw-semibold fs-7 text-gray-800">{{ $acct['name'] }}</span>
                                        <span class="text-muted fs-8">
                                            {{ $currencySymbol }}{{ number_format($acct['balance'], 0) }}
                                            <span class="text-gray-400 ms-1">({{ $pct }}%)</span>
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted fs-7">No accounts found.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- Data Row --}}
            <div class="row g-5 mb-6">

                {{-- Recent Transactions --}}
                <div class="col-xl-8">
                    <div class="card card-flush h-100">
                        <div class="card-header pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-dark">Recent Transactions</span>
                                <span class="text-muted fw-semibold fs-7 mt-1">Latest activities</span>
                            </h3>
                            <div class="card-toolbar">
                                <a href="{{ route('accountflow::transactions') }}"
                                    class="btn btn-sm btn-light-primary">
                                    View All
                                </a>
                            </div>
                        </div>
                        <div class="card-body pt-2">
                            <div class="table-responsive">
                                <table class="table table-row-dashed align-middle gs-0 gy-3 my-0">
                                    <thead>
                                        <tr class="fs-8 fw-semibold text-muted text-uppercase border-bottom-0">
                                            <th>Transaction</th>
                                            <th>Category</th>
                                            <th class="text-end">Amount</th>
                                            <th class="text-end">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentTransactions as $tx)
                                            @php
                                                $txArr = is_array($tx) ? $tx : (array) $tx;
                                                $txType = $txArr['type'] ?? '';
                                                $isExpense = in_array($txType, ['expense', '2', 2]);
                                                $amtClass = $isExpense ? 'text-danger' : 'text-success';
                                                $sign = $isExpense ? '−' : '+';
                                                $txCat = is_array($txArr['category'] ?? null)
                                                    ? $txArr['category']['name'] ?? '—'
                                                    : (is_object($txArr['category'] ?? null)
                                                        ? $txArr['category']->name ?? '—'
                                                        : '—');
                                                $txDesc = Str::limit($txArr['description'] ?? $txCat, 45);
                                                $txAmount = $txArr['amount'] ?? 0;
                                                $txDate = $txArr['date'] ?? ($txArr['created_at'] ?? now());
                                            @endphp
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="symbol symbol-30px me-3 flex-shrink-0">
                                                            <div
                                                                class="symbol-label bg-light-{{ $isExpense ? 'danger' : 'success' }}">
                                                                <i
                                                                    class="fas {{ $isExpense ? 'fa-minus' : 'fa-plus' }} text-{{ $isExpense ? 'danger' : 'success' }} fs-8"></i>
                                                            </div>
                                                        </div>
                                                        <span
                                                            class="fw-semibold text-gray-800 fs-7">{{ $txDesc }}</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge badge-light fs-8">{{ $txCat }}</span>
                                                </td>
                                                <td class="text-end">
                                                    <span class="{{ $amtClass }} fw-bold fs-7">
                                                        {{ $sign }}{{ $currencySymbol }}{{ number_format($txAmount, 2) }}
                                                    </span>
                                                </td>
                                                <td class="text-end text-muted fs-8">
                                                    {{ Carbon::parse($txDate)->format('d M Y') }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-8 text-muted">
                                                    No transactions for this period.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Top Expense Categories --}}
                <div class="col-xl-4">
                    <div class="card card-flush h-100">
                        <div class="card-header pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-dark">Top Categories</span>
                                <span class="text-muted fw-semibold fs-7 mt-1">By expense amount</span>
                            </h3>
                        </div>
                        <div class="card-body pt-2">
                            @php $catColors = ['primary','success','warning','danger','info']; @endphp
                            @forelse($topCategories as $ci => $cat)
                                @php
                                    $cc = $catColors[$ci % count($catColors)];
                                    $pct = $cat['pct'] ?? 0;
                                @endphp
                                <div class="{{ $ci > 0 ? 'mt-5' : '' }}">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span
                                            class="fw-semibold text-gray-800 fs-7">{{ $cat['name'] ?? 'Category' }}</span>
                                        <span class="fw-bold text-gray-700 fs-7">
                                            {{ $currencySymbol }}{{ number_format($cat['expense'] ?? 0, 0) }}
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1 h-6px bg-light-{{ $cc }}">
                                            <div class="progress-bar bg-{{ $cc }}"
                                                style="width: {{ $pct }}%"></div>
                                        </div>
                                        <span class="text-muted fs-8 w-30px text-end">{{ $pct }}%</span>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8">
                                    <i class="fas fa-tags text-muted fs-2x mb-3 d-block"></i>
                                    <span class="text-muted fs-7">No expense data for this period</span>
                                </div>
                            @endforelse
                        </div>
                        @if (!empty($topCategories))
                            <div class="card-footer pt-0 pb-5 px-6 border-0">
                                <a href="{{ route('accountflow::transactions') }}"
                                    class="btn btn-sm btn-light w-100">
                                    View All Transactions
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Quick Links --}}
            <div class="row g-5">
                <div class="col-12">
                    <div class="card card-flush">
                        <div class="card-body py-4 px-6">
                            <div class="d-flex flex-wrap gap-3 align-items-center">
                                <span class="fw-semibold text-muted fs-7 me-2">Quick Links:</span>
                                @featureEnabled('budgets')
                                <a href="{{ route('accountflow::budgets') }}" class="btn btn-sm btn-light-warning">
                                    <i class="fas fa-piggy-bank me-1"></i>Budgets
                                </a>
                                @endFeatureEnabled
                                @featureEnabled('planned_payments')
                                <a href="{{ route('accountflow::planned-payments') }}"
                                    class="btn btn-sm btn-light-info">
                                    <i class="fas fa-calendar-check me-1"></i>Planned Payments
                                </a>
                                @endFeatureEnabled
                                @featureEnabled('profit_loss')
                                <a href="{{ route('accountflow::report.profitLoss') }}"
                                    class="btn btn-sm btn-light-success">
                                    <i class="fas fa-chart-line me-1"></i>P&amp;L Report
                                </a>
                                @endFeatureEnabled
                                @featureEnabled('trial_balance')
                                <a href="{{ route('accountflow::report.trial-balance') }}"
                                    class="btn btn-sm btn-light-primary">
                                    <i class="fas fa-balance-scale me-1"></i>Trial Balance
                                </a>
                                @endFeatureEnabled
                                @featureEnabled('cashbook')
                                <a href="{{ route('accountflow::report.cashbook') }}" class="btn btn-sm btn-light">
                                    <i class="fas fa-book me-1"></i>Cashbook
                                </a>
                                @endFeatureEnabled
                                <a href="{{ route('accountflow::report.balance-sheet') }}" class="btn btn-sm btn-light-dark">
                                    <i class="fas fa-file-invoice me-1"></i>Balance Sheet
                                </a>
                                @featureEnabled('equity')
                                <a href="{{ route('accountflow::equity.partners') }}"
                                    class="btn btn-sm btn-light-primary">
                                    <i class="fas fa-users me-1"></i>Equity Partners
                                </a>
                                @endFeatureEnabled
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
        <script>
            (function() {
                'use strict';

                const sym = @json($currencySymbol);
                const fmt = v => sym + new Intl.NumberFormat().format(Math.round(v));
                let charts = [];

                function destroyAll() {
                    charts.forEach(c => c && typeof c.destroy === 'function' && c.destroy());
                    charts = [];
                }

                function init() {
                    destroyAll();

                    if (typeof ApexCharts === 'undefined') {
                        setTimeout(init, 300);
                        return;
                    }

                    // Financial Trends
                    const trendsEl = document.getElementById('af_trends_chart');
                    if (trendsEl) {
                        const c = new ApexCharts(trendsEl, {
                            chart: {
                                type: 'area',
                                height: 250,
                                toolbar: {
                                    show: false
                                },
                                zoom: {
                                    enabled: false
                                }
                            },
                            series: [{
                                    name: 'Income',
                                    data: @json($trends['income'])
                                },
                                {
                                    name: 'Expenses',
                                    data: @json($trends['expenses'])
                                },
                            ],
                            xaxis: {
                                categories: @json($trends['labels']),
                                labels: {
                                    style: {
                                        colors: '#a1a5b7',
                                        fontSize: '12px'
                                    }
                                },
                                axisBorder: {
                                    show: false
                                },
                                axisTicks: {
                                    show: false
                                },
                            },
                            yaxis: {
                                labels: {
                                    style: {
                                        colors: '#a1a5b7',
                                        fontSize: '12px'
                                    },
                                    formatter: fmt
                                }
                            },
                            colors: ['#22c55e', '#ef4444'],
                            stroke: {
                                curve: 'smooth',
                                width: 2
                            },
                            fill: {
                                type: 'gradient',
                                gradient: {
                                    opacityFrom: 0.3,
                                    opacityTo: 0.01
                                }
                            },
                            grid: {
                                borderColor: '#f3f4f6',
                                strokeDashArray: 4
                            },
                            tooltip: {
                                shared: true,
                                intersect: false,
                                y: {
                                    formatter: fmt
                                }
                            },
                            legend: {
                                position: 'top',
                                labels: {
                                    colors: '#6b7280'
                                }
                            },
                            markers: {
                                size: 4,
                                strokeColors: '#fff',
                                strokeWidth: 2
                            },
                            dataLabels: {
                                enabled: false
                            },
                        });
                        c.render();
                        charts.push(c);
                    }

                    // Account distribution donut
                    const acctEl = document.getElementById('af_accounts_chart');
                    if (acctEl && @json(count($accountValues)) > 0) {
                        const c = new ApexCharts(acctEl, {
                            chart: {
                                type: 'donut',
                                height: 160
                            },
                            series: @json($accountValues),
                            labels: @json($accountLabels),
                            colors: ['#6366f1', '#22c55e', '#f59e0b', '#ef4444', '#06b6d4'],
                            plotOptions: {
                                pie: {
                                    donut: {
                                        size: '60%',
                                        labels: {
                                            show: true,
                                            total: {
                                                show: true,
                                                label: 'Total',
                                                formatter: w => fmt(w.globals.seriesTotals.reduce((a, b) => a + b,
                                                    0)),
                                            },
                                        },
                                    },
                                },
                            },
                            dataLabels: {
                                enabled: false
                            },
                            legend: {
                                show: false
                            },
                            tooltip: {
                                y: {
                                    formatter: fmt
                                }
                            },
                        });
                        c.render();
                        charts.push(c);
                    }
                }

                document.readyState === 'loading' ?
                    document.addEventListener('DOMContentLoaded', init) :
                    init();

                document.addEventListener('livewire:navigated', init);
            })();
        </script>
    @endpush
</div>
