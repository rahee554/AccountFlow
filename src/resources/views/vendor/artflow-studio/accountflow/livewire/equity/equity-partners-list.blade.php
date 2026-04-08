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
                        <div class="card card-flush" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <div class="card-body p-6">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="symbol symbol-50px">
                                        <div class="symbol-label" style="background: rgba(255,255,255,0.2);">
                                            <i class="fas fa-users text-white fs-2"></i>
                                        </div>
                                    </div>
                                    <span class="badge badge-white fs-8">Partners</span>
                                </div>
                                <div>
                                    <span class="text-white opacity-75 fw-semibold fs-7 d-block mb-1">Total Partners</span>
                                    <span class="text-white fs-2hx fw-bolder">{{ $totalCount }}</span>
                                    <span class="text-white opacity-75 fs-8 d-block mt-1">{{ $activeCount }} active</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="card card-flush" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                            <div class="card-body p-6">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="symbol symbol-50px">
                                        <div class="symbol-label" style="background: rgba(255,255,255,0.2);">
                                            <i class="fas fa-chart-pie text-white fs-2"></i>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <span class="text-white opacity-75 fw-semibold fs-7 d-block mb-1">Total Equity Pool</span>
                                    <span class="text-white fs-2hx fw-bolder">{{ $currencySymbol ?? '' }}{{ number_format($totalEquity, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="card card-flush h-100">
                            <div class="card-body p-6 d-flex flex-column justify-content-center">
                                <div class="d-flex gap-2 justify-content-center flex-wrap">
                                    <a href="{{ route('accountflow::equity.partners.create') }}"
                                       class="btn btn-primary btn-sm">
                                        <i class="fas fa-user-plus me-1"></i>Add Partner
                                    </a>
                                    <a href="{{ route('accountflow::equity.transactions') }}"
                                       class="btn btn-light-primary btn-sm">
                                        <i class="fas fa-exchange-alt me-1"></i>Transactions
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Filters & Table --}}
                <div class="card card-flush">
                    <div class="card-header pt-5 pb-3">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-dark fs-3">
                                <i class="fas fa-users me-2 text-primary"></i>Equity Partners
                            </span>
                            <span class="text-muted fw-semibold fs-7">Manage ownership & equity stakes</span>
                        </h3>
                        <div class="card-toolbar gap-2">
                            <input wire:model.live.debounce.300ms="search"
                                   type="text"
                                   class="form-control form-control-sm w-200px"
                                   placeholder="Search partners...">
                            <select wire:model.live="statusFilter" class="form-select form-select-sm w-150px">
                                <option value="">All Statuses</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <a href="{{ route('accountflow::equity.partners.create') }}"
                               class="btn btn-sm btn-primary">
                                <i class="fas fa-plus me-1"></i>Add Partner
                            </a>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-row-dashed align-middle gs-0 gy-4">
                                <thead>
                                    <tr class="fw-bold text-muted fs-7 border-bottom-2 border-gray-200">
                                        <th class="min-w-200px">Partner</th>
                                        <th class="min-w-150px">Contact</th>
                                        <th class="min-w-120px text-end">Ownership %</th>
                                        <th class="min-w-150px text-end">Current Equity</th>
                                        <th class="min-w-100px text-center">Status</th>
                                        <th class="min-w-120px text-center">Joined</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($partners as $partner)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="symbol symbol-40px me-3">
                                                        <div class="symbol-label bg-light-primary fw-bold text-primary fs-6">
                                                            {{ strtoupper(substr($partner->name, 0, 2)) }}
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-gray-900 fs-6">{{ $partner->name }}</div>
                                                        @if($partner->company)
                                                            <div class="text-muted fs-8">{{ $partner->company }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($partner->email)
                                                    <div class="text-gray-800 fs-7">{{ $partner->email }}</div>
                                                @endif
                                                @if($partner->phone)
                                                    <div class="text-muted fs-8">{{ $partner->phone }}</div>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <span class="badge badge-light-info fw-bold">
                                                    {{ number_format($partner->ownership_percentage ?? 0, 2) }}%
                                                </span>
                                            </td>
                                            <td class="text-end fw-bold text-gray-900">
                                                {{ $currencySymbol ?? '' }}{{ number_format($partner->current_equity ?? 0, 2) }}
                                            </td>
                                            <td class="text-center">
                                                @if($partner->is_active)
                                                    <span class="badge badge-light-success">Active</span>
                                                @else
                                                    <span class="badge badge-light-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td class="text-center text-muted fs-8">
                                                {{ $partner->joined_at ? \Carbon\Carbon::parse($partner->joined_at)->format('M d, Y') : '—' }}
                                            </td>
                                            <td class="text-end">
                                                <div class="d-flex gap-1 justify-content-end">
                                                    <a href="{{ route('accountflow::equity.partners.edit', $partner->id) }}"
                                                       class="btn btn-sm btn-light-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button wire:click="deletePartner({{ $partner->id }})"
                                                            wire:confirm="Are you sure you want to delete this partner?"
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
                                                    <i class="fas fa-users fs-2x mb-3 d-block"></i>
                                                    No equity partners found.
                                                    <a href="{{ route('accountflow::equity.partners.create') }}" class="ms-1">Add your first partner</a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $partners->links() }}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
