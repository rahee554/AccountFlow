<div>
    @unless($standalone)
        <div class="page-header">
            <div class="page-header-body">
                <nav class="page-breadcrumb" aria-label="Breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('accountflow::dashboard') }}" wire:navigate>Accounts</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Planned Payments</li>
                    </ol>
                </nav>
                <div class="page-header-title"><h1>Planned Payments</h1></div>
                <p class="page-header-subtitle">Scheduled and recurring payments you plan to post.</p>
            </div>
            @if ($canManage)
                <div class="page-header-actions">
                    <a href="{{ route('accountflow::planned-payments.create') }}" class="btn btn-primary" wire:navigate>
                        <i data-lucide="plus"></i> Add Planned Payment
                    </a>
                </div>
            @endif
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-xl-4">
                <div class="card stat-tile h-100"><div class="card-body">
                    <div class="stat">
                        <div class="stat-head">
                            <div class="stat-label">Total Planned</div>
                            <span class="icon-box icon-box-primary"><i data-lucide="calendar-clock"></i></span>
                        </div>
                        <div class="stat-value">{{ config('accountflow.currency_symbols.' . config('accountflow.currency', 'PKR'), config('accountflow.currency', 'PKR') . ' ') }}{{ number_format($totalPlanned, 0) }}</div>
                        <div class="stat-meta"><span>All scheduled payments</span></div>
                    </div>
                </div></div>
            </div>
            <div class="col-6 col-xl-4">
                <div class="card stat-tile h-100"><div class="card-body">
                    <div class="stat">
                        <div class="stat-head">
                            <div class="stat-label">Due This Week</div>
                            <span class="icon-box icon-box-warning"><i data-lucide="circle-alert"></i></span>
                        </div>
                        <div class="stat-value">{{ number_format($upcomingCount) }}</div>
                        <div class="stat-meta"><span>Next 7 days</span></div>
                    </div>
                </div></div>
            </div>
            <div class="col-12 col-xl-4">
                <div class="card stat-tile h-100"><div class="card-body">
                    <div class="stat">
                        <div class="stat-head">
                            <div class="stat-label">Auto-Posting</div>
                            <span class="icon-box icon-box-success"><i data-lucide="repeat"></i></span>
                        </div>
                        <div class="stat-value">{{ number_format($autoPostCount) }}</div>
                        <div class="stat-meta"><span>Post automatically on due date</span></div>
                    </div>
                </div></div>
            </div>
        </div>
    @endunless

    <div class="{{ $standalone ? '' : 'card' }}">
        @unless($standalone)
            <div class="card-header">
                <h2 class="card-title">All Planned Payments</h2>
            </div>
            <div class="card-body">
        @endunless

        @livewire('aftable', [
            'model' => 'ArtflowStudio\AccountFlow\Models\PlannedPayment',
            'columns' => [
                [
                    'key' => 'name',
                    'label' => 'Name',
                ],
                [
                    'key' => 'description',
                    'label' => 'Description',
                ],
                [
                    'key'   => 'amount',
                    'label' => 'Amount',
                    'raw'   => '<span class="font-mono cell-numeric">{{ config(\'accountflow.currency_symbols.\' . config(\'accountflow.currency\', \'PKR\'), config(\'accountflow.currency\', \'PKR\') . \' \') }}{{ number_format($row->amount, 2) }}</span>',
                ],
                [
                    'key'      => 'category_id',
                    'relation' => 'category:name',
                    'label'    => 'Category',
                    'raw'      => '<span>{!! $row->category ? "<img src=\"" . asset(config(\'accountflow.asset_path\') . \'icons/accounts_icons/\' . $row->category->icon) . "\" alt=\"" . $row->category->name . "\" class=\"me-2\" style=\"height:20px;width:20px;object-fit:contain\">" . $row->category->name : "—" !!}</span>',
                ],
                [
                    'key'   => 'next_run_date',
                    'label' => 'Next Due',
                    'raw'   => '{{ $row->next_run_date ? \Carbon\Carbon::parse($row->next_run_date)->format("d M Y") : "—" }}',
                ],
                [
                    'key'   => 'last_run_date',
                    'label' => 'Status',
                    'raw'   => '{!! $row->last_run_date ? "<span class=\"badge badge-soft-success\">Posted</span>" : "<span class=\"badge badge-soft-warning\">Pending</span>" !!}',
                ],
            ],
            'actions' => $canManage ? [
                'raw' => '<div class="d-flex gap-1 justify-content-end">
                        <a href="{{ route(\'accountflow::planned-payments.edit\', $row->id) }}" class="btn btn-sm btn-ghost"><i data-lucide="pencil"></i></a>
                        <a href="#" class="btn btn-sm btn-ghost"><i data-lucide="user"></i></a>
                        <a href="#" class="btn btn-sm btn-ghost text-danger"><i data-lucide="trash-2"></i></a>
                    </div>'
            ] : []
        ])

        @unless($standalone)
            </div>
        @endunless
    </div>
</div>
