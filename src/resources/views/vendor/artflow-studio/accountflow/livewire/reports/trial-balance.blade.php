<div>
    @include(config('accountflow.view_path') . '.blades.dashboard-header')

    <div class="card mt-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-0">Trial Balance</h4>
                    <small class="text-muted">A snapshot of debits and credits per account</small>
                </div>
                <div class="d-flex gap-2">
                    <button wire:click="applyQuickRange('today')" class="btn btn-sm btn-outline-primary">Today</button>
                    <button wire:click="applyQuickRange('month')" class="btn btn-sm btn-outline-primary">This Month</button>
                    <button wire:click="applyQuickRange('year')" class="btn btn-sm btn-outline-primary">This Year</button>
                </div>
            </div>

            <div class="row mb-3 g-2">
                <div class="col-md-3">
                    <input type="date" wire:model="dateFrom" class="form-control form-control-sm" />
                </div>
                <div class="col-md-3">
                    <input type="date" wire:model="dateTo" class="form-control form-control-sm" />
                </div>
                <div class="col-md-3 ms-auto d-flex justify-content-end">
                    <select wire:model="perPage" class="form-select form-select-sm w-auto">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Account</th>
                            <th class="text-end">Debit</th>
                            <th class="text-end">Credit</th>
                            <th class="text-end">Net</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($accounts as $acct)
                            @php
                                $agg = $txAgg[$acct->id] ?? ['debit'=>0,'credit'=>0,'net'=>0];
                                $categories = $categoryAgg[$acct->id] ?? [];
                                $collapseId = 'acct-cat-' . $acct->id;
                            @endphp

                            <tr class="table-primary" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="false" style="cursor:pointer;">
                                <td>
                                    <strong>{{ $acct->name }}</strong>
                                    <div class="text-muted small">{{ $acct->code ?? '' }} • {{ $acct->type ?? '' }}</div>
                                </td>
                                <td class="text-end">{{ number_format($agg['debit'],2) }}</td>
                                <td class="text-end">{{ number_format($agg['credit'],2) }}</td>
                                <td class="text-end">{{ number_format($agg['net'],2) }}</td>
                            </tr>

                            <tr class="collapse-row">
                                <td colspan="4" class="p-0">
                                    <div class="collapse" id="{{ $collapseId }}">
                                        <div class="card mb-2">
                                            <div class="card-body p-3">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <div><strong>Category breakdown</strong></div>
                                                    <div class="text-muted small">{{ number_format($agg['debit'],2) }} debit • {{ number_format($agg['credit'],2) }} credit</div>
                                                </div>

                                                @if(empty($categories))
                                                    <div class="text-muted small">No transactions for selected period.</div>
                                                @else
                                                    <div class="table-responsive">
                                                        <table class="table table-sm mb-0">
                                                            <thead>
                                                                <tr class="table-light">
                                                                    <th>Category</th>
                                                                    <th class="text-end">Debit</th>
                                                                    <th class="text-end">Credit</th>
                                                                    <th class="text-end">Net</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($categories as $cid => $c)
                                                                    @php
                                                                        $net = ($c['credit'] ?? 0) - ($c['debit'] ?? 0);
                                                                    @endphp
                                                                    <tr>
                                                                        <td>{{ $c['category_name'] ?? ('#' . $cid) }}</td>
                                                                        <td class="text-end">{{ number_format($c['debit'] ?? 0,2) }}</td>
                                                                        <td class="text-end">{{ number_format($c['credit'] ?? 0,2) }}</td>
                                                                        <td class="text-end">{{ number_format($net,2) }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th class="text-end">Totals</th>
                            <th class="text-end">{{ number_format($totalDebit,2) }}</th>
                            <th class="text-end">{{ number_format($totalCredit,2) }}</th>
                            <th class="text-end">{{ number_format($totalNet,2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted small">Showing {{ $accounts->firstItem() ?? 0 }} to {{ $accounts->lastItem() ?? 0 }} of {{ $accounts->total() ?? 0 }} accounts</div>
                <div>
                    {{ $accounts->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
