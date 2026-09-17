@php
    $currencySymbol = $currencySymbol ?? config('accountflow.currency_symbols.' . config('accountflow.currency', 'PKR'), config('accountflow.currency', 'PKR') . ' ');
@endphp

<div>
    <div class="page-header">
        <div class="page-header-body">
            <nav class="page-breadcrumb" aria-label="Breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('accountflow::dashboard') }}" wire:navigate>Accounts</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Cashbook</li>
                </ol>
            </nav>
            <div class="page-header-title"><h1>Cashbook Report</h1></div>
            <p class="page-header-subtitle">Overview of cash transactions</p>
        </div>
        <div class="page-header-actions">
            <div class="nav nav-segmented" role="tablist" aria-label="Period">
                <button type="button" class="nav-link" wire:click.prevent="applyQuickRange('today')">Today</button>
                <button type="button" class="nav-link" wire:click.prevent="applyQuickRange('month')">This Month</button>
                <button type="button" class="nav-link" wire:click.prevent="applyQuickRange('year')">This Year</button>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Account</label>
                    <select wire:model="accountId" class="form-select form-select-sm">
                        <option value="">All Accounts</option>
                        @foreach($accounts as $acct)
                            <option value="{{ $acct['id'] }}">{{ $acct['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">From</label>
                    <input wire:model="dateFrom" type="date" class="form-control form-control-sm" />
                </div>
                <div class="col-md-3">
                    <label class="form-label">To</label>
                    <input wire:model="dateTo" type="date" class="form-control form-control-sm" />
                </div>
                <div class="col-md-2">
                    <button wire:click="$refresh" class="btn btn-sm btn-primary w-100">Apply</button>
                </div>
            </div>
        </div>
    </div>

    {{-- KPI row --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card stat-tile h-100"><div class="card-body">
                <div class="stat">
                    <div class="stat-head">
                        <div class="stat-label">Total Debit</div>
                        <span class="icon-box icon-box-danger"><i data-lucide="arrow-up"></i></span>
                    </div>
                    <div class="stat-value">{{ $currencySymbol }}{{ number_format($totalDebit, 2) }}</div>
                </div>
            </div></div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card stat-tile h-100"><div class="card-body">
                <div class="stat">
                    <div class="stat-head">
                        <div class="stat-label">Total Credit</div>
                        <span class="icon-box icon-box-success"><i data-lucide="arrow-down"></i></span>
                    </div>
                    <div class="stat-value">{{ $currencySymbol }}{{ number_format($totalCredit, 2) }}</div>
                </div>
            </div></div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-header">
            <div><h2 class="card-title">Transactions</h2></div>
            <div class="card-actions">
                <select wire:model="perPage" class="form-select form-select-sm" style="width:80px">
                    <option>10</option>
                    <option>25</option>
                    <option>50</option>
                    <option>100</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-flush table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Date</th>
                            <th scope="col">Account</th>
                            <th scope="col">Category</th>
                            <th scope="col" class="cell-numeric">Debit</th>
                            <th scope="col" class="cell-numeric">Credit</th>
                            <th scope="col" class="cell-numeric">Balance</th>
                            <th scope="col">Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $running = 0; @endphp
                        @forelse($transactions as $tx)
                            @php
                                // type codes: 1 = income/credit, 2 = expense/debit
                                $debit = $tx->type == 2 ? $tx->amount : 0;
                                $credit = $tx->type == 1 ? $tx->amount : 0;
                                $running += ($credit - $debit);
                            @endphp
                            <tr>
                                <td class="text-body-secondary text-nowrap">{{ optional($tx->date)->format('Y-m-d') }}</td>
                                <td>{{ $tx->account->name ?? '—' }}</td>
                                <td><span class="badge badge-soft-secondary">{{ $tx->category->name ?? '—' }}</span></td>
                                <td class="cell-numeric font-mono text-danger">{{ $debit ? $currencySymbol . number_format($debit, 2) : '' }}</td>
                                <td class="cell-numeric font-mono text-success">{{ $credit ? $currencySymbol . number_format($credit, 2) : '' }}</td>
                                <td class="cell-numeric font-mono">{{ $currencySymbol }}{{ number_format($running, 2) }}</td>
                                <td class="text-body-secondary text-size-sm">{{ Str::limit($tx->description, 60) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-body-secondary">No transactions found for the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center p-3">
                <span class="text-body-secondary text-size-sm">
                    Showing {{ $transactions->firstItem() ?? 0 }} to {{ $transactions->lastItem() ?? 0 }} of {{ $transactions->total() }} entries
                </span>
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</div>
