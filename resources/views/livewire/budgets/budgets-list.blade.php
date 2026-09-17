<div>
    @unless($standalone)
        <div class="page-header">
            <div class="page-header-body">
                <nav class="page-breadcrumb" aria-label="Breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('accountflow::dashboard') }}" wire:navigate>Accounts</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Budgets</li>
                    </ol>
                </nav>
                <div class="page-header-title"><h1>Budgets</h1></div>
                <p class="page-header-subtitle">Allocations by category and account.</p>
            </div>
            @if ($canManage)
                <div class="page-header-actions">
                    <a href="{{ route('accountflow::budgets.create') }}" class="btn btn-primary" wire:navigate>
                        <i data-lucide="plus"></i> Create Budget
                    </a>
                </div>
            @endif
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-xl-3">
                <div class="card stat-tile h-100"><div class="card-body">
                    <div class="stat">
                        <div class="stat-head">
                            <div class="stat-label">Total Budgets</div>
                            <span class="icon-box icon-box-primary"><i data-lucide="target"></i></span>
                        </div>
                        <div class="stat-value">{{ number_format($stats['count']) }}</div>
                        <div class="stat-meta"><span>Active allocations</span></div>
                    </div>
                </div></div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="card stat-tile h-100"><div class="card-body">
                    <div class="stat">
                        <div class="stat-head">
                            <div class="stat-label">Total Allocated</div>
                            <span class="icon-box icon-box-success"><i data-lucide="dollar-sign"></i></span>
                        </div>
                        <div class="stat-value">{{ $currency }} {{ number_format($stats['total'], 0) }}</div>
                        <div class="stat-meta"><span>Across all budgets</span></div>
                    </div>
                </div></div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="card stat-tile h-100"><div class="card-body">
                    <div class="stat">
                        <div class="stat-head">
                            <div class="stat-label">Monthly Budget</div>
                            <span class="icon-box icon-box-warning"><i data-lucide="calendar-clock"></i></span>
                        </div>
                        <div class="stat-value">{{ $currency }} {{ number_format($stats['monthly'], 0) }}</div>
                        <div class="stat-meta"><span>Recurring monthly</span></div>
                    </div>
                </div></div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="card stat-tile h-100"><div class="card-body">
                    <div class="stat">
                        <div class="stat-head">
                            <div class="stat-label">Yearly Budget</div>
                            <span class="icon-box icon-box-secondary"><i data-lucide="calendar-days"></i></span>
                        </div>
                        <div class="stat-value">{{ $currency }} {{ number_format($stats['yearly'], 0) }}</div>
                        <div class="stat-meta"><span>Recurring yearly</span></div>
                    </div>
                </div></div>
            </div>
        </div>
    @endunless

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <div>
                <h2 class="card-title">All Budgets</h2>
                <p class="card-subtitle">Allocations by category and account</p>
            </div>
            <div class="d-flex gap-2">
                <input wire:model.live.debounce.300ms="search" type="text" class="form-control form-control-sm" style="width:200px" placeholder="Search budgets...">
                <select wire:model.live="periodFilter" class="form-select form-select-sm" style="width:150px">
                    <option value="">All Periods</option>
                    <option value="monthly">Monthly</option>
                    <option value="yearly">Yearly</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-flush align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Account</th>
                            <th class="text-center">Period</th>
                            <th class="text-center">Month / Year</th>
                            <th class="cell-numeric">Allocated Amount</th>
                            <th>Description</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($budgets as $budget)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="icon-box icon-box-sm icon-box-primary"><i data-lucide="tag"></i></span>
                                        <span class="fw-semibold">{{ $budget->category->name ?? '—' }}</span>
                                    </div>
                                </td>
                                <td>{{ $budget->account->name ?? '—' }}</td>
                                <td class="text-center">
                                    @if($budget->period === 'monthly')
                                        <span class="badge badge-soft-primary">Monthly</span>
                                    @elseif($budget->period === 'yearly')
                                        <span class="badge badge-soft-success">Yearly</span>
                                    @else
                                        <span class="badge badge-soft-secondary">{{ Str::title($budget->period) }}</span>
                                    @endif
                                </td>
                                <td class="text-center text-body-secondary text-size-sm">
                                    @if($budget->month)
                                        {{ \Carbon\Carbon::create()->month($budget->month)->format('M') }}
                                    @else — @endif
                                    {{ $budget->year ? '/ ' . $budget->year : '' }}
                                </td>
                                <td class="cell-numeric font-mono fw-semibold">
                                    {{ $currency }} {{ number_format($budget->amount, 2) }}
                                </td>
                                <td class="text-body-secondary text-size-sm">{{ Str::limit($budget->description ?? '—', 50) }}</td>
                                <td class="text-end">
                                    @if ($canManage)
                                        <div class="d-flex gap-1 justify-content-end">
                                            <a href="{{ route('accountflow::budgets') }}" class="btn btn-sm btn-ghost">
                                                <i data-lucide="pencil"></i>
                                            </a>
                                            <button wire:click="deleteBudget({{ $budget->id }})"
                                                    wire:confirm="Delete this budget?"
                                                    class="btn btn-sm btn-ghost text-danger">
                                                <i data-lucide="trash-2"></i>
                                            </button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i data-lucide="chart-pie" class="text-body-secondary mb-2"></i>
                                    <p class="text-body-secondary mb-0">
                                        No budgets found. <a href="{{ route('accountflow::budgets.create') }}" wire:navigate>Create your first budget</a>
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($budgets->hasPages())
            <div class="card-footer">
                {{ $budgets->links() }}
            </div>
        @endif
    </div>
</div>
