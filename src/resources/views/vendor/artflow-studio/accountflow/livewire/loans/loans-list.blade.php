<div>
    @if(!$standalone)
        @include(config('accountflow.view_path') . 'blades.dashboard-header')
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
                    <div class="col-xl-3">
                        <div class="card card-flush" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <div class="card-body p-6">
                                <div class="symbol symbol-50px mb-3" style="opacity:.8"><div class="symbol-label" style="background:rgba(255,255,255,.2)"><i class="fas fa-file-invoice-dollar text-white fs-2"></i></div></div>
                                <span class="text-white opacity-75 fw-semibold fs-7 d-block mb-1">Total Loans</span>
                                <span class="text-white fs-2hx fw-bolder">{{ $stats['total'] }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3">
                        <div class="card card-flush" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                            <div class="card-body p-6">
                                <div class="symbol symbol-50px mb-3" style="opacity:.8"><div class="symbol-label" style="background:rgba(255,255,255,.2)"><i class="fas fa-arrow-right text-white fs-2"></i></div></div>
                                <span class="text-white opacity-75 fw-semibold fs-7 d-block mb-1">Lended Out</span>
                                <span class="text-white fs-2hx fw-bolder">{{ $currency }} {{ number_format($stats['lended'], 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3">
                        <div class="card card-flush" style="background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);">
                            <div class="card-body p-6">
                                <div class="symbol symbol-50px mb-3" style="opacity:.8"><div class="symbol-label" style="background:rgba(255,255,255,.2)"><i class="fas fa-arrow-left text-white fs-2"></i></div></div>
                                <span class="text-white opacity-75 fw-semibold fs-7 d-block mb-1">Borrowed</span>
                                <span class="text-white fs-2hx fw-bolder">{{ $currency }} {{ number_format($stats['borrowed'], 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3">
                        <div class="card card-flush h-100">
                            <div class="card-body p-6 d-flex flex-column justify-content-center">
                                <div class="d-flex gap-2 justify-content-center flex-wrap">
                                    <a href="{{ route('accountflow::loans.create') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus me-1"></i>New Loan
                                    </a>
                                    <a href="{{ route('accountflow::loans.partners') }}" class="btn btn-light-primary btn-sm">
                                        <i class="fas fa-users me-1"></i>Partners
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
                            <span class="card-label fw-bold text-dark fs-3"><i class="fas fa-hand-holding-usd me-2 text-primary"></i>Loans</span>
                            <span class="text-muted fw-semibold fs-7">Track lended and borrowed amounts</span>
                        </h3>
                        <div class="card-toolbar gap-2">
                            <input wire:model.live.debounce.300ms="search" type="text" class="form-control form-control-sm w-200px" placeholder="Search loans...">
                            <select wire:model.live="typeFilter" class="form-select form-select-sm w-150px">
                                <option value="">All Types</option>
                                <option value="1">Lended Out</option>
                                <option value="2">Borrowed</option>
                            </select>
                            <select wire:model.live="statusFilter" class="form-select form-select-sm w-150px">
                                <option value="">All Statuses</option>
                                <option value="1">Active</option>
                                <option value="0">Closed</option>
                            </select>
                            <a href="{{ route('accountflow::loans.create') }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus me-1"></i>New Loan
                            </a>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-row-dashed align-middle gs-0 gy-4">
                                <thead>
                                    <tr class="fw-bold text-muted fs-7 border-bottom-2 border-gray-200">
                                        <th>Loan</th>
                                        <th class="text-center">Type</th>
                                        <th>Partner</th>
                                        <th class="text-end">Amount</th>
                                        <th class="text-center">ROI</th>
                                        <th class="text-center">Status</th>
                                        <th>Date</th>
                                        <th>Due Date</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($loans as $loan)
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-gray-900 fs-7">{{ $loan->name }}</div>
                                                @if($loan->unique_id)
                                                    <div class="text-muted fs-8">#{{ $loan->unique_id }}</div>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($loan->loan_type == 1)
                                                    <span class="badge badge-light-success">Lended Out</span>
                                                @else
                                                    <span class="badge badge-light-warning">Borrowed</span>
                                                @endif
                                            </td>
                                            <td class="text-gray-800 fs-7">{{ $loan->loanPartner->name ?? '—' }}</td>
                                            <td class="text-end fw-bold text-gray-900">{{ $currency }} {{ number_format($loan->amount, 2) }}</td>
                                            <td class="text-center text-muted fs-8">{{ $loan->roi ? $loan->roi . '%' : '—' }}</td>
                                            <td class="text-center">
                                                @if($loan->status == 1)
                                                    <span class="badge badge-light-primary">Active</span>
                                                @else
                                                    <span class="badge badge-light-secondary">Closed</span>
                                                @endif
                                            </td>
                                            <td class="text-muted fs-8">{{ $loan->date ? \Carbon\Carbon::parse($loan->date)->format('M d, Y') : '—' }}</td>
                                            <td class="text-muted fs-8">
                                                @if($loan->due_date)
                                                    @php $overdue = \Carbon\Carbon::parse($loan->due_date)->isPast() && $loan->status == 1; @endphp
                                                    <span class="{{ $overdue ? 'text-danger fw-bold' : '' }}">
                                                        {{ \Carbon\Carbon::parse($loan->due_date)->format('M d, Y') }}
                                                    </span>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <div class="d-flex gap-1 justify-content-end">
                                                    <a href="{{ route('accountflow::loans.edit', $loan->id) }}" class="btn btn-sm btn-light-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button wire:click="deleteLoan({{ $loan->id }})" wire:confirm="Delete this loan?" class="btn btn-sm btn-light-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-10">
                                                <div class="text-muted">
                                                    <i class="fas fa-hand-holding-usd fs-2x mb-3 d-block"></i>
                                                    No loans found. <a href="{{ route('accountflow::loans.create') }}">Create your first loan</a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">{{ $loans->links() }}</div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
