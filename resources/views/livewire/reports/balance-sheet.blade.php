{{-- Balance Sheet Report --}}
@php
    use Illuminate\Support\Carbon;

    $currencySymbol = $currencySymbol ?? config('accountflow.currency', 'PKR') . ' ';
    $data           = $reportData ?? [];

    $accounts      = collect($data['accounts'] ?? []);
    $loans         = collect($data['loans'] ?? []);
    $partners      = collect($data['equity_partners'] ?? []);
    $totalAssets   = (float) ($data['total_assets'] ?? 0);
    $totalLiab     = (float) ($data['total_liabilities'] ?? 0);
    $totalPartEq   = (float) ($data['total_partner_equity'] ?? 0);
    $retainedEarn  = (float) ($data['retained_earnings'] ?? 0);
    $totalEquity   = (float) ($data['total_equity'] ?? 0);
    $totalLE       = (float) ($data['total_liabilities_and_equity'] ?? 0);
    $isBalanced    = (bool)  ($data['is_balanced'] ?? false);
    $diff          = abs($totalAssets - $totalLE);
@endphp

<div>
    <div class="page-header">
        <div class="page-header-body">
            <nav class="page-breadcrumb" aria-label="Breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('accountflow::dashboard') }}" wire:navigate>Accounts</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Balance Sheet</li>
                </ol>
            </nav>
            <div class="page-header-title"><h1>Balance Sheet</h1></div>
            <p class="page-header-subtitle">
                {{ $companyInfo['name'] ?? config('app.name') }}
                @if(!empty($asOfDate))
                    — as of {{ Carbon::parse($asOfDate)->format('d F Y') }}
                    @if($diff > 0)
                        <span class="badge badge-soft-danger ms-1"><i data-lucide="alert-triangle"></i> Out of balance by {{ $currencySymbol }}{{ number_format($diff, 2) }}</span>
                    @else
                        <span class="badge badge-soft-success ms-1"><i data-lucide="check-circle-2"></i> Balanced</span>
                    @endif
                @endif
            </p>
        </div>
        <div class="page-header-actions">
            <div class="input-group input-group-sm" style="width:170px">
                <span class="input-group-text"><i data-lucide="calendar"></i></span>
                <input type="date" wire:model.live="asOfDate" value="{{ $asOfDate }}" class="form-control">
            </div>
            <button type="button" onclick="window.print()" class="btn btn-sm btn-soft-secondary">
                <i data-lucide="printer"></i> Print
            </button>
        </div>
    </div>

    {{-- KPI Summary --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-4 col-md-4">
            <div class="card stat-tile h-100"><div class="card-body">
                <div class="stat">
                    <div class="stat-head">
                        <div class="stat-label">Total Assets</div>
                        <span class="icon-box icon-box-primary"><i data-lucide="building-2"></i></span>
                    </div>
                    <div class="stat-value">{{ $currencySymbol }}{{ number_format($totalAssets, 2) }}</div>
                    <div class="stat-meta"><span>What the business owns</span></div>
                </div>
            </div></div>
        </div>
        <div class="col-xl-4 col-md-4">
            <div class="card stat-tile h-100"><div class="card-body">
                <div class="stat">
                    <div class="stat-head">
                        <div class="stat-label">Total Liabilities</div>
                        <span class="icon-box icon-box-danger"><i data-lucide="banknote"></i></span>
                    </div>
                    <div class="stat-value">{{ $currencySymbol }}{{ number_format($totalLiab, 2) }}</div>
                    <div class="stat-meta"><span>What the business owes</span></div>
                </div>
            </div></div>
        </div>
        <div class="col-xl-4 col-md-4">
            <div class="card stat-tile h-100"><div class="card-body">
                <div class="stat">
                    <div class="stat-head">
                        <div class="stat-label">Total Equity</div>
                        <span class="icon-box icon-box-success"><i data-lucide="chart-pie"></i></span>
                    </div>
                    <div class="stat-value {{ $totalEquity >= 0 ? '' : 'text-danger' }}">{{ $currencySymbol }}{{ number_format($totalEquity, 2) }}</div>
                    <div class="stat-meta"><span>Partner equity + retained earnings</span></div>
                </div>
            </div></div>
        </div>
    </div>

    {{-- Main Balance Sheet --}}
    <div class="row g-3">

        {{-- Assets --}}
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <div>
                        <h2 class="card-title"><i data-lucide="building-2"></i> Assets</h2>
                        <p class="card-subtitle">What the business owns</p>
                    </div>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold text-body-secondary text-uppercase text-size-sm mb-3">Current Assets</h6>

                    <div class="list-divided mb-3">
                        @forelse($accounts as $acct)
                            <div class="d-flex align-items-center justify-content-between py-2">
                                <span class="text-size-sm">{{ $acct['name'] }}</span>
                                <span class="cell-numeric font-mono">{{ $currencySymbol }}{{ number_format($acct['balance'], 2) }}</span>
                            </div>
                        @empty
                            <div class="text-body-secondary text-size-sm py-4">No accounts found.</div>
                        @endforelse
                    </div>

                    <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                        <span class="fw-bold text-uppercase text-size-sm">Total Assets</span>
                        <span class="fw-bold text-primary cell-numeric font-mono">{{ $currencySymbol }}{{ number_format($totalAssets, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Liabilities + Equity --}}
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <div>
                        <h2 class="card-title"><i data-lucide="scale"></i> Liabilities &amp; Equity</h2>
                        <p class="card-subtitle">What the business owes and owns</p>
                    </div>
                </div>
                <div class="card-body">

                    {{-- Liabilities --}}
                    <h6 class="fw-bold text-body-secondary text-uppercase text-size-sm mb-3">Liabilities</h6>

                    <div class="list-divided mb-2">
                        @forelse($loans as $loan)
                            <div class="d-flex align-items-center justify-content-between py-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-size-sm">{{ $loan['name'] }}</span>
                                    <span class="badge badge-soft-secondary">{{ ucfirst(str_replace('_', ' ', $loan['loan_type'] ?? '')) }}</span>
                                </div>
                                <span class="text-danger cell-numeric font-mono">{{ $currencySymbol }}{{ number_format($loan['amount'], 2) }}</span>
                            </div>
                        @empty
                            <div class="text-body-secondary text-size-sm py-2">No outstanding loans.</div>
                        @endforelse
                    </div>

                    <div class="d-flex justify-content-between py-2 mb-4 px-3 rounded bg-danger-subtle">
                        <span class="fw-semibold text-danger text-size-sm">Total Liabilities</span>
                        <span class="fw-bold text-danger cell-numeric font-mono">{{ $currencySymbol }}{{ number_format($totalLiab, 2) }}</span>
                    </div>

                    {{-- Equity --}}
                    <h6 class="fw-bold text-body-secondary text-uppercase text-size-sm mb-3">Equity</h6>

                    <div class="list-divided mb-2">
                        @forelse($partners as $partner)
                            <div class="d-flex align-items-center justify-content-between py-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-size-sm">{{ $partner['name'] }}</span>
                                    <span class="badge badge-soft-primary">{{ number_format($partner['ownership_percentage'] ?? 0, 1) }}%</span>
                                </div>
                                <span class="cell-numeric font-mono">{{ $currencySymbol }}{{ number_format($partner['current_equity'], 2) }}</span>
                            </div>
                        @empty
                            <div class="text-body-secondary text-size-sm py-2">No equity partners found.</div>
                        @endforelse

                        <div class="d-flex align-items-center justify-content-between py-2">
                            <span class="text-size-sm">Retained Earnings</span>
                            <span class="{{ $retainedEarn >= 0 ? 'text-success' : 'text-warning' }} cell-numeric font-mono">
                                {{ $retainedEarn < 0 ? '(' : '' }}{{ $currencySymbol }}{{ number_format(abs($retainedEarn), 2) }}{{ $retainedEarn < 0 ? ')' : '' }}
                            </span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between py-2 mb-4 px-3 rounded bg-success-subtle">
                        <span class="fw-semibold text-success text-size-sm">Total Equity</span>
                        <span class="fw-bold {{ $totalEquity >= 0 ? 'text-success' : 'text-danger' }} cell-numeric font-mono">{{ $currencySymbol }}{{ number_format($totalEquity, 2) }}</span>
                    </div>

                    {{-- Total L+E --}}
                    <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                        <span class="fw-bold text-uppercase text-size-sm">Total Liabilities &amp; Equity</span>
                        <span class="fw-bold {{ $isBalanced ? 'text-success' : 'text-danger' }} cell-numeric font-mono">{{ $currencySymbol }}{{ number_format($totalLE, 2) }}</span>
                    </div>

                    @if(!$isBalanced && $diff > 0)
                        <div class="alert alert-danger mt-4 py-3 mb-0">
                            <i data-lucide="alert-triangle"></i>
                            Balance difference: {{ $currencySymbol }}{{ number_format($diff, 2) }}.
                            Assets should equal Liabilities + Equity.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="text-center text-body-secondary text-size-sm mt-4">
        Generated on {{ !empty($data['generated_at']) ? Carbon::parse($data['generated_at'])->format('d M Y, H:i') : now()->format('d M Y, H:i') }}
        — Balance Sheet as of {{ !empty($asOfDate) ? Carbon::parse($asOfDate)->format('d F Y') : 'Today' }}
    </div>
</div>
