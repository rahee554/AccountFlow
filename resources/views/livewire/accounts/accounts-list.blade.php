<div>
    @unless($standalone)
        <div class="page-header">
            <div class="page-header-body">
                <nav class="page-breadcrumb" aria-label="Breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('accountflow::dashboard') }}" wire:navigate>Accounts</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Accounts</li>
                    </ol>
                </nav>
                <div class="page-header-title"><h1>Accounts</h1></div>
                <p class="page-header-subtitle">Cash, bank and wallet accounts you post transactions against.</p>
            </div>
            <div class="page-header-actions">
                @if ($canTransfer)
                    <a href="{{ route('accountflow::transfers.create') }}" class="btn btn-soft-secondary" wire:navigate>
                        <i data-lucide="repeat"></i> Transfer Funds
                    </a>
                @endif
                @if ($canManage)
                    <a href="{{ route('accountflow::accounts.create') }}" class="btn btn-primary" wire:navigate>
                        <i data-lucide="plus"></i> Add Account
                    </a>
                @endif
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-xl-4">
                <div class="card stat-tile h-100"><div class="card-body">
                    <div class="stat">
                        <div class="stat-head">
                            <div class="stat-label">Total Accounts</div>
                            <span class="icon-box icon-box-primary"><i data-lucide="landmark"></i></span>
                        </div>
                        <div class="stat-value">{{ number_format($totalAccounts) }}</div>
                        <div class="stat-meta"><span>{{ $activeAccounts }} active</span></div>
                    </div>
                </div></div>
            </div>
            <div class="col-6 col-xl-4">
                <div class="card stat-tile h-100"><div class="card-body">
                    <div class="stat">
                        <div class="stat-head">
                            <div class="stat-label">Total Balance</div>
                            <span class="icon-box icon-box-success"><i data-lucide="wallet"></i></span>
                        </div>
                        <div class="stat-value">{{ config('accountflow.currency_symbols.' . config('accountflow.currency', 'PKR'), config('accountflow.currency', 'PKR') . ' ') }}{{ number_format($totalBalance, 0) }}</div>
                        <div class="stat-meta"><span>Across all accounts</span></div>
                    </div>
                </div></div>
            </div>
            <div class="col-12 col-xl-4">
                <div class="card stat-tile h-100"><div class="card-body">
                    <div class="stat">
                        <div class="stat-head">
                            <div class="stat-label">Need Attention</div>
                            <span class="icon-box icon-box-warning"><i data-lucide="circle-alert"></i></span>
                        </div>
                        <div class="stat-value">{{ $totalAccounts - $activeAccounts }}</div>
                        <div class="stat-meta"><span>Inactive accounts</span></div>
                    </div>
                </div></div>
            </div>
        </div>
    @endunless

    <div class="{{ $standalone ? '' : 'card' }}">
        @unless($standalone)
            <div class="card-header">
                <h2 class="card-title">All Accounts</h2>
            </div>
            <div class="card-body">
        @endunless

        @livewire('aftable', [
            'model' => 'ArtflowStudio\AccountFlow\Models\Account',
            'columns' => [
                ['key' => 'name', 'label' => 'Account Title'],
                ['key' => 'description', 'label' => 'Description'],
                ['key' => 'balance', 'label' => 'Balance', 'raw' => '<span class="font-mono cell-numeric">{{ config(\'accountflow.currency_symbols.\' . config(\'accountflow.currency\', \'PKR\'), config(\'accountflow.currency\', \'PKR\') . \' \') }}{{ number_format($row->balance, 2) }}</span>'],
                ['key' => 'active', 'label' => 'Status', 'raw' => '{!! $row->active == true ? "<span class=\"badge badge-soft-success\">Active</span>" : "<span class=\"badge badge-soft-secondary\">Inactive</span>" !!}'],
            ],
            'actions' => [
                'raw' => "<a href='{{ route('accountflow::accounts.edit', \$row->id) }}' class='btn btn-sm btn-ghost'>Edit</a>"
            ]
        ])

        @unless($standalone)
            </div>
        @endunless
    </div>
</div>
