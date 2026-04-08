<div>
    @include(config('accountflow.view_path') . '.blades.dashboard-header')

    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">

                {{-- Summary KPI Cards --}}
                <div class="row g-5 g-xl-8 mb-5">
                    <div class="col-xl-4">
                        <div class="card card-flush" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                            <div class="card-body p-6">
                                <div class="symbol symbol-50px mb-3" style="opacity:.8">
                                    <div class="symbol-label" style="background:rgba(255,255,255,.2)">
                                        <i class="fas fa-arrow-up text-white fs-2"></i>
                                    </div>
                                </div>
                                <span class="text-white opacity-75 fw-semibold fs-7 d-block mb-1">Total Debits</span>
                                <span class="text-white fs-2hx fw-bolder">{{ number_format($totalDebit, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="card card-flush" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                            <div class="card-body p-6">
                                <div class="symbol symbol-50px mb-3" style="opacity:.8">
                                    <div class="symbol-label" style="background:rgba(255,255,255,.2)">
                                        <i class="fas fa-arrow-down text-white fs-2"></i>
                                    </div>
                                </div>
                                <span class="text-white opacity-75 fw-semibold fs-7 d-block mb-1">Total Credits</span>
                                <span class="text-white fs-2hx fw-bolder">{{ number_format($totalCredit, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="card card-flush"
                             style="background: linear-gradient(135deg, {{ $totalNet >= 0 ? '#4facfe 0%, #00f2fe' : '#f093fb 0%, #f5576c' }} 100%);">
                            <div class="card-body p-6">
                                <div class="symbol symbol-50px mb-3" style="opacity:.8">
                                    <div class="symbol-label" style="background:rgba(255,255,255,.2)">
                                        <i class="fas fa-balance-scale text-white fs-2"></i>
                                    </div>
                                </div>
                                <span class="text-white opacity-75 fw-semibold fs-7 d-block mb-1">Net Balance</span>
                                <span class="text-white fs-2hx fw-bolder">{{ number_format(abs($totalNet), 2) }}</span>
                                <span class="text-white opacity-75 fs-8">{{ $totalNet >= 0 ? 'Surplus' : 'Deficit' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Main Table Card --}}
                <div class="card card-flush">
                    <div class="card-header pt-5 pb-3">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-dark fs-3">
                                <i class="fas fa-scale-balanced me-2 text-primary"></i>Trial Balance
                            </span>
                            <span class="text-muted fw-semibold fs-7">Debit and credit summary per account</span>
                        </h3>
                        <div class="card-toolbar gap-2 flex-wrap">
                            {{-- Quick range buttons --}}
                            <div class="btn-group btn-group-sm me-2">
                                <button wire:click="applyQuickRange('today')"
                                        class="btn {{ $period === 'today' ? 'btn-primary' : 'btn-light-primary' }}">Today</button>
                                <button wire:click="applyQuickRange('month')"
                                        class="btn {{ $period === 'month' ? 'btn-primary' : 'btn-light-primary' }}">Month</button>
                                <button wire:click="applyQuickRange('year')"
                                        class="btn {{ $period === 'year' ? 'btn-primary' : 'btn-light-primary' }}">Year</button>
                            </div>
                            <input type="date" wire:model.live="dateFrom" class="form-control form-control-sm w-150px">
                            <input type="date" wire:model.live="dateTo" class="form-control form-control-sm w-150px">
                            <input wire:model.live.debounce.300ms="search"
                                   type="text"
                                   class="form-control form-control-sm w-150px"
                                   placeholder="Search accounts...">
                            <select wire:model.live="perPage" class="form-select form-select-sm w-80px">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-row-dashed align-middle gs-0 gy-3">
                                <thead>
                                    <tr class="fw-bold text-muted fs-7 border-bottom-2 border-gray-200">
                                        <th class="min-w-220px">Account</th>
                                        <th class="min-w-140px text-end">Debit</th>
                                        <th class="min-w-140px text-end">Credit</th>
                                        <th class="min-w-140px text-end">Net Balance</th>
                                        <th class="text-center">Detail</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($accounts as $acct)
                                        @php
                                            $agg       = $txAgg[$acct->id] ?? ['debit' => 0, 'credit' => 0, 'net' => 0];
                                            $categories = $categoryAgg[$acct->id] ?? [];
                                            $collapseId = 'acct-cat-' . $acct->id;
                                            $net        = $agg['net'];
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="symbol symbol-40px">
                                                        <div class="symbol-label bg-light-primary">
                                                            <span class="fw-bold text-primary fs-7">{{ strtoupper(substr($acct->name, 0, 2)) }}</span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <span class="fw-bold text-gray-900 d-block fs-7">{{ $acct->name }}</span>
                                                        @if(!empty($acct->code))
                                                            <span class="text-muted fw-semibold fs-8">{{ $acct->code }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <span class="fw-bold {{ $agg['debit'] > 0 ? 'text-danger' : 'text-muted' }} fs-7">
                                                    {{ number_format($agg['debit'], 2) }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <span class="fw-bold {{ $agg['credit'] > 0 ? 'text-success' : 'text-muted' }} fs-7">
                                                    {{ number_format($agg['credit'], 2) }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                @if($net > 0)
                                                    <span class="badge badge-light-success">+ {{ number_format($net, 2) }}</span>
                                                @elseif($net < 0)
                                                    <span class="badge badge-light-danger">{{ number_format($net, 2) }}</span>
                                                @else
                                                    <span class="badge badge-light-secondary">0.00</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if(!empty($categories))
                                                    <button class="btn btn-sm btn-light-primary py-1 px-2"
                                                            data-bs-toggle="collapse"
                                                            data-bs-target="#{{ $collapseId }}"
                                                            aria-expanded="false">
                                                        <i class="fas fa-chevron-down fs-9"></i>
                                                    </button>
                                                @else
                                                    <span class="text-muted fs-8">—</span>
                                                @endif
                                            </td>
                                        </tr>

                                        @if(!empty($categories))
                                            <tr class="collapse-row">
                                                <td colspan="5" class="p-0 border-0">
                                                    <div class="collapse" id="{{ $collapseId }}">
                                                        <div class="bg-light-primary rounded mx-4 mb-3 p-4">
                                                            <div class="fs-7 fw-bold text-primary mb-3">
                                                                <i class="fas fa-tags me-1"></i>Category Breakdown — {{ $acct->name }}
                                                            </div>
                                                            <table class="table table-sm table-row-bordered mb-0">
                                                                <thead>
                                                                    <tr class="fw-bold text-muted fs-8">
                                                                        <th>Category</th>
                                                                        <th class="text-end">Debit</th>
                                                                        <th class="text-end">Credit</th>
                                                                        <th class="text-end">Net</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($categories as $cid => $c)
                                                                        @php $cnet = ($c['credit'] ?? 0) - ($c['debit'] ?? 0); @endphp
                                                                        <tr>
                                                                            <td class="fw-semibold text-gray-800 fs-8">{{ $c['category_name'] ?? ('# ' . $cid) }}</td>
                                                                            <td class="text-end text-danger fs-8">{{ number_format($c['debit'] ?? 0, 2) }}</td>
                                                                            <td class="text-end text-success fs-8">{{ number_format($c['credit'] ?? 0, 2) }}</td>
                                                                            <td class="text-end fs-8">
                                                                                @if($cnet > 0)
                                                                                    <span class="text-success fw-bold">+{{ number_format($cnet, 2) }}</span>
                                                                                @elseif($cnet < 0)
                                                                                    <span class="text-danger fw-bold">{{ number_format($cnet, 2) }}</span>
                                                                                @else
                                                                                    <span class="text-muted">0.00</span>
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-10">
                                                <div class="text-muted">
                                                    <i class="fas fa-scale-balanced fs-2x mb-3 d-block"></i>
                                                    No accounts found for the selected period.
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold text-dark fs-7 border-top-2 border-gray-300">
                                        <td class="pt-4">
                                            <span class="badge badge-light-dark">
                                                Totals — {{ $accounts->firstItem() ?? 0 }}–{{ $accounts->lastItem() ?? 0 }} of {{ $accounts->total() ?? 0 }} accounts
                                            </span>
                                        </td>
                                        <td class="text-end text-danger fw-bolder pt-4">{{ number_format($totalDebit, 2) }}</td>
                                        <td class="text-end text-success fw-bolder pt-4">{{ number_format($totalCredit, 2) }}</td>
                                        <td class="text-end fw-bolder pt-4 {{ $totalNet >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $totalNet >= 0 ? '+' : '' }}{{ number_format($totalNet, 2) }}
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="mt-4">{{ $accounts->links() }}</div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
