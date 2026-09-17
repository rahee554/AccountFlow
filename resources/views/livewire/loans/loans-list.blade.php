<div>
    @unless($standalone)
        <div class="page-header">
            <div class="page-header-body">
                <nav class="page-breadcrumb" aria-label="Breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('accountflow::dashboard') }}" wire:navigate>Accounts</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Loans</li>
                    </ol>
                </nav>
                <div class="page-header-title"><h1>Loans</h1></div>
                <p class="page-header-subtitle">Money you've lent out or borrowed.</p>
            </div>
            <div class="page-header-actions">
                <a href="{{ route('accountflow::loans.partners') }}" class="btn btn-soft-secondary" wire:navigate>
                    <i data-lucide="users"></i> Partners
                </a>
                @if ($canManage)
                    <a href="{{ route('accountflow::loans.create') }}" class="btn btn-primary" wire:navigate>
                        <i data-lucide="plus"></i> New Loan
                    </a>
                @endif
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-xl-4">
                <div class="card stat-tile h-100"><div class="card-body">
                    <div class="stat">
                        <div class="stat-head">
                            <div class="stat-label">Total Loans</div>
                            <span class="icon-box icon-box-primary"><i data-lucide="receipt"></i></span>
                        </div>
                        <div class="stat-value">{{ number_format($stats['total']) }}</div>
                        <div class="stat-meta"><span>{{ $stats['active'] }} active</span></div>
                    </div>
                </div></div>
            </div>
            <div class="col-6 col-xl-4">
                <div class="card stat-tile h-100"><div class="card-body">
                    <div class="stat">
                        <div class="stat-head">
                            <div class="stat-label">Lended Out</div>
                            <span class="icon-box icon-box-success"><i data-lucide="arrow-right"></i></span>
                        </div>
                        <div class="stat-value">{{ $currency }} {{ number_format($stats['lended'], 2) }}</div>
                    </div>
                </div></div>
            </div>
            <div class="col-12 col-xl-4">
                <div class="card stat-tile h-100"><div class="card-body">
                    <div class="stat">
                        <div class="stat-head">
                            <div class="stat-label">Borrowed</div>
                            <span class="icon-box icon-box-danger"><i data-lucide="arrow-left"></i></span>
                        </div>
                        <div class="stat-value">{{ $currency }} {{ number_format($stats['borrowed'], 2) }}</div>
                    </div>
                </div></div>
            </div>
        </div>
    @endunless

    <div class="{{ $standalone ? '' : 'card' }}">
        @unless($standalone)
            <div class="card-header">
                <h2 class="card-title">All Loans</h2>
                <div class="card-actions">
                    <input wire:model.live.debounce.300ms="search" type="search" class="form-control form-control-sm" style="width:200px" placeholder="Search loans...">
                    <select wire:model.live="typeFilter" class="form-select form-select-sm" style="width:150px">
                        <option value="">All Types</option>
                        <option value="1">Lended Out</option>
                        <option value="2">Borrowed</option>
                    </select>
                    <select wire:model.live="statusFilter" class="form-select form-select-sm" style="width:150px">
                        <option value="">All Statuses</option>
                        <option value="1">Active</option>
                        <option value="0">Closed</option>
                    </select>
                </div>
            </div>
        @endunless

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-flush align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Loan</th>
                            <th class="text-center">Type</th>
                            <th>Partner</th>
                            <th class="cell-numeric">Amount</th>
                            <th class="cell-numeric">ROI</th>
                            <th class="text-center">Status</th>
                            <th>Date</th>
                            <th>Due Date</th>
                            <th class="cell-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($loans as $loan)
                            <tr>
                                <td>
                                    <div class="fw-medium">{{ $loan->name }}</div>
                                    @if($loan->unique_id)
                                        <div class="text-body-secondary text-size-sm">#{{ $loan->unique_id }}</div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($loan->loan_type == 1)
                                        <span class="badge badge-soft-success">Lended Out</span>
                                    @else
                                        <span class="badge badge-soft-warning">Borrowed</span>
                                    @endif
                                </td>
                                <td>{{ $loan->loanPartner->name ?? '—' }}</td>
                                <td class="cell-numeric font-mono">{{ $currency }} {{ number_format($loan->amount, 2) }}</td>
                                <td class="cell-numeric text-body-secondary">{{ $loan->roi ? $loan->roi . '%' : '—' }}</td>
                                <td class="text-center">
                                    @if($loan->status == 1)
                                        <span class="badge badge-soft-primary">Active</span>
                                    @else
                                        <span class="badge badge-soft-secondary">Closed</span>
                                    @endif
                                </td>
                                <td class="text-body-secondary text-size-sm">{{ $loan->date ? \Carbon\Carbon::parse($loan->date)->format('M d, Y') : '—' }}</td>
                                <td class="text-size-sm">
                                    @if($loan->due_date)
                                        @php $overdue = \Carbon\Carbon::parse($loan->due_date)->isPast() && $loan->status == 1; @endphp
                                        <span class="{{ $overdue ? 'text-danger fw-semibold' : 'text-body-secondary' }}">
                                            {{ \Carbon\Carbon::parse($loan->due_date)->format('M d, Y') }}
                                        </span>
                                    @else
                                        <span class="text-body-secondary">—</span>
                                    @endif
                                </td>
                                <td class="cell-actions">
                                    @if($canManage)
                                        <div class="d-flex gap-1 justify-content-end">
                                            <a href="{{ route('accountflow::loans.edit', $loan->id) }}" class="btn btn-sm btn-ghost">
                                                <i data-lucide="pencil"></i>
                                            </a>
                                            <button wire:click="deleteLoan({{ $loan->id }})" wire:confirm="Delete this loan?" class="btn btn-sm btn-ghost text-danger">
                                                <i data-lucide="trash-2"></i>
                                            </button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="text-body-secondary">
                                        <i data-lucide="receipt"></i>
                                        <div class="mt-2">
                                            No loans found.
                                            @if($canManage)
                                                <a href="{{ route('accountflow::loans.create') }}" wire:navigate>Create your first loan</a>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if(! $standalone && $loans->hasPages())
            <div class="card-footer justify-content-center">
                {{ $loans->links() }}
            </div>
        @endif
    </div>
</div>
