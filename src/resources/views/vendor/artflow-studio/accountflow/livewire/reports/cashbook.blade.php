<div class="">
    @include(config('accountflow.view_path') . '.blades.dashboard-header')

    <div class="card mt-4 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="mb-0">Cashbook Report</h3>
                    <small class="text-muted">Overview of cash transactions</small>
                </div>
                <div class="text-end">
                    <div class="btn-group" role="group">
                        <button wire:click="applyQuickRange('today')" type="button" class="btn btn-sm btn-outline-primary">Today</button>
                        <button wire:click="applyQuickRange('month')" type="button" class="btn btn-sm btn-outline-primary">This Month</button>
                        <button wire:click="applyQuickRange('year')" type="button" class="btn btn-sm btn-outline-primary">This Year</button>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label">Account</label>
                    <select wire:model="accountId" class="form-select form-select-sm">
                        <option value="">All Accounts</option>
                        @foreach($accounts as $acct)
                            <option value="{{ $acct->id }}">{{ $acct->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">From</label>
                    <input wire:model.defer="dateFrom" type="date" class="form-control form-control-sm" />
                </div>
                <div class="col-md-3">
                    <label class="form-label">To</label>
                    <input wire:model.defer="dateTo" type="date" class="form-control form-control-sm" />
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button wire:click="$refresh" class="btn btn-sm btn-primary w-100">Apply</button>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="bg-light p-3 rounded">
                        <div class="d-flex justify-content-between">
                            <div>
                                <small class="text-muted">Total Debit</small>
                                <div class="h5">{{ number_format($totalDebit,2) }}</div>
                            </div>
                            <div>
                                <small class="text-muted">Total Credit</small>
                                <div class="h5">{{ number_format($totalCredit,2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <div class="d-inline-block">
                        <label class="form-label d-block">Per page</label>
                        <select wire:model="perPage" class="form-select form-select-sm">
                            <option>10</option>
                            <option>25</option>
                            <option>50</option>
                            <option>100</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Account</th>
                            <th>Category</th>
                            <th class="text-end">Debit</th>
                            <th class="text-end">Credit</th>
                            <th class="text-end">Balance</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $running = 0; @endphp
                        @foreach($transactions as $tx)
                            @php
                                // type codes: 1 = income/credit, 2 = expense/debit
                                $debit = $tx->type == 2 ? $tx->amount : 0;
                                $credit = $tx->type == 1 ? $tx->amount : 0;
                                $running += ($credit - $debit);
                                $typeLabel = $tx->type == 1 ? 'Income' : ($tx->type == 2 ? 'Expense' : 'Unknown');
                            @endphp
                            <tr>
                                <td>{{ optional($tx->date)->format('Y-m-d') }}</td>
                                <td>{{ $tx->account->name ?? '—' }}</td>
                                <td>{{ $tx->category->name ?? '—' }}</td>
                                <td class="text-end">{{ $debit ? number_format($debit,2) : '' }}</td>
                                <td class="text-end">{{ $credit ? number_format($credit,2) : '' }}</td>
                                <td class="text-end">{{ number_format($running,2) }}</td>
                                <td>{{ Str::limit($tx->description,60) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Showing {{ $transactions->firstItem() ?? 0 }} to {{ $transactions->lastItem() ?? 0 }} of {{ $transactions->total() }} entries
                </div>
                <div>
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
