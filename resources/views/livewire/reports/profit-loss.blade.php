@php
    use Illuminate\Support\Carbon;

    $currencySymbol = config('accountflow.currency_symbols.' . config('accountflow.currency', 'PKR'), config('accountflow.currency', 'PKR') . ' ');
@endphp

<div>
    <div class="page-header d-print-none">
        <div class="page-header-body">
            <nav class="page-breadcrumb" aria-label="Breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('accountflow::dashboard') }}" wire:navigate>Accounts</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Profit &amp; Loss</li>
                </ol>
            </nav>
            <div class="page-header-title"><h1>Profit &amp; Loss Report</h1></div>
            <p class="page-header-subtitle">For the period {{ Carbon::parse($startDate)->format('F d, Y') }} - {{ Carbon::parse($endDate)->format('F d, Y') }}</p>
        </div>
        <div class="page-header-actions">
            <div class="input-group input-group-sm" style="width:220px">
                <span class="input-group-text"><i data-lucide="calendar"></i></span>
                <input type="text" class="form-control" id="daterange" wire:model="dateRange" placeholder="Select date range">
            </div>
            <button class="btn btn-sm btn-soft-secondary" wire:click="loadReportData">
                <i data-lucide="refresh-cw"></i> Refresh
            </button>
            <button class="btn btn-sm btn-primary" wire:click="printReport">
                <i data-lucide="printer"></i> Print
            </button>
        </div>
    </div>

    {{-- Loading Indicator --}}
    <div wire:loading class="text-center py-5 d-print-none">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <div class="mt-3">
            <h5 class="text-body-secondary">Generating Financial Report...</h5>
            <small class="text-body-secondary">Please wait while we compile your data</small>
        </div>
    </div>

    <div wire:loading.remove>
        {{-- Report Header (print-friendly) --}}
        <div class="card mb-4">
            <div class="card-body">
                <div class="fw-bold fs-4">{{ $companyInfo['name'] }}</div>
                <div class="text-body-secondary text-size-sm">
                    <div><i data-lucide="map-pin"></i> {{ $companyInfo['address'] }}</div>
                    <div>{{ $companyInfo['city'] }}</div>
                    <div>
                        <i data-lucide="phone"></i> {{ $companyInfo['phone'] }} |
                        <i data-lucide="mail"></i> {{ $companyInfo['email'] }}
                    </div>
                </div>
            </div>
        </div>

        {{-- KPI Summary --}}
        <div class="row g-3 mb-4 d-print-none">
            <div class="col-6 col-xl-4">
                <div class="card stat-tile h-100"><div class="card-body">
                    <div class="stat">
                        <div class="stat-head">
                            <div class="stat-label">Total Revenue</div>
                            <span class="icon-box icon-box-success"><i data-lucide="trending-up"></i></span>
                        </div>
                        <div class="stat-value">{{ $currencySymbol }}{{ number_format($reportData['total_revenue'], 2) }}</div>
                    </div>
                </div></div>
            </div>
            <div class="col-6 col-xl-4">
                <div class="card stat-tile h-100"><div class="card-body">
                    <div class="stat">
                        <div class="stat-head">
                            <div class="stat-label">Total Expenses</div>
                            <span class="icon-box icon-box-danger"><i data-lucide="trending-down"></i></span>
                        </div>
                        <div class="stat-value">{{ $currencySymbol }}{{ number_format($reportData['total_expenses'], 2) }}</div>
                    </div>
                </div></div>
            </div>
            <div class="col-12 col-xl-4">
                <div class="card stat-tile h-100"><div class="card-body">
                    <div class="stat">
                        <div class="stat-head">
                            <div class="stat-label">Net {{ $reportData['net_income'] >= 0 ? 'Income' : 'Loss' }}</div>
                            <span class="icon-box {{ $reportData['net_income'] >= 0 ? 'icon-box-primary' : 'icon-box-warning' }}"><i data-lucide="scale"></i></span>
                        </div>
                        <div class="stat-value {{ $reportData['net_income'] >= 0 ? '' : 'text-danger' }}">
                            {{ $currencySymbol }}{{ number_format(abs($reportData['net_income']), 2) }}
                        </div>
                    </div>
                </div></div>
            </div>
        </div>

        {{-- REVENUE SECTION --}}
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="card-title"><i data-lucide="arrow-up"></i> Revenue</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-flush mb-0">
                        <tbody>
                            @if($reportData['revenue']->count() > 0)
                                @foreach($reportData['revenue'] as $revenueCategory)
                                    <tr>
                                        <td>{{ $revenueCategory->name }}</td>
                                        <td class="cell-numeric font-mono">{{ $currencySymbol }}{{ number_format($revenueCategory->total_amount, 2) }}</td>
                                    </tr>

                                    @php
                                        $subCategories = $reportData['categories']->where('parent_id', $revenueCategory->id)->where('total_amount', '>', 0);
                                    @endphp
                                    @if($subCategories->count() > 0)
                                        @foreach($subCategories as $subCategory)
                                            <tr>
                                                <td class="ps-5 text-body-secondary text-size-sm">{{ $subCategory->name }}</td>
                                                <td class="cell-numeric font-mono text-body-secondary text-size-sm">{{ $currencySymbol }}{{ number_format($subCategory->total_amount, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                @endforeach
                                <tr class="table-active">
                                    <td class="fw-bold">TOTAL REVENUE</td>
                                    <td class="cell-numeric font-mono fw-bold">{{ $currencySymbol }}{{ number_format($reportData['total_revenue'], 2) }}</td>
                                </tr>
                            @else
                                <tr>
                                    <td colspan="2" class="text-center text-body-secondary py-4">
                                        <i data-lucide="info"></i> No revenue recorded for this period
                                    </td>
                                </tr>
                                <tr class="table-active">
                                    <td class="fw-bold">TOTAL REVENUE</td>
                                    <td class="cell-numeric font-mono fw-bold">{{ $currencySymbol }}0.00</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- EXPENSES SECTION --}}
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="card-title"><i data-lucide="arrow-down"></i> Expenses</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-flush mb-0">
                        <tbody>
                            @if($reportData['expenses']->count() > 0)
                                @foreach($reportData['expenses'] as $expenseCategory)
                                    <tr>
                                        <td>{{ $expenseCategory->name }}</td>
                                        <td class="cell-numeric font-mono">{{ $currencySymbol }}{{ number_format(abs($expenseCategory->total_amount), 2) }}</td>
                                    </tr>

                                    @php
                                        $subCategories = $reportData['categories']->where('parent_id', $expenseCategory->id)->where('total_amount', '<', 0);
                                    @endphp
                                    @if($subCategories->count() > 0)
                                        @foreach($subCategories as $subCategory)
                                            <tr>
                                                <td class="ps-5 text-body-secondary text-size-sm">{{ $subCategory->name }}</td>
                                                <td class="cell-numeric font-mono text-body-secondary text-size-sm">{{ $currencySymbol }}{{ number_format(abs($subCategory->total_amount), 2) }}</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                @endforeach
                                <tr class="table-active">
                                    <td class="fw-bold">TOTAL EXPENSES</td>
                                    <td class="cell-numeric font-mono fw-bold">{{ $currencySymbol }}{{ number_format($reportData['total_expenses'], 2) }}</td>
                                </tr>
                            @else
                                <tr>
                                    <td colspan="2" class="text-center text-body-secondary py-4">
                                        <i data-lucide="info"></i> No expenses recorded for this period
                                    </td>
                                </tr>
                                <tr class="table-active">
                                    <td class="fw-bold">TOTAL EXPENSES</td>
                                    <td class="cell-numeric font-mono fw-bold">{{ $currencySymbol }}0.00</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- NET INCOME SECTION --}}
        <div class="card mb-4">
            <div class="card-body d-flex justify-content-between align-items-center">
                <span class="fw-bold text-uppercase"><i data-lucide="trophy"></i> Net {{ $reportData['net_income'] >= 0 ? 'Income' : 'Loss' }}</span>
                <span class="fs-4 fw-bold {{ $reportData['net_income'] >= 0 ? 'text-success' : 'text-danger' }} font-mono">
                    {{ $currencySymbol }}{{ number_format(abs($reportData['net_income']), 2) }}
                </span>
            </div>
        </div>

        {{-- Report Footer --}}
        <div class="card">
            <div class="card-body">
                <div class="row text-size-sm text-body-secondary">
                    <div class="col-6">
                        <div><i data-lucide="user"></i> <strong>Generated by:</strong> {{ auth()->user()->name ?? 'System Administrator' }}</div>
                        <div><i data-lucide="clock"></i> <strong>Generated on:</strong> {{ now()->format('F d, Y \a\t H:i A') }}</div>
                    </div>
                    <div class="col-6 text-md-end">
                        <div><strong>Document:</strong> P&amp;L Statement</div>
                        <div><strong>Report Date:</strong> {{ now()->format('Y-m-d') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
