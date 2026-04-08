<div>
    @if(!$standalone)
        @include(config('accountflow.view_path') . '.blades.dashboard-header')
    @endif

    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-5" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                {{-- KPI Cards --}}
                <div class="row g-5 g-xl-8 mb-5">
                    <div class="col-xl-4">
                        <div class="card card-flush" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                            <div class="card-body p-6">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="symbol symbol-50px">
                                        <div class="symbol-label" style="background: rgba(255,255,255,0.2);">
                                            <i class="fas fa-arrow-down text-white fs-2"></i>
                                        </div>
                                    </div>
                                </div>
                                <span class="text-white opacity-75 fw-semibold fs-7 d-block mb-1">Total Contributions</span>
                                <span class="text-white fs-2hx fw-bolder">{{ $currencySymbol }}{{ number_format($totalContributions, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="card card-flush" style="background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);">
                            <div class="card-body p-6">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="symbol symbol-50px">
                                        <div class="symbol-label" style="background: rgba(255,255,255,0.2);">
                                            <i class="fas fa-arrow-up text-white fs-2"></i>
                                        </div>
                                    </div>
                                </div>
                                <span class="text-white opacity-75 fw-semibold fs-7 d-block mb-1">Total Withdrawals</span>
                                <span class="text-white fs-2hx fw-bolder">{{ $currencySymbol }}{{ number_format($totalWithdrawals, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="card card-flush h-100">
                            <div class="card-body p-6 d-flex flex-column justify-content-center">
                                <div class="d-flex gap-2 justify-content-center flex-wrap">
                                    <a href="{{ route('accountflow::equity.partners') }}"
                                       class="btn btn-light-primary btn-sm">
                                        <i class="fas fa-users me-1"></i>View Partners
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Table --}}
                <div class="card card-flush">
                    <div class="card-header pt-5 pb-3">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-dark fs-3">
                                <i class="fas fa-exchange-alt me-2 text-primary"></i>Equity Transactions
                            </span>
                            <span class="text-muted fw-semibold fs-7">Contributions and withdrawals per partner</span>
                        </h3>
                        <div class="card-toolbar gap-2">
                            <input wire:model.live.debounce.300ms="search"
                                   type="text"
                                   class="form-control form-control-sm w-200px"
                                   placeholder="Search description...">
                            <select wire:model.live="partnerFilter" class="form-select form-select-sm w-180px">
                                <option value="">All Partners</option>
                                @foreach($partners as $p)
                                    <option value="{{ $p['id'] }}">{{ $p['name'] }}</option>
                                @endforeach
                            </select>
                            <select wire:model.live="typeFilter" class="form-select form-select-sm w-150px">
                                <option value="">All Types</option>
                                <option value="1">Contribution</option>
                                <option value="2">Withdrawal</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-row-dashed align-middle gs-0 gy-4">
                                <thead>
                                    <tr class="fw-bold text-muted fs-7 border-bottom-2 border-gray-200">
                                        <th class="min-w-200px">Partner</th>
                                        <th class="min-w-120px text-center">Type</th>
                                        <th class="min-w-150px text-end">Amount</th>
                                        <th class="min-w-200px">Description</th>
                                        <th class="min-w-120px">Date</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($transactions as $trx)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="symbol symbol-35px me-3">
                                                        <div class="symbol-label bg-light-primary fw-bold text-primary fs-7">
                                                            {{ strtoupper(substr($trx->partner->name ?? 'NA', 0, 2)) }}
                                                        </div>
                                                    </div>
                                                    <div class="fw-bold text-gray-900 fs-7">
                                                        {{ $trx->partner->name ?? '—' }}
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                @if($trx->type == 1)
                                                    <span class="badge badge-light-success">Contribution</span>
                                                @else
                                                    <span class="badge badge-light-danger">Withdrawal</span>
                                                @endif
                                            </td>
                                            <td class="text-end fw-bold {{ $trx->type == 1 ? 'text-success' : 'text-danger' }}">
                                                {{ $trx->type == 1 ? '+' : '-' }}{{ $currencySymbol }}{{ number_format($trx->amount, 2) }}
                                            </td>
                                            <td class="text-muted fs-7">{{ $trx->description ?? '—' }}</td>
                                            <td class="text-muted fs-8">
                                                {{ $trx->created_at ? \Carbon\Carbon::parse($trx->created_at)->format('M d, Y') : '—' }}
                                            </td>
                                            <td class="text-end">
                                                <button wire:click="deleteTransaction({{ $trx->id }})"
                                                        wire:confirm="Are you sure you want to delete this transaction?"
                                                        class="btn btn-sm btn-light-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-10">
                                                <div class="text-muted">
                                                    <i class="fas fa-exchange-alt fs-2x mb-3 d-block"></i>
                                                    No equity transactions found.
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $transactions->links() }}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
