@php
    $currencySymbol = $currencySymbol ?? config('accountflow.currency_symbols.' . config('accountflow.currency', 'PKR'), config('accountflow.currency', 'PKR') . ' ');
@endphp

<div>
    <div class="page-header">
        <div class="page-header-body">
            <nav class="page-breadcrumb" aria-label="Breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('accountflow::dashboard') }}" wire:navigate>Accounts</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Trial Balance</li>
                </ol>
            </nav>
            <div class="page-header-title"><h1>Trial Balance</h1></div>
            <p class="page-header-subtitle">Debit and credit summary per account</p>
        </div>
        <div class="page-header-actions">
            <div class="nav nav-segmented" role="tablist" aria-label="Period">
                <button type="button" class="nav-link {{ $period === 'today' ? 'active' : '' }}" wire:click="applyQuickRange('today')">Today</button>
                <button type="button" class="nav-link {{ $period === 'month' ? 'active' : '' }}" wire:click="applyQuickRange('month')">Month</button>
                <button type="button" class="nav-link {{ $period === 'year' ? 'active' : '' }}" wire:click="applyQuickRange('year')">Year</button>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body d-flex flex-wrap gap-2 align-items-center">
            <input type="date" wire:model.live="dateFrom" class="form-control form-control-sm" style="width:150px">
            <span class="text-body-secondary">–</span>
            <input type="date" wire:model.live="dateTo" class="form-control form-control-sm" style="width:150px">
            <input wire:model.live.debounce.300ms="search" type="text" class="form-control form-control-sm" style="width:200px" placeholder="Search accounts...">
            <select wire:model.live="perPage" class="form-select form-select-sm" style="width:90px">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
        </div>
    </div>

    {{-- KPI row --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-4">
            <div class="card stat-tile h-100"><div class="card-body">
                <div class="stat">
                    <div class="stat-head">
                        <div class="stat-label">Total Debits</div>
                        <span class="icon-box icon-box-danger"><i data-lucide="arrow-up"></i></span>
                    </div>
                    <div class="stat-value">{{ $currencySymbol }}{{ number_format($totalDebit, 2) }}</div>
                </div>
            </div></div>
        </div>
        <div class="col-6 col-xl-4">
            <div class="card stat-tile h-100"><div class="card-body">
                <div class="stat">
                    <div class="stat-head">
                        <div class="stat-label">Total Credits</div>
                        <span class="icon-box icon-box-success"><i data-lucide="arrow-down"></i></span>
                    </div>
                    <div class="stat-value">{{ $currencySymbol }}{{ number_format($totalCredit, 2) }}</div>
                </div>
            </div></div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card stat-tile h-100"><div class="card-body">
                <div class="stat">
                    <div class="stat-head">
                        <div class="stat-label">Net Balance</div>
                        <span class="icon-box {{ $totalNet >= 0 ? 'icon-box-primary' : 'icon-box-warning' }}"><i data-lucide="scale"></i></span>
                    </div>
                    <div class="stat-value {{ $totalNet >= 0 ? '' : 'text-danger' }}">{{ $currencySymbol }}{{ number_format(abs($totalNet), 2) }}</div>
                    <div class="stat-meta"><span>{{ $totalNet >= 0 ? 'Surplus' : 'Deficit' }}</span></div>
                </div>
            </div></div>
        </div>
    </div>

    {{-- Main Table Card --}}
    <div class="card">
        <div class="card-header">
            <div><h2 class="card-title">Accounts</h2><p class="card-subtitle">Debit and credit summary per account</p></div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-flush align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Account</th>
                            <th scope="col" class="cell-numeric">Debit</th>
                            <th scope="col" class="cell-numeric">Credit</th>
                            <th scope="col" class="cell-numeric">Net Balance</th>
                            <th scope="col" class="text-center">Detail</th>
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
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="icon-box icon-box-sm icon-box-primary">{{ strtoupper(substr($acct->name, 0, 2)) }}</span>
                                        <div>
                                            <span class="fw-semibold d-block text-size-sm">{{ $acct->name }}</span>
                                            @if(!empty($acct->code))
                                                <span class="text-body-secondary text-size-sm">{{ $acct->code }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="cell-numeric">
                                    <span class="font-mono {{ $agg['debit'] > 0 ? 'text-danger' : 'text-body-secondary' }}">
                                        {{ $currencySymbol }}{{ number_format($agg['debit'], 2) }}
                                    </span>
                                </td>
                                <td class="cell-numeric">
                                    <span class="font-mono {{ $agg['credit'] > 0 ? 'text-success' : 'text-body-secondary' }}">
                                        {{ $currencySymbol }}{{ number_format($agg['credit'], 2) }}
                                    </span>
                                </td>
                                <td class="cell-numeric">
                                    @if($net > 0)
                                        <span class="badge badge-soft-success font-mono">+ {{ $currencySymbol }}{{ number_format($net, 2) }}</span>
                                    @elseif($net < 0)
                                        <span class="badge badge-soft-danger font-mono">{{ $currencySymbol }}{{ number_format($net, 2) }}</span>
                                    @else
                                        <span class="badge badge-soft-secondary font-mono">{{ $currencySymbol }}0.00</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(!empty($categories))
                                        <button class="btn btn-ghost btn-sm" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="false">
                                            <i data-lucide="chevron-down"></i>
                                        </button>
                                    @else
                                        <span class="text-body-secondary">–</span>
                                    @endif
                                </td>
                            </tr>

                            @if(!empty($categories))
                                <tr class="collapse-row">
                                    <td colspan="5" class="p-0 border-0">
                                        <div class="collapse" id="{{ $collapseId }}">
                                            <div class="bg-body-secondary rounded mx-4 mb-3 p-3">
                                                <div class="text-size-sm fw-bold text-primary mb-2">
                                                    <i data-lucide="tag"></i> Category Breakdown — {{ $acct->name }}
                                                </div>
                                                <table class="table table-flush table-sm mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Category</th>
                                                            <th class="cell-numeric">Debit</th>
                                                            <th class="cell-numeric">Credit</th>
                                                            <th class="cell-numeric">Net</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($categories as $cid => $c)
                                                            @php $cnet = ($c['credit'] ?? 0) - ($c['debit'] ?? 0); @endphp
                                                            <tr>
                                                                <td class="text-size-sm">{{ $c['category_name'] ?? ('# ' . $cid) }}</td>
                                                                <td class="cell-numeric font-mono text-danger text-size-sm">{{ $currencySymbol }}{{ number_format($c['debit'] ?? 0, 2) }}</td>
                                                                <td class="cell-numeric font-mono text-success text-size-sm">{{ $currencySymbol }}{{ number_format($c['credit'] ?? 0, 2) }}</td>
                                                                <td class="cell-numeric font-mono text-size-sm">
                                                                    @if($cnet > 0)
                                                                        <span class="text-success fw-bold">+{{ $currencySymbol }}{{ number_format($cnet, 2) }}</span>
                                                                    @elseif($cnet < 0)
                                                                        <span class="text-danger fw-bold">{{ $currencySymbol }}{{ number_format($cnet, 2) }}</span>
                                                                    @else
                                                                        <span class="text-body-secondary">{{ $currencySymbol }}0.00</span>
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
                                <td colspan="5" class="text-center py-5 text-body-secondary">
                                    <i data-lucide="scale"></i><br>
                                    No accounts found for the selected period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold border-top">
                            <td>
                                <span class="badge badge-soft-secondary">
                                    Totals {{ $accounts->firstItem() ?? 0 }}–{{ $accounts->lastItem() ?? 0 }} of {{ $accounts->total() ?? 0 }} accounts
                                </span>
                            </td>
                            <td class="cell-numeric font-mono text-danger">{{ $currencySymbol }}{{ number_format($totalDebit, 2) }}</td>
                            <td class="cell-numeric font-mono text-success">{{ $currencySymbol }}{{ number_format($totalCredit, 2) }}</td>
                            <td class="cell-numeric font-mono {{ $totalNet >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $totalNet >= 0 ? '+' : '' }}{{ $currencySymbol }}{{ number_format($totalNet, 2) }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="p-3">{{ $accounts->links() }}</div>
        </div>
    </div>
</div>
