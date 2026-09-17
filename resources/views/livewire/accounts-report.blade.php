@php
    use Illuminate\Support\Carbon;

    $currencySymbol = $currencySymbol ?? config('accountflow.currency_symbols.' . config('accountflow.currency', 'PKR'), config('accountflow.currency', 'PKR') . ' ');
    $visibleParents = $parentCategories->filter(fn($p) => (($p->net ?? 0) != 0));
    $totalIncome = $parentCategories->sum('total_income');
    $totalExpense = $parentCategories->sum('total_expense');
@endphp

<div>
    <div class="page-header">
        <div class="page-header-body">
            <nav class="page-breadcrumb" aria-label="Breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('accountflow::dashboard') }}" wire:navigate>Accounts</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Summary</li>
                </ol>
            </nav>
            <div class="page-header-title"><h1>Financial Summary</h1></div>
            <p class="page-header-subtitle">{{ Carbon::parse($startDate)->format('d M Y') }} – {{ Carbon::parse($endDate)->format('d M Y') }}</p>
        </div>
        <div class="page-header-actions">
            @featureEnabled('profit_loss')
                <a href="{{ route('accountflow::report.profitLoss') }}" class="btn btn-sm btn-soft-success" wire:navigate><i data-lucide="trending-up"></i> P&amp;L</a>
            @endFeatureEnabled
            @featureEnabled('trial_balance')
                <a href="{{ route('accountflow::report.trial-balance') }}" class="btn btn-sm btn-soft-primary" wire:navigate><i data-lucide="columns-3"></i> Trial Balance</a>
            @endFeatureEnabled
            @featureEnabled('cashbook')
                <a href="{{ route('accountflow::report.cashbook') }}" class="btn btn-sm btn-soft-secondary" wire:navigate><i data-lucide="book-open"></i> Cashbook</a>
            @endFeatureEnabled
            <a href="{{ route('accountflow::report.balance-sheet') }}" class="btn btn-sm btn-soft-secondary" wire:navigate><i data-lucide="file-text"></i> Balance Sheet</a>
        </div>
    </div>

    {{-- Display any error messages --}}
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Loading indicator --}}
    <div wire:loading class="text-center my-3">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    {{-- KPI row --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-4">
            <div class="card stat-tile h-100"><div class="card-body">
                <div class="stat">
                    <div class="stat-head">
                        <div class="stat-label">Total Income</div>
                        <span class="icon-box icon-box-success"><i data-lucide="trending-up"></i></span>
                    </div>
                    <div class="stat-value">{{ $currencySymbol }}{{ number_format($totalIncome, 0) }}</div>
                    <div class="stat-meta"><span>This period</span></div>
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
                    <div class="stat-value">{{ $currencySymbol }}{{ number_format($totalExpense, 0) }}</div>
                    <div class="stat-meta"><span>This period</span></div>
                </div>
            </div></div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card stat-tile h-100"><div class="card-body">
                <div class="stat">
                    <div class="stat-head">
                        <div class="stat-label">Net Total</div>
                        <span class="icon-box {{ $totalAmount >= 0 ? 'icon-box-primary' : 'icon-box-warning' }}"><i data-lucide="scale"></i></span>
                    </div>
                    <div class="stat-value {{ $totalAmount >= 0 ? '' : 'text-danger' }}">
                        {{ $totalAmount < 0 ? '-' : '' }}{{ $currencySymbol }}{{ number_format(abs($totalAmount), 0) }}
                    </div>
                    <div class="stat-meta"><span class="badge badge-soft-{{ $totalAmount >= 0 ? 'success' : 'warning' }}">{{ $totalAmount >= 0 ? 'Surplus' : 'Deficit' }}</span></div>
                </div>
            </div></div>
        </div>
    </div>

    {{-- Category breakdown --}}
    <div class="card mb-4">
        <div class="card-header">
            <div><h2 class="card-title">Category Breakdown</h2><p class="card-subtitle">Income and expense by category</p></div>
            <div class="card-actions">
                <div class="input-group input-group-sm" style="width:220px">
                    <span class="input-group-text"><i data-lucide="calendar"></i></span>
                    <input type="text" class="form-control" id="datarange" wire:model="dateRange">
                </div>
            </div>
        </div>
        <div class="card-body">
            @if($visibleParents->count() == 0)
                <div class="alert alert-warning mb-0">
                    <h5><i data-lucide="alert-triangle"></i> No Data Found</h5>
                    <p>No transactions found for the selected date range.</p>
                    <small class="text-body-secondary">Try selecting a different date range or check if you have any transactions
                        recorded.</small>
                </div>
            @else
                <div class="accordion" id="accordionExample">
                    @foreach ($visibleParents as $index => $parent)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading-{{ $parent->id }}">
                                <button class="accordion-button collapsed p-2" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapse-{{ $parent->id }}" aria-expanded="false"
                                    aria-controls="collapse-{{ $parent->id }}">
                                    <div class="d-flex align-items-center w-100">
                                        @if ($parent->icon ?? false)
                                            <img src="{{ asset(config('accountflow.asset_path') . 'icons/accounts_icons/' . $parent->icon) }}"
                                                alt="" class="rounded-circle me-3" style="width:30px;height:30px;object-fit:cover;">
                                        @else
                                            <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center me-3"
                                                style="width:30px;height:30px;font-size:20px;">
                                                {{ strtoupper(substr($parent->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div class="flex-grow-1 fs-4 text-start">
                                            {{ $parent->name }}
                                        </div>
                                        <div class="me-1 text-end" style="margin-right:5px;">
                                            @php $pnet = $parent->net ?? 0; @endphp
                                            <div>
                                                <span class="badge badge-soft-{{ $pnet >= 0 ? 'success' : 'danger' }} font-mono">
                                                    {{ $pnet >= 0 ? '+' : '-' }} {{ $currencySymbol }}{{ number_format(abs($pnet), 2) }}
                                                </span>
                                            </div>
                                            <div class="text-size-sm text-body-secondary">
                                                Inc: {{ number_format($parent->total_income ?? 0, 2) }} • Exp: {{ number_format($parent->total_expense ?? 0, 2) }}
                                            </div>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse-{{ $parent->id }}" class="accordion-collapse collapse"
                                aria-labelledby="heading-{{ $parent->id }}" data-bs-parent="#accordionExample">
                                <div class="accordion-body mx-5">
                                    @php
                                        $childCategories = $categories->where('parent_id', $parent->id)->filter(fn($c) => (($c->net ?? 0) != 0));
                                    @endphp

                                    @if($childCategories->count() > 0)
                                        @foreach ($childCategories as $child)
                                            <div class="d-flex align-items-center border-bottom py-2">
                                                @if ($child->icon ?? false)
                                                    <img src="{{ asset(config('accountflow.asset_path') . 'icons/accounts_icons/' . $child->icon) }}"
                                                        alt="" class="rounded-circle me-2" style="width:20px;height:20px;object-fit:cover;">
                                                @else
                                                    <div class="rounded-circle bg-secondary text-white d-flex justify-content-center align-items-center me-2"
                                                        style="width:20px;height:20px;font-size:12px;">
                                                        {{ strtoupper(substr($child->name, 0, 1)) }}
                                                    </div>
                                                @endif
                                                <div class="flex-grow-1">
                                                    {{ $child->name }}
                                                </div>
                                                @php $cnet = $child->net ?? 0; @endphp
                                                <div class="text-end">
                                                    <span class="badge badge-soft-{{ $cnet >= 0 ? 'success' : 'danger' }} font-mono">
                                                        {{ $cnet >= 0 ? '+' : '-' }} {{ $currencySymbol }}{{ number_format(abs($cnet), 2) }}
                                                    </span>
                                                    <div class="text-size-sm text-body-secondary">Inc: {{ number_format($child->total_income ?? 0,2) }} • Exp: {{ number_format($child->total_expense ?? 0,2) }}</div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="text-body-secondary text-center py-3">
                                            <i data-lucide="info"></i> No subcategory transactions found for this period.
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="card-footer d-flex flex-wrap justify-content-between align-items-center">
            <small class="text-body-secondary">
                Period: {{ Carbon::parse($startDate)->format('M d, Y') }} -
                {{ Carbon::parse($endDate)->format('M d, Y') }}
            </small>
            <span class="badge badge-soft-primary font-mono">Total: {{ $currencySymbol }}{{ number_format($totalAmount, 2) }}</span>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function () {
            $('#datarange').daterangepicker({
                "showDropdowns": true,
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                "locale": {
                    "format": "MM/DD/YYYY",
                    "separator": " - ",
                    "applyLabel": "Apply",
                    "cancelLabel": "Cancel",
                    "fromLabel": "From",
                    "toLabel": "To",
                    "customRangeLabel": "Custom",
                    "weekLabel": "W",
                    "daysOfWeek": ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa"],
                    "monthNames": [
                        "January", "February", "March", "April", "May", "June",
                        "July", "August", "September", "October", "November", "December"
                    ],
                    "firstDay": 1
                },
                "alwaysShowCalendars": true,
                "startDate": moment().startOf('month'), // Fixed: Use current month instead of future date
                "endDate": moment().endOf('month'),     // Fixed: Use current month instead of future date
                "opens": "left"
            }, function (start, end, label) {
                @this.set('dateRange', start.format('MM/DD/YYYY') + ' - ' + end.format('MM/DD/YYYY'));
            });
        });
    </script>
@endpush
