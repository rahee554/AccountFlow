<div>
    @unless($standalone)
        <div class="page-header">
            <div class="page-header-body">
                <nav class="page-breadcrumb" aria-label="Breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('accountflow::dashboard') }}" wire:navigate>Accounts</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Equity Transactions</li>
                    </ol>
                </nav>
                <div class="page-header-title"><h1>Equity Transactions</h1></div>
                <p class="page-header-subtitle">Contributions and withdrawals per partner.</p>
            </div>
            <div class="page-header-actions">
                <a href="{{ route('accountflow::equity.partners') }}" class="btn btn-soft-secondary" wire:navigate>
                    <i data-lucide="users"></i> View Partners
                </a>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-xl-4">
                <div class="card stat-tile h-100"><div class="card-body">
                    <div class="stat">
                        <div class="stat-head">
                            <div class="stat-label">Total Contributions</div>
                            <span class="icon-box icon-box-success"><i data-lucide="arrow-down"></i></span>
                        </div>
                        <div class="stat-value">{{ $currencySymbol }}{{ number_format($totalContributions, 2) }}</div>
                    </div>
                </div></div>
            </div>
            <div class="col-6 col-xl-4">
                <div class="card stat-tile h-100"><div class="card-body">
                    <div class="stat">
                        <div class="stat-head">
                            <div class="stat-label">Total Withdrawals</div>
                            <span class="icon-box icon-box-danger"><i data-lucide="arrow-up"></i></span>
                        </div>
                        <div class="stat-value">{{ $currencySymbol }}{{ number_format($totalWithdrawals, 2) }}</div>
                    </div>
                </div></div>
            </div>
            <div class="col-12 col-xl-4">
                <div class="card stat-tile h-100"><div class="card-body">
                    <div class="stat">
                        <div class="stat-head">
                            <div class="stat-label">Net Equity Change</div>
                            <span class="icon-box icon-box-primary"><i data-lucide="wallet"></i></span>
                        </div>
                        <div class="stat-value">{{ $currencySymbol }}{{ number_format($totalContributions - $totalWithdrawals, 2) }}</div>
                    </div>
                </div></div>
            </div>
        </div>
    @endunless

    <div class="{{ $standalone ? '' : 'card' }}">
        @unless($standalone)
            <div class="card-header">
                <h2 class="card-title">All Transactions</h2>
                <div class="card-actions">
                    <input wire:model.live.debounce.300ms="search" type="search" class="form-control form-control-sm" style="width:200px" placeholder="Search description...">
                    <select wire:model.live="partnerFilter" class="form-select form-select-sm" style="width:180px">
                        <option value="">All Partners</option>
                        @foreach($partners as $p)
                            <option value="{{ $p['id'] }}">{{ $p['name'] }}</option>
                        @endforeach
                    </select>
                    <select wire:model.live="typeFilter" class="form-select form-select-sm" style="width:150px">
                        <option value="">All Types</option>
                        <option value="1">Contribution</option>
                        <option value="2">Withdrawal</option>
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
                            <th class="text-center">Type</th>
                            <th class="cell-numeric">Amount</th>
                            <th>Description</th>
                            <th>Date</th>
                            <th class="cell-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $trx)
                            <tr>
                                <td>
                                    <div class="cell-user">
                                        <span class="avatar avatar-sm avatar-primary">{{ strtoupper(substr($trx->partner->name ?? 'NA', 0, 2)) }}</span>
                                        <div class="cell-user-body">
                                            <div class="cell-user-name">{{ $trx->partner->name ?? '—' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($trx->type == 1)
                                        <span class="badge badge-soft-success">Contribution</span>
                                    @else
                                        <span class="badge badge-soft-danger">Withdrawal</span>
                                    @endif
                                </td>
                                <td class="cell-numeric font-mono {{ $trx->type == 1 ? 'text-success' : 'text-danger' }}">
                                    {{ $trx->type == 1 ? '+' : '-' }}{{ $currencySymbol }}{{ number_format($trx->amount, 2) }}
                                </td>
                                <td class="text-body-secondary">{{ $trx->description ?? '—' }}</td>
                                <td class="text-body-secondary text-size-sm">
                                    {{ $trx->created_at ? \Carbon\Carbon::parse($trx->created_at)->format('M d, Y') : '—' }}
                                </td>
                                <td class="cell-actions">
                                    @if($canManage)
                                        <button wire:click="deleteTransaction({{ $trx->id }})"
                                                wire:confirm="Are you sure you want to delete this transaction?"
                                                class="btn btn-sm btn-ghost text-danger">
                                            <i data-lucide="trash-2"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-body-secondary">
                                        <i data-lucide="repeat"></i>
                                        <div class="mt-2">No equity transactions found.</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if(! $standalone && $transactions->hasPages())
            <div class="card-footer justify-content-center">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
