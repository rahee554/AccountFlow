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
    <div class="page-header">
        <div class="page-header-body">
            <nav class="page-breadcrumb" aria-label="Breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                </ol>
            </nav>
            <div class="page-header-title"><h1>Accounts Dashboard</h1></div>
            <p class="page-header-subtitle">{{ $periods[$selectedPeriod ?? 'this_month'] ?? 'Custom range' }} — updated {{ Carbon::parse($metrics['last_updated'])->diffForHumans() }}</p>
        </div>
        <div class="page-header-actions">
            <div class="nav nav-segmented mbl-none" role="tablist" aria-label="Period">
                @foreach ($periods as $key => $label)
                    <button type="button" class="nav-link @if (($selectedPeriod ?? 'this_month') === $key) active @endif"
                        wire:click.prevent="changePeriod('{{ $key }}')">{{ $label }}</button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Custom date range + net summary --}}
    <div class="card mb-4">
        <div class="card-body d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div class="d-flex gap-2 align-items-center">
                <input type="date" wire:model="customStartDate" class="form-control form-control-sm" style="width:150px">
                <span class="text-body-secondary">–</span>
                <input type="date" wire:model="customEndDate" class="form-control form-control-sm" style="width:150px">
                <button type="button" wire:click="applyDateRange" class="btn btn-sm btn-primary">Apply</button>
            </div>
            <div class="text-size-sm text-body-secondary">
                Net this period:
                <strong class="{{ $netPosition >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ $currencySymbol }}{{ number_format($netPosition, 2) }}
                </strong>
            </div>
        </div>
    </div>

    {{-- KPI row --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card stat-tile h-100"><div class="card-body">
                <div class="stat">
                    <div class="stat-head">
                        <div class="stat-label">Total Balance</div>
                        <span class="icon-box icon-box-primary"><i data-lucide="wallet"></i></span>
                    </div>
                    <div class="stat-value">{{ $currencySymbol }}{{ number_format($metrics['total_balance'], 0) }}</div>
                    <div class="stat-meta"><span>All accounts</span></div>
                </div>
            </div></div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card stat-tile h-100"><div class="card-body">
                <div class="stat">
                    <div class="stat-head">
                        <div class="stat-label">Income</div>
                        <span class="icon-box icon-box-success"><i data-lucide="trending-up"></i></span>
                    </div>
                    <div class="stat-value">{{ $currencySymbol }}{{ number_format($metrics['period_income'], 0) }}</div>
                    <div class="stat-meta">
                        @if ($incomeChange !== null)
                            <span class="stat-delta {{ $incomeChange >= 0 ? 'stat-delta-positive' : 'stat-delta-negative' }}">
                                <i data-lucide="{{ $incomeChange >= 0 ? 'trending-up' : 'trending-down' }}"></i>{{ number_format(abs($incomeChange), 1) }}%
                            </span>
                            <span>vs last period</span>
                        @else
                            <span>This period</span>
                        @endif
                    </div>
                </div>
            </div></div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card stat-tile h-100"><div class="card-body">
                <div class="stat">
                    <div class="stat-head">
                        <div class="stat-label">Expenses</div>
                        <span class="icon-box icon-box-danger"><i data-lucide="trending-down"></i></span>
                    </div>
                    <div class="stat-value">{{ $currencySymbol }}{{ number_format($metrics['period_expenses'], 0) }}</div>
                    <div class="stat-meta">
                        @if ($expenseChange !== null)
                            <span class="stat-delta {{ $expenseChange <= 0 ? 'stat-delta-positive' : 'stat-delta-negative' }}">
                                <i data-lucide="{{ $expenseChange >= 0 ? 'trending-up' : 'trending-down' }}"></i>{{ number_format(abs($expenseChange), 1) }}%
                            </span>
                            <span>vs last period</span>
                        @else
                            <span>This period</span>
                        @endif
                    </div>
                </div>
            </div></div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card stat-tile h-100"><div class="card-body">
                <div class="stat">
                    <div class="stat-head">
                        <div class="stat-label">Net Position</div>
                        <span class="icon-box {{ $netPosition >= 0 ? 'icon-box-info' : 'icon-box-warning' }}"><i data-lucide="scale"></i></span>
                    </div>
                    <div class="stat-value {{ $netPosition >= 0 ? '' : 'text-danger' }}">
                        {{ $netPosition < 0 ? '-' : '' }}{{ $currencySymbol }}{{ number_format(abs($netPosition), 0) }}
                    </div>
                    <div class="stat-meta"><span class="badge {{ $netPosition >= 0 ? 'badge-soft-success' : 'badge-soft-warning' }}">{{ $netPosition >= 0 ? 'Surplus' : 'Deficit' }}</span></div>
                </div>
            </div></div>
        </div>
    </div>

    {{-- Charts row --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header">
                    <div><h2 class="card-title">Financial Trends</h2><p class="card-subtitle">6-month income vs expenses</p></div>
                </div>
                <div class="card-body">
                    <div id="af_trends_chart" style="height:250px"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header">
                    <div><h2 class="card-title">Account Balances</h2><p class="card-subtitle">Distribution by account</p></div>
                    <div class="card-actions">
                        <a href="{{ route('accountflow::accounts') }}" class="btn btn-ghost btn-sm" wire:navigate>
                            <i data-lucide="external-link"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div id="af_accounts_chart" style="height:160px" class="mb-3"></div>
                    @php $acColors = ['primary', 'success', 'warning', 'danger', 'info']; @endphp
                    <div class="list-divided">
                        @forelse($accounts as $ai => $acct)
                            @php
                                $lc = $acColors[$ai % count($acColors)];
                                $pct = $totalAccBal > 0 ? round(100 * ((float) $acct['balance'] / $totalAccBal), 1) : 0;
                            @endphp
                            <div class="d-flex align-items-center gap-2 py-1">
                                <span class="badge-dot badge-dot-{{ $lc }}"></span>
                                <span class="flex-grow-1 text-size-sm">{{ $acct['name'] }}</span>
                                <span class="text-size-sm text-body-secondary font-mono">{{ $currencySymbol }}{{ number_format($acct['balance'], 0) }} <span class="text-body-tertiary">({{ $pct }}%)</span></span>
                            </div>
                        @empty
                            <p class="text-body-secondary text-size-sm mb-0">No accounts found.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Data row --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header card-header-divided">
                    <div><h2 class="card-title">Recent Transactions</h2></div>
                    <div class="card-actions">
                        <a href="{{ route('accountflow::transactions') }}" class="btn btn-ghost btn-sm" wire:navigate>
                            View all<i data-lucide="chevron-right"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-flush table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">Transaction</th>
                                    <th scope="col">Category</th>
                                    <th scope="col" class="cell-numeric">Amount</th>
                                    <th scope="col" class="cell-numeric">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTransactions as $tx)
                                    @php
                                        $txArr = is_array($tx) ? $tx : (array) $tx;
                                        $txType = $txArr['type'] ?? '';
                                        $isExpense = in_array($txType, ['expense', '2', 2]);
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
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="icon-box icon-box-sm {{ $isExpense ? 'icon-box-danger' : 'icon-box-success' }}">
                                                    <i data-lucide="{{ $isExpense ? 'minus' : 'plus' }}"></i>
                                                </span>
                                                <span class="text-size-sm">{{ $txDesc }}</span>
                                            </div>
                                        </td>
                                        <td><span class="badge badge-soft-secondary">{{ $txCat }}</span></td>
                                        <td class="cell-numeric">
                                            <span class="font-mono {{ $isExpense ? 'text-danger' : 'text-success' }}">
                                                {{ $sign }}{{ $currencySymbol }}{{ number_format($txAmount, 2) }}
                                            </span>
                                        </td>
                                        <td class="cell-numeric text-body-secondary text-nowrap">{{ Carbon::parse($txDate)->format('d M Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-body-secondary">No transactions for this period.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header">
                    <div><h2 class="card-title">Top Categories</h2><p class="card-subtitle">By expense amount</p></div>
                </div>
                <div class="card-body">
                    @forelse($topCategories as $ci => $cat)
                        @php $pct = $cat['pct'] ?? 0; @endphp
                        <div class="progress-labelled {{ $ci > 0 ? 'mt-3' : '' }}">
                            <div class="progress-meta">
                                <span class="progress-label">{{ $cat['name'] ?? 'Category' }}</span>
                                <span class="progress-value">{{ $currencySymbol }}{{ number_format($cat['expense'] ?? 0, 0) }}</span>
                            </div>
                            <div class="progress progress-sm">
                                <div class="progress-bar" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i data-lucide="tag" class="text-body-secondary mb-2"></i>
                            <p class="text-body-secondary text-size-sm mb-0">No expense data for this period.</p>
                        </div>
                    @endforelse
                </div>
                @if (!empty($topCategories))
                    <div class="card-footer">
                        <a href="{{ route('accountflow::transactions') }}" class="btn btn-ghost btn-sm w-100" wire:navigate>View all transactions</a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Quick links --}}
    <div class="card">
        <div class="card-body d-flex flex-wrap gap-2 align-items-center">
            <span class="text-body-secondary text-size-sm me-2">Quick links:</span>
            @featureEnabled('budgets')
                <a href="{{ route('accountflow::budgets') }}" class="btn btn-sm btn-soft-warning" wire:navigate><i data-lucide="chart-pie"></i>Budgets</a>
            @endFeatureEnabled
            @featureEnabled('planned_payments')
                <a href="{{ route('accountflow::planned-payments') }}" class="btn btn-sm btn-soft-info" wire:navigate><i data-lucide="calendar-clock"></i>Planned Payments</a>
            @endFeatureEnabled
            @featureEnabled('profit_loss')
                <a href="{{ route('accountflow::report.profitLoss') }}" class="btn btn-sm btn-soft-success" wire:navigate><i data-lucide="trending-up"></i>P&amp;L Report</a>
            @endFeatureEnabled
            @featureEnabled('trial_balance')
                <a href="{{ route('accountflow::report.trial-balance') }}" class="btn btn-sm btn-soft-primary" wire:navigate><i data-lucide="columns-3"></i>Trial Balance</a>
            @endFeatureEnabled
            @featureEnabled('cashbook')
                <a href="{{ route('accountflow::report.cashbook') }}" class="btn btn-sm btn-soft-secondary" wire:navigate><i data-lucide="book-open"></i>Cashbook</a>
            @endFeatureEnabled
            <a href="{{ route('accountflow::report.balance-sheet') }}" class="btn btn-sm btn-soft-secondary" wire:navigate><i data-lucide="file-text"></i>Balance Sheet</a>
            @featureEnabled('equity')
                <a href="{{ route('accountflow::equity.partners') }}" class="btn btn-sm btn-soft-primary" wire:navigate><i data-lucide="users"></i>Equity Partners</a>
            @endFeatureEnabled
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

                /**
                 * Colours, fonts and grid/tooltip chrome all come from
                 * window.AccountFlowCharts (charts-theme.js, a plain-script port of
                 * ui-flow-admin's own charts/theme.js token bridge) rather than
                 * hardcoded hex — so these charts use the SAME --uf-* custom
                 * properties as the rest of the shell and follow a live dark-mode
                 * or accent change instead of staying stuck on their first-paint
                 * colours.
                 */
                function buildTrendsOptions() {
                    const t = window.AccountFlowCharts.tokens();
                    const base = window.AccountFlowCharts.baseOptions();

                    return {
                        ...base,
                        chart: {
                            ...base.chart,
                            type: 'area',
                            height: 250,
                        },
                        series: [
                            { name: 'Income', data: @json($trends['income']) },
                            { name: 'Expenses', data: @json($trends['expenses']) },
                        ],
                        colors: [t.success, t.danger],
                        xaxis: {
                            ...base.xaxis,
                            categories: @json($trends['labels']),
                        },
                        yaxis: {
                            ...base.yaxis,
                            labels: { ...base.yaxis.labels, formatter: fmt },
                        },
                        stroke: { ...base.stroke, curve: 'smooth', width: 2 },
                        fill: {
                            type: 'gradient',
                            gradient: { opacityFrom: 0.3, opacityTo: 0.01 },
                        },
                        tooltip: { ...base.tooltip, shared: true, intersect: false, y: { formatter: fmt } },
                        legend: { ...base.legend, position: 'top' },
                        markers: { size: 4, strokeWidth: 2 },
                    };
                }

                function buildAccountsOptions() {
                    const t = window.AccountFlowCharts.tokens();
                    const base = window.AccountFlowCharts.baseOptions();

                    return {
                        ...base,
                        chart: { ...base.chart, type: 'donut', height: 160 },
                        series: @json($accountValues),
                        labels: @json($accountLabels),
                        colors: window.AccountFlowCharts.palette(),
                        stroke: { width: 2, colors: [t.surface] },
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '60%',
                                    labels: {
                                        show: true,
                                        total: {
                                            show: true,
                                            label: 'Total',
                                            color: t.text,
                                            formatter: w => fmt(w.globals.seriesTotals.reduce((a, b) => a + b, 0)),
                                        },
                                        value: { color: t.text },
                                    },
                                },
                            },
                        },
                        legend: { show: false },
                        tooltip: { ...base.tooltip, y: { formatter: fmt } },
                    };
                }

                function init() {
                    destroyAll();

                    if (typeof ApexCharts === 'undefined' || !window.AccountFlowCharts) {
                        setTimeout(init, 300);
                        return;
                    }

                    const trendsEl = document.getElementById('af_trends_chart');
                    if (trendsEl) {
                        const c = new ApexCharts(trendsEl, buildTrendsOptions());
                        c.render();
                        window.AccountFlowCharts.registerChart(c, buildTrendsOptions);
                        charts.push(c);
                    }

                    const acctEl = document.getElementById('af_accounts_chart');
                    if (acctEl && @json(count($accountValues)) > 0) {
                        const c = new ApexCharts(acctEl, buildAccountsOptions());
                        c.render();
                        window.AccountFlowCharts.registerChart(c, buildAccountsOptions);
                        charts.push(c);
                    }

                    if (window.ArtflowAdmin) window.ArtflowAdmin.renderIcons();
                }

                document.readyState === 'loading' ?
                    document.addEventListener('DOMContentLoaded', init) :
                    init();

                document.addEventListener('livewire:navigated', init);
            })();
        </script>
    @endpush
</div>
