<div>
    @unless($standalone)
        <div class="page-header">
            <div class="page-header-body">
                <nav class="page-breadcrumb" aria-label="Breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('accountflow::dashboard') }}" wire:navigate>Accounts</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Equity Partners</li>
                    </ol>
                </nav>
                <div class="page-header-title"><h1>Equity Partners</h1></div>
                <p class="page-header-subtitle">Ownership stakes and equity contributions per partner.</p>
            </div>
            <div class="page-header-actions">
                <a href="{{ route('accountflow::equity.transactions') }}" class="btn btn-soft-secondary" wire:navigate>
                    <i data-lucide="repeat"></i> Transactions
                </a>
                @if ($canManage)
                    <a href="{{ route('accountflow::equity.partners.create') }}" class="btn btn-primary" wire:navigate>
                        <i data-lucide="user-plus"></i> Add Partner
                    </a>
                @endif
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-xl-4">
                <div class="card stat-tile h-100"><div class="card-body">
                    <div class="stat">
                        <div class="stat-head">
                            <div class="stat-label">Total Partners</div>
                            <span class="icon-box icon-box-primary"><i data-lucide="users"></i></span>
                        </div>
                        <div class="stat-value">{{ number_format($totalCount) }}</div>
                        <div class="stat-meta"><span>{{ $activeCount }} active</span></div>
                    </div>
                </div></div>
            </div>
            <div class="col-6 col-xl-4">
                <div class="card stat-tile h-100"><div class="card-body">
                    <div class="stat">
                        <div class="stat-head">
                            <div class="stat-label">Total Equity Pool</div>
                            <span class="icon-box icon-box-success"><i data-lucide="chart-pie"></i></span>
                        </div>
                        <div class="stat-value">{{ $currencySymbol }}{{ number_format($totalEquity, 2) }}</div>
                        <div class="stat-meta"><span>Across all partners</span></div>
                    </div>
                </div></div>
            </div>
            <div class="col-12 col-xl-4">
                <div class="card stat-tile h-100"><div class="card-body">
                    <div class="stat">
                        <div class="stat-head">
                            <div class="stat-label">Inactive Partners</div>
                            <span class="icon-box icon-box-warning"><i data-lucide="circle-alert"></i></span>
                        </div>
                        <div class="stat-value">{{ $totalCount - $activeCount }}</div>
                        <div class="stat-meta"><span>Need review</span></div>
                    </div>
                </div></div>
            </div>
        </div>
    @endunless

    <div class="{{ $standalone ? '' : 'card' }}">
        @unless($standalone)
            <div class="card-header">
                <h2 class="card-title">All Partners</h2>
                <div class="card-actions">
                    <input wire:model.live.debounce.300ms="search" type="search" class="form-control form-control-sm" style="width:200px" placeholder="Search partners...">
                    <select wire:model.live="statusFilter" class="form-select form-select-sm" style="width:150px">
                        <option value="">All Statuses</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
        @endunless

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-flush align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Partner</th>
                            <th>Contact</th>
                            <th class="cell-numeric">Ownership %</th>
                            <th class="cell-numeric">Current Equity</th>
                            <th class="text-center">Status</th>
                            <th>Joined</th>
                            <th class="cell-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($partners as $partner)
                            <tr>
                                <td>
                                    <div class="cell-user">
                                        <span class="avatar avatar-primary">{{ strtoupper(substr($partner->name, 0, 2)) }}</span>
                                        <div class="cell-user-body">
                                            <div class="cell-user-name">{{ $partner->name }}</div>
                                            @if($partner->company)
                                                <div class="cell-user-meta">{{ $partner->company }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($partner->email)
                                        <div>{{ $partner->email }}</div>
                                    @endif
                                    @if($partner->phone)
                                        <div class="text-body-secondary text-size-sm">{{ $partner->phone }}</div>
                                    @endif
                                </td>
                                <td class="cell-numeric font-mono">{{ number_format($partner->ownership_percentage ?? 0, 2) }}%</td>
                                <td class="cell-numeric font-mono">{{ $currencySymbol }}{{ number_format($partner->current_equity ?? 0, 2) }}</td>
                                <td class="text-center">
                                    @if($partner->is_active)
                                        <span class="badge badge-soft-success">Active</span>
                                    @else
                                        <span class="badge badge-soft-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-body-secondary text-size-sm">
                                    {{ $partner->joined_at ? \Carbon\Carbon::parse($partner->joined_at)->format('M d, Y') : '—' }}
                                </td>
                                <td class="cell-actions">
                                    @if($canManage)
                                        <div class="d-flex gap-1 justify-content-end">
                                            <a href="{{ route('accountflow::equity.partners.edit', $partner->id) }}" class="btn btn-sm btn-ghost">
                                                <i data-lucide="pencil"></i>
                                            </a>
                                            <button wire:click="deletePartner({{ $partner->id }})"
                                                    wire:confirm="Are you sure you want to delete this partner?"
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
                                    <div class="text-body-secondary">
                                        <i data-lucide="users"></i>
                                        <div class="mt-2">No equity partners found.</div>
                                        @if($canManage)
                                            <a href="{{ route('accountflow::equity.partners.create') }}" wire:navigate>Add your first partner</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if(! $standalone && $partners->hasPages())
            <div class="card-footer justify-content-center">
                {{ $partners->links() }}
            </div>
        @endif
    </div>
</div>
