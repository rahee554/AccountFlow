{{-- filepath: d:\Repositories\Al-Emaan_Travels\resources\views\vendor\artflow-studio\accountflow\livewire\budgets\budgets-list.blade.php --}}
<div>
    @include(config('accountflow.view_path') . '.blades.dashboard-header')

    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="mb-0">Budgets Overview</h3>
            <div class="small text-muted">High-level KPIs and recent budget activity</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('accountflow::budgets.create') ?? '#' }}" class="btn btn-primary btn-sm">Create Budget</a>
            <button class="btn btn-outline-secondary btn-sm">Export</button>
        </div>
    </div>

    @php
        $totalBudget = collect($budgets ?? [])->sum(fn($b) => data_get($b, 'amount', 0));
        $spent = collect($budgets ?? [])->sum(fn($b) => data_get($b, 'spent', 0));
        $remaining = $totalBudget - $spent;
        $activeCount = collect($budgets ?? [])->filter(fn($b) => data_get($b, 'is_active', true) || data_get($b, 'active', true))->count();
        $totalCount = collect($budgets ?? [])->count();
        $avg = $totalCount ? ($totalBudget / $totalCount) : 0;
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="small text-muted">Total Budget</div>
                    <div class="h4 fw-bold mt-2">{{ number_format($totalBudget,2) }} <small class="text-muted">USD</small></div>
                    <div class="progress mt-3" style="height:6px">
                        <div class="progress-bar bg-primary" role="progressbar" style="width:100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="small text-muted">Spent</div>
                    <div class="h4 text-danger fw-bold mt-2">{{ number_format($spent,2) }}</div>
                    <div class="small text-muted">{{ $totalCount }} budgets • {{ $activeCount }} active</div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="small text-muted">Remaining</div>
                    <div class="h4 text-success fw-bold mt-2">{{ number_format($remaining,2) }}</div>
                    <div class="small text-muted">Avg: {{ number_format($avg,2) }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <div class="small text-muted">Active Budgets</div>
                        <div class="h5 fw-bold mt-1">{{ $activeCount }} / {{ $totalCount }}</div>
                    </div>
                    <div class="text-end">
                        <div class="badge bg-info">Overview</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Budget Trends</h6>
                        <div class="small text-muted">Last 30 days</div>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-outline-secondary">Range</button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="budgetsTrendChart" style="height:260px;"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="mb-0">Top Budgets</h6>
                </div>
                <div class="card-body">
                    @php $top = collect($budgets ?? [])->sortByDesc(fn($b) => data_get($b,'amount',0))->take(5); @endphp
                    @if($top->isEmpty())
                        <div class="small text-muted">No budgets available.</div>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach($top as $t)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-semibold">{{ data_get($t,'title', data_get($t,'category.name','Budget')) }}</div>
                                        <div class="small text-muted">{{ data_get($t,'account.name') ?? '—' }}</div>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold">{{ number_format(data_get($t,'amount',0),2) }}</div>
                                        <div class="small text-muted">{{ number_format(data_get($t,'spent',0),2) }} spent</div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Budgets table --}}
    <div class="card mt-3 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-0">Budgets</h6>
                <div class="small text-muted">Overview of budgets and progress</div>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <input type="text" class="form-control form-control-sm" placeholder="Search budgets..." />
                <select class="form-select form-select-sm w-auto">
                    <option>10</option>
                    <option>25</option>
                    <option>50</option>
                </select>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Budget</th>
                            <th>Account</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Spent</th>
                            <th class="text-end">Remaining</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($budgets ?? [] as $b)
                            @php
                                $amount = data_get($b,'amount',0);
                                $spentItem = data_get($b,'spent',0);
                                $rem = $amount - $spentItem;
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ data_get($b,'title', data_get($b,'category.name','Budget')) }}</div>
                                    <div class="small text-muted">{{ data_get($b,'description') }}</div>
                                </td>
                                <td>{{ data_get($b,'account.name') ?? '—' }}</td>
                                <td class="text-end">{{ number_format($amount,2) }}</td>
                                <td class="text-end text-danger">{{ number_format($spentItem,2) }}</td>
                                <td class="text-end text-success">{{ number_format($rem,2) }}</td>
                                <td class="text-center">
                                    @if(data_get($b,'is_active', data_get($b,'active', true)))
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button wire:click="edit({{ data_get($b,'id') }})" class="btn btn-sm btn-outline-primary">Edit</button>
                                    <button wire:click="delete({{ data_get($b,'id') }})" class="btn btn-sm btn-outline-danger">Delete</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center small text-muted py-3">No budgets yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer d-flex justify-content-between align-items-center small text-muted">
            <div>Showing {{ collect($budgets ?? [])->count() }} budgets</div>
            <div>Last updated: {{ optional(collect($budgets ?? [])->sortByDesc('updated_at')->first()?->updated_at)?->format('M d, Y') ?? '—' }}</div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const ctx = document.getElementById('budgetsTrendChart');
        if (!ctx) return;

        const labels = @json($trends['labels'] ?? []);
        const values = @json($trends['values'] ?? []);

        const demoLabels = labels.length ? labels : Array.from({length:7}).map((_,i) => {
            const d = new Date(); d.setDate(d.getDate() - (6 - i)); return d.toLocaleDateString();
        });
        const demoValues = values.length ? values : [200,350,400,300,450,600,700];

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: demoLabels,
                datasets: [{
                    label: 'Budget trend',
                    data: demoValues,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13,110,253,0.08)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });
    })();
</script>
@endpush