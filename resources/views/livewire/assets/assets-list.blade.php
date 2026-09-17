<div>
    @unless($standalone)
        <div class="page-header">
            <div class="page-header-body">
                <nav class="page-breadcrumb" aria-label="Breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('accountflow::dashboard') }}" wire:navigate>Accounts</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Assets</li>
                    </ol>
                </nav>
                <div class="page-header-title"><h1>Assets</h1></div>
                <p class="page-header-subtitle">Capital assets owned by the business.</p>
            </div>
            <div class="page-header-actions">
                <a href="{{ route('accountflow::assets.transactions') }}" class="btn btn-soft-secondary" wire:navigate>
                    <i data-lucide="receipt"></i> Asset Transactions
                </a>
                @if ($canManage)
                    <a href="{{ route('accountflow::assets.create') }}" class="btn btn-primary" wire:navigate>
                        <i data-lucide="plus"></i> Add Asset
                    </a>
                @endif
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-xl-4">
                <div class="card stat-tile h-100"><div class="card-body">
                    <div class="stat">
                        <div class="stat-head">
                            <div class="stat-label">Total Assets</div>
                            <span class="icon-box icon-box-primary"><i data-lucide="package"></i></span>
                        </div>
                        <div class="stat-value">{{ number_format($totalAssets) }}</div>
                        <div class="stat-meta"><span>{{ $activeAssets }} operating</span></div>
                    </div>
                </div></div>
            </div>
            <div class="col-6 col-xl-4">
                <div class="card stat-tile h-100"><div class="card-body">
                    <div class="stat">
                        <div class="stat-head">
                            <div class="stat-label">Total Value</div>
                            <span class="icon-box icon-box-success"><i data-lucide="wallet"></i></span>
                        </div>
                        <div class="stat-value">{{ config('accountflow.currency_symbols.' . config('accountflow.currency', 'PKR'), config('accountflow.currency', 'PKR') . ' ') }}{{ number_format($totalValue, 0) }}</div>
                        <div class="stat-meta"><span>Across all assets</span></div>
                    </div>
                </div></div>
            </div>
            <div class="col-12 col-xl-4">
                <div class="card stat-tile h-100"><div class="card-body">
                    <div class="stat">
                        <div class="stat-head">
                            <div class="stat-label">Needs Attention</div>
                            <span class="icon-box icon-box-warning"><i data-lucide="circle-alert"></i></span>
                        </div>
                        <div class="stat-value">{{ $totalAssets - $activeAssets }}</div>
                        <div class="stat-meta"><span>Not currently operating</span></div>
                    </div>
                </div></div>
            </div>
        </div>
    @endunless

    <div class="{{ $standalone ? '' : 'card' }}">
        @unless($standalone)
            <div class="card-header">
                <h2 class="card-title">All Assets</h2>
            </div>
            <div class="card-body">
        @endunless

        @livewire('aftable', [
            'model' => 'ArtflowStudio\AccountFlow\Models\Asset',
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
                    'key'   => 'value',
                    'label' => 'Value',
                    'raw'   => '<span class="font-mono cell-numeric">{{ config(\'accountflow.currency_symbols.\' . config(\'accountflow.currency\', \'PKR\'), config(\'accountflow.currency\', \'PKR\') . \' \') }}{{ number_format($row->value, 2) }}</span>',
                ],
                [
                    'key' => 'category_id',
                    'relation' => 'category:name',
                    'label' => 'Category',
                ],
                [
                    'key' => 'status',
                    'label' => 'Status',
                    'raw' => '{!! $row->status == 1 ? "<span class=\"badge badge-soft-success\">Operating Asset</span>" : ($row->status == 2 ? "<span class=\"badge badge-soft-danger\">Not Operating</span>" : "<span class=\"badge badge-soft-secondary\">Sold Out</span>") !!}'
                ],
                [
                    'key' => 'acquisition_date',
                    'label' => 'Date',
                    'raw' => '{{ \Carbon\Carbon::parse($row->acquisition_date)->format("d M Y") }}'
                ],
                [
                    'key'   => 'transactions',
                    'label' => 'Transactions',
                    'raw'   => '{{ \\ArtflowStudio\\AccountFlow\\Models\\AssetTransaction::where("asset_id", $row->id)->count() }}',
                ],
            ],
            'actions' => [
                'raw' => '<a href="{{ route(\'accountflow::assets.edit\', $row->id) }}" class="btn btn-sm btn-ghost" title="Edit"><i data-lucide="pencil"></i></a>
                        <a href="#" data-id="{{ $row->id }}" class="btn btn-sm btn-ghost" title="Assign"><i data-lucide="user"></i></a>
                        <a href="#" data-id="{{ $row->id }}" class="btn btn-sm btn-ghost text-danger" title="Delete"><i data-lucide="trash-2"></i></a>'
            ]
        ])

        @unless($standalone)
            </div>
        @endunless
    </div>
</div>
