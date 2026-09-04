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
    @include(config('accountflow.view_path') . 'blades.dashboard-header')

    <div class="app-content flex-column-fluid py-6">
        <div class="app-container container-xxl">

            {{-- Header Controls --}}
            <div class="card card-flush mb-6">
                <div class="card-body py-4 px-6">
                    <div class="d-flex flex-wrap gap-4 align-items-center justify-content-between">
                        <div>
                            <h2 class="fw-bold text-dark mb-1">Balance Sheet</h2>
                            <span class="text-muted fs-7">{{ $companyInfo['name'] ?? config('app.name') }}</span>
                        </div>
                        <div class="d-flex gap-3 align-items-center">
                            <div>
                                <label class="fw-semibold fs-7 text-muted me-2">As of Date:</label>
                                <input type="date" wire:model.live="asOfDate"
                                       value="{{ $asOfDate }}"
                                       class="form-control form-control-sm d-inline-block"
                                       style="width:160px">
                            </div>
                            <button type="button" onclick="window.print()" class="btn btn-sm btn-light-primary">
                                <i class="fas fa-print me-1"></i>Print
                            </button>
                        </div>
                    </div>
                    @if(!empty($asOfDate))
                        <div class="mt-3">
                            <span class="badge badge-light-info">
                                As of {{ Carbon::parse($asOfDate)->format('d F Y') }}
                            </span>
                            @if($diff > 0)
                                <span class="badge badge-light-danger ms-2">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    Out of balance by {{ $currencySymbol }}{{ number_format($diff, 2) }}
                                </span>
                            @else
                                <span class="badge badge-light-success ms-2">
                                    <i class="fas fa-check-circle me-1"></i>Balanced
                                </span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- KPI Summary --}}
            <div class="row g-5 mb-6">
                <div class="col-xl-4 col-md-4">
                    <div class="card card-flush h-100 border-top border-4 border-primary">
                        <div class="card-body p-5">
                            <div class="symbol symbol-40px mb-4">
                                <div class="symbol-label bg-light-primary">
                                    <i class="fas fa-building text-primary fs-4"></i>
                                </div>
                            </div>
                            <div class="text-muted fw-semibold fs-7 mb-1">Total Assets</div>
                            <div class="fw-bolder text-gray-900 fs-2hx lh-1">
                                <span class="fs-5 fw-semibold me-1">{{ $currencySymbol }}</span>{{ number_format($totalAssets, 2) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-4">
                    <div class="card card-flush h-100 border-top border-4 border-danger">
                        <div class="card-body p-5">
                            <div class="symbol symbol-40px mb-4">
                                <div class="symbol-label bg-light-danger">
                                    <i class="fas fa-hand-holding-usd text-danger fs-4"></i>
                                </div>
                            </div>
                            <div class="text-muted fw-semibold fs-7 mb-1">Total Liabilities</div>
                            <div class="fw-bolder text-gray-900 fs-2hx lh-1">
                                <span class="fs-5 fw-semibold me-1">{{ $currencySymbol }}</span>{{ number_format($totalLiab, 2) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-md-4">
                    <div class="card card-flush h-100 border-top border-4 border-success">
                        <div class="card-body p-5">
                            <div class="symbol symbol-40px mb-4">
                                <div class="symbol-label bg-light-success">
                                    <i class="fas fa-chart-pie text-success fs-4"></i>
                                </div>
                            </div>
                            <div class="text-muted fw-semibold fs-7 mb-1">Total Equity</div>
                            <div class="fw-bolder {{ $totalEquity >= 0 ? 'text-success' : 'text-danger' }} fs-2hx lh-1">
                                <span class="fs-5 fw-semibold me-1">{{ $currencySymbol }}</span>{{ number_format($totalEquity, 2) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Main Balance Sheet --}}
            <div class="row g-5">

                {{-- Assets --}}
                <div class="col-xl-6">
                    <div class="card card-flush h-100">
                        <div class="card-header pt-5 border-bottom">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-dark">
                                    <i class="fas fa-building me-2 text-primary"></i>ASSETS
                                </span>
                                <span class="text-muted fw-semibold fs-7 mt-1">What the business owns</span>
                            </h3>
                        </div>
                        <div class="card-body pt-4">
                            <h6 class="fw-bold text-gray-700 text-uppercase fs-8 mb-3">Current Assets</h6>

                            @forelse($accounts as $acct)
                                <div class="d-flex align-items-center justify-content-between py-2 border-bottom border-gray-100">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bullet w-6px h-6px rounded-2 bg-primary"></div>
                                        <span class="fw-semibold text-gray-800 fs-7">{{ $acct['name'] }}</span>
                                    </div>
                                    <span class="fw-bold text-gray-700 fs-7">{{ $currencySymbol }}{{ number_format($acct['balance'], 2) }}</span>
                                </div>
                            @empty
                                <div class="text-muted fs-7 py-4">No accounts found.</div>
                            @endforelse

                            <div class="d-flex align-items-center justify-content-between mt-4 pt-4 border-top border-2 border-primary">
                                <span class="fw-bolder text-dark fs-6 text-uppercase">Total Assets</span>
                                <span class="fw-bolder text-primary fs-5">{{ $currencySymbol }}{{ number_format($totalAssets, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Liabilities + Equity --}}
                <div class="col-xl-6">
                    <div class="card card-flush h-100">
                        <div class="card-header pt-5 border-bottom">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-dark">
                                    <i class="fas fa-balance-scale me-2 text-danger"></i>LIABILITIES &amp; EQUITY
                                </span>
                                <span class="text-muted fw-semibold fs-7 mt-1">What the business owes and owns</span>
                            </h3>
                        </div>
                        <div class="card-body pt-4">

                            {{-- Liabilities --}}
                            <h6 class="fw-bold text-gray-700 text-uppercase fs-8 mb-3">Liabilities</h6>

                            @forelse($loans as $loan)
                                <div class="d-flex align-items-center justify-content-between py-2 border-bottom border-gray-100">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bullet w-6px h-6px rounded-2 bg-danger"></div>
                                        <span class="fw-semibold text-gray-800 fs-7">{{ $loan['name'] }}</span>
                                        <span class="badge badge-light-secondary fs-9">{{ ucfirst(str_replace('_', ' ', $loan['loan_type'] ?? '')) }}</span>
                                    </div>
                                    <span class="fw-bold text-danger fs-7">{{ $currencySymbol }}{{ number_format($loan['amount'], 2) }}</span>
                                </div>
                            @empty
                                <div class="text-muted fs-7 py-2">No outstanding loans.</div>
                            @endforelse

                            <div class="d-flex justify-content-between py-2 mt-2 bg-light-danger rounded px-3">
                                <span class="fw-semibold text-danger fs-7">Total Liabilities</span>
                                <span class="fw-bolder text-danger fs-7">{{ $currencySymbol }}{{ number_format($totalLiab, 2) }}</span>
                            </div>

                            {{-- Equity --}}
                            <h6 class="fw-bold text-gray-700 text-uppercase fs-8 mt-6 mb-3">Equity</h6>

                            @forelse($partners as $partner)
                                <div class="d-flex align-items-center justify-content-between py-2 border-bottom border-gray-100">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bullet w-6px h-6px rounded-2 bg-success"></div>
                                        <span class="fw-semibold text-gray-800 fs-7">{{ $partner['name'] }}</span>
                                        <span class="badge badge-light-info fs-9">{{ number_format($partner['ownership_percentage'] ?? 0, 1) }}%</span>
                                    </div>
                                    <span class="fw-bold text-gray-700 fs-7">{{ $currencySymbol }}{{ number_format($partner['current_equity'], 2) }}</span>
                                </div>
                            @empty
                                <div class="text-muted fs-7 py-2">No equity partners found.</div>
                            @endforelse

                            <div class="d-flex align-items-center justify-content-between py-2 border-bottom border-gray-100">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bullet w-6px h-6px rounded-2 {{ $retainedEarn >= 0 ? 'bg-success' : 'bg-warning' }}"></div>
                                    <span class="fw-semibold text-gray-800 fs-7">Retained Earnings</span>
                                </div>
                                <span class="fw-bold {{ $retainedEarn >= 0 ? 'text-success' : 'text-warning' }} fs-7">
                                    {{ $retainedEarn < 0 ? '(' : '' }}{{ $currencySymbol }}{{ number_format(abs($retainedEarn), 2) }}{{ $retainedEarn < 0 ? ')' : '' }}
                                </span>
                            </div>

                            <div class="d-flex justify-content-between py-2 mt-2 bg-light-success rounded px-3">
                                <span class="fw-semibold text-success fs-7">Total Equity</span>
                                <span class="fw-bolder {{ $totalEquity >= 0 ? 'text-success' : 'text-danger' }} fs-7">{{ $currencySymbol }}{{ number_format($totalEquity, 2) }}</span>
                            </div>

                            {{-- Total L+E --}}
                            <div class="d-flex align-items-center justify-content-between mt-4 pt-4 border-top border-2 border-danger">
                                <span class="fw-bolder text-dark fs-6 text-uppercase">Total Liabilities &amp; Equity</span>
                                <span class="fw-bolder {{ $isBalanced ? 'text-success' : 'text-danger' }} fs-5">{{ $currencySymbol }}{{ number_format($totalLE, 2) }}</span>
                            </div>

                            @if(!$isBalanced && $diff > 0)
                                <div class="alert alert-danger mt-4 py-3">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Balance difference: {{ $currencySymbol }}{{ number_format($diff, 2) }}.
                                    Assets should equal Liabilities + Equity.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="row mt-5">
                <div class="col-12">
                    <div class="text-center text-muted fs-8">
                        Generated on {{ !empty($data['generated_at']) ? Carbon::parse($data['generated_at'])->format('d M Y, H:i') : now()->format('d M Y, H:i') }}
                        — Balance Sheet as of {{ !empty($asOfDate) ? Carbon::parse($asOfDate)->format('d F Y') : 'Today' }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
