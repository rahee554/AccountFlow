<div>
    {{-- Because she competes with no one, no one can compete with her. --}}
        @include(config('accountflow.view_path') . '.blades.dashboard-header')
        <div class="container">
            <!-- Overview cards (Metronic-style) -->
            <div class="row g-4 mb-6">
                <div class="col-lg-3">
                    <div class="card shadow-sm">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small">Total Loans</div>
                                <div class="fs-2 fw-bolder">{{ $stats['total_loans'] ?? 0 }}</div>
                            </div>
                            <div class="symbol symbol-45px bg-light-primary text-primary rounded-circle d-flex align-items-center justify-content-center">
                                <!-- icon -->
                                <svg class="svg-icon svg-icon-2 text-primary" width="24" height="24" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" d="M12 8v8m4-4H8"/></svg>
                            </div>
                        </div>
                        <div class="card-footer py-2 text-muted small">Active, paid & pending loans combined</div>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="card shadow-sm">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small">Outstanding Amount</div>
                                <div class="fs-2 fw-bolder">${{ number_format($stats['outstanding_amount'] ?? 0, 2) }}</div>
                            </div>
                            <div class="symbol symbol-45px bg-light-warning text-warning rounded-circle d-flex align-items-center justify-content-center">
                                <svg class="svg-icon svg-icon-2 text-warning" width="24" height="24" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            </div>
                        </div>
                        <div class="card-footer py-2 text-muted small">Sum of unpaid principal + interest</div>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="card shadow-sm">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small">Overdue Loans</div>
                                <div class="fs-2 fw-bolder">{{ $stats['overdue_count'] ?? 0 }}</div>
                            </div>
                            <div class="symbol symbol-45px bg-light-danger text-danger rounded-circle d-flex align-items-center justify-content-center">
                                <svg class="svg-icon svg-icon-2 text-danger" width="24" height="24" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 3"/></svg>
                            </div>
                        </div>
                        <div class="card-footer py-2 text-muted small">Loans past due date</div>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="card shadow-sm">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small">Avg. Interest Rate</div>
                                <div class="fs-2 fw-bolder">{{ number_format($stats['avg_rate'] ?? 0, 2) }}%</div>
                            </div>
                            <div class="symbol symbol-45px bg-light-success text-success rounded-circle d-flex align-items-center justify-content-center">
                                <svg class="svg-icon svg-icon-2 text-success" width="24" height="24" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" d="M3 10h4l3 10 4-18 3 8h4"/></svg>
                            </div>
                        </div>
                        <div class="card-footer py-2 text-muted small">Simple average across active loans</div>
                    </div>
                </div>
            </div>

            <!-- Toolbar (Metronic-style) -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="input-group input-group-sm">
                        <input type="text" wire:model.debounce.500ms="search" class="form-control form-control-solid" placeholder="Search loans, borrower..." aria-label="Search loans">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                    </div>

                    <select wire:model="status" class="form-select form-select-sm">
                        <option value="">All statuses</option>
                        <option value="active">Active</option>
                        <option value="paid">Paid</option>
                        <option value="overdue">Overdue</option>
                    </select>

                    <select wire:model="perPage" class="form-select form-select-sm">
                        <option value="10">10 / page</option>
                        <option value="25">25 / page</option>
                        <option value="50">50 / page</option>
                    </select>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a href="#" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> New Loan
                    </a>
                    <button wire:click="exportCsv" type="button" class="btn btn-sm btn-outline-secondary">Export CSV</button>
                </div>
            </div>

            <!-- Loans table -->
            <div class="bg-white/80 dark:bg-slate-800 rounded-lg shadow overflow-hidden">
                <div class="w-full overflow-x-auto">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="py-3">#</th>
                                <th class="py-3">Borrower</th>
                                <th class="py-3">Amount</th>
                                <th class="py-3">Status</th>
                                <th class="py-3">Start</th>
                                <th class="py-3">Due</th>
                                <th class="py-3 text-end">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="bg-white dark:bg-transparent divide-y divide-slate-100 dark:divide-slate-700">
                            @php
                                $dummyLoans = [
                                    [
                                        'id' => 101,
                                        'borrower' => ['name' => 'Alice Smith', 'email' => 'alice@example.com'],
                                        'amount' => 5000,
                                        'status' => 'active',
                                        'start_date' => \Carbon\Carbon::now()->subMonths(6)->format('Y-m-d'),
                                        'due_date' => \Carbon\Carbon::now()->addMonths(6)->format('Y-m-d'),
                                    ],
                                    [
                                        'id' => 102,
                                        'borrower' => ['name' => 'Bob Johnson', 'email' => 'bob.j@example.com'],
                                        'amount' => 12000,
                                        'status' => 'paid',
                                        'start_date' => \Carbon\Carbon::now()->subYears(1)->format('Y-m-d'),
                                        'due_date' => \Carbon\Carbon::now()->subMonths(1)->format('Y-m-d'),
                                    ],
                                    [
                                        'id' => 103,
                                        'borrower' => ['name' => 'Carla Reyes', 'email' => 'carla.r@example.com'],
                                        'amount' => 2500,
                                        'status' => 'overdue',
                                        'start_date' => \Carbon\Carbon::now()->subMonths(10)->format('Y-m-d'),
                                        'due_date' => \Carbon\Carbon::now()->subDays(10)->format('Y-m-d'),
                                    ],
                                    [
                                        'id' => 104,
                                        'borrower' => ['name' => 'Daniel Lee', 'email' => 'daniel@example.com'],
                                        'amount' => 800,
                                        'status' => 'active',
                                        'start_date' => \Carbon\Carbon::now()->subMonths(2)->format('Y-m-d'),
                                        'due_date' => \Carbon\Carbon::now()->addMonths(10)->format('Y-m-d'),
                                    ],
                                    [
                                        'id' => 105,
                                        'borrower' => ['name' => 'Eve Thompson', 'email' => 'eve.t@example.com'],
                                        'amount' => 4500,
                                        'status' => 'active',
                                        'start_date' => \Carbon\Carbon::now()->subMonths(3)->format('Y-m-d'),
                                        'due_date' => \Carbon\Carbon::now()->addMonths(9)->format('Y-m-d'),
                                    ],
                                ];
                            @endphp

                            @foreach ($dummyLoans as $loan)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/30">
                                    <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ $loan['id'] }}</td>

                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-sm font-medium text-slate-700 dark:text-slate-200">
                                                {{ strtoupper(substr($loan['borrower']['name'],0,1)) }}
                                            </div>
                                            <div class="text-sm">
                                                <div class="font-medium text-slate-900 dark:text-white">{{ $loan['borrower']['name'] }}</div>
                                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $loan['borrower']['email'] }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">${{ number_format($loan['amount'] ?? 0, 2) }}</td>

                                    <td class="px-4 py-3">
                                        @php $status = $loan['status'] ?? 'unknown'; @endphp
                                        @if ($status === 'active')
                                            <span class="badge bg-success">Active</span>
                                        @elseif ($status === 'paid')
                                            <span class="badge bg-secondary">Paid</span>
                                        @elseif ($status === 'overdue')
                                            <span class="badge bg-danger">Overdue</span>
                                        @else
                                            <span class="badge bg-warning">Unknown</span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $loan['start_date'] }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">{{ $loan['due_date'] }}</td>

                                    <td class="px-4 py-3 text-right text-sm">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="#" class="btn btn-sm btn-light" title="View"><i class="bi bi-eye"></i></a>
                                            <a href="#" class="btn btn-sm btn-light" title="Edit"><i class="bi bi-pencil"></i></a>
                                            <button type="button" class="btn btn-sm btn-light text-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3 border-t border-slate-100 dark:border-slate-700 flex items-center justify-between">
                    @php
                        if (isset($loans) && is_object($loans) && method_exists($loans, 'firstItem')) {
                            $firstItem = $loans->firstItem() ?? 0;
                            $lastItem = $loans->lastItem() ?? 0;
                            $totalItems = $loans->total() ?? 0;
                            $hasLinks = true;
                        } else {
                            $firstItem = count($dummyLoans) ? 1 : 0;
                            $lastItem = count($dummyLoans);
                            $totalItems = count($dummyLoans);
                            $hasLinks = false;
                        }
                    @endphp

                    <div class="text-xs text-slate-500 dark:text-slate-400">
                        Showing {{ $firstItem }} to {{ $lastItem }} of {{ $totalItems }} results
                    </div>

                    <div>
                        @if($hasLinks)
                            {{ $loans->links() }}
                        @else
                            <nav class="inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                <a href="#" class="px-3 py-1 rounded-l-md border border-slate-200 bg-white text-sm text-slate-500">Prev</a>
                                <a href="#" class="px-3 py-1 border-t border-b border-slate-200 bg-white text-sm text-slate-700">1</a>
                                <a href="#" class="px-3 py-1 rounded-r-md border border-slate-200 bg-white text-sm text-slate-500">Next</a>
                            </nav>
                        @endif
                    </div>
                </div>
            </div>
        </div>
</div>
