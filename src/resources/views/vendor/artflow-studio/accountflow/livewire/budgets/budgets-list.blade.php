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
                    <div class="col-xl-3">
                        <div class="card card-flush" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <div class="card-body p-6">
                                <div class="symbol symbol-50px mb-3" style="opacity:.8"><div class="symbol-label" style="background:rgba(255,255,255,.2)"><i class="fas fa-bullseye text-white fs-2"></i></div></div>
                                <span class="text-white opacity-75 fw-semibold fs-7 d-block mb-1">Total Budgets</span>
                                <span class="text-white fs-2hx fw-bolder">{{ $stats['count'] }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3">
                        <div class="card card-flush" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                            <div class="card-body p-6">
                                <div class="symbol symbol-50px mb-3" style="opacity:.8"><div class="symbol-label" style="background:rgba(255,255,255,.2)"><i class="fas fa-dollar-sign text-white fs-2"></i></div></div>
                                <span class="text-white opacity-75 fw-semibold fs-7 d-block mb-1">Total Allocated</span>
                                <span class="text-white fs-2hx fw-bolder">{{ $currency }} {{ number_format($stats['total'], 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3">
                        <div class="card card-flush" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                            <div class="card-body p-6">
                                <div class="symbol symbol-50px mb-3" style="opacity:.8"><div class="symbol-label" style="background:rgba(255,255,255,.2)"><i class="fas fa-calendar-day text-white fs-2"></i></div></div>
                                <span class="text-white opacity-75 fw-semibold fs-7 d-block mb-1">Monthly Budget</span>
                                <span class="text-white fs-2hx fw-bolder">{{ $currency }} {{ number_format($stats['monthly'], 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3">
                        <div class="card card-flush h-100">
                            <div class="card-body p-6 d-flex flex-column justify-content-center">
                                <div class="d-flex gap-2 justify-content-center flex-wrap">
                                    <a href="{{ route('accountflow::budgets.create') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus me-1"></i>Create Budget
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
                                <i class="fas fa-chart-bar me-2 text-primary"></i>Budget Overview
                            </span>
                            <span class="text-muted fw-semibold fs-7">Allocations by category and account</span>
                        </h3>
                        <div class="card-toolbar gap-2">
                            <input wire:model.live.debounce.300ms="search"
                                   type="text"
                                   class="form-control form-control-sm w-200px"
                                   placeholder="Search budgets...">
                            <select wire:model.live="periodFilter" class="form-select form-select-sm w-150px">
                                <option value="">All Periods</option>
                                <option value="monthly">Monthly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                            <a href="{{ route('accountflow::budgets.create') }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus me-1"></i>Create
                            </a>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-row-dashed align-middle gs-0 gy-4">
                                <thead>
                                    <tr class="fw-bold text-muted fs-7 border-bottom-2 border-gray-200">
                                        <th class="min-w-200px">Category</th>
                                        <th class="min-w-150px">Account</th>
                                        <th class="min-w-100px text-center">Period</th>
                                        <th class="min-w-80px text-center">Month / Year</th>
                                        <th class="min-w-150px text-end">Allocated Amount</th>
                                        <th class="min-w-200px">Description</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($budgets as $budget)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="symbol symbol-35px">
                                                        <div class="symbol-label bg-light-primary">
                                                            <i class="fas fa-tag text-primary fs-7"></i>
                                                        </div>
                                                    </div>
                                                    <div class="fw-bold text-gray-900 fs-7">
                                                        {{ $budget->category->name ?? '—' }}
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-gray-800 fs-7">{{ $budget->account->name ?? '—' }}</td>
                                            <td class="text-center">
                                                @if($budget->period === 'monthly')
                                                    <span class="badge badge-light-primary">Monthly</span>
                                                @elseif($budget->period === 'yearly')
                                                    <span class="badge badge-light-success">Yearly</span>
                                                @else
                                                    <span class="badge badge-light-secondary">{{ $budget->period }}</span>
                                                @endif
                                            </td>
                                            <td class="text-center text-muted fs-8">
                                                @if($budget->month)
                                                    {{ \Carbon\Carbon::create()->month($budget->month)->format('M') }}
                                                @else — @endif
                                                {{ $budget->year ? '/ ' . $budget->year : '' }}
                                            </td>
                                            <td class="text-end fw-bold text-gray-900">
                                                {{ $currency }} {{ number_format($budget->amount, 2) }}
                                            </td>
                                            <td class="text-muted fs-8">{{ Str::limit($budget->description ?? '—', 50) }}</td>
                                            <td class="text-end">
                                                <div class="d-flex gap-1 justify-content-end">
                                                    <a href="{{ route('accountflow::budgets') }}" class="btn btn-sm btn-light-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button wire:click="deleteBudget({{ $budget->id }})"
                                                            wire:confirm="Delete this budget?"
                                                            class="btn btn-sm btn-light-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-10">
                                                <div class="text-muted">
                                                    <i class="fas fa-chart-bar fs-2x mb-3 d-block"></i>
                                                    No budgets found. <a href="{{ route('accountflow::budgets.create') }}">Create your first budget</a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">{{ $budgets->links() }}</div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
