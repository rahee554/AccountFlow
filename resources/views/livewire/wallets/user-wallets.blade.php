<div>
    @unless($standalone)
        <div class="page-header">
            <div class="page-header-body">
                <nav class="page-breadcrumb" aria-label="Breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('accountflow::dashboard') }}" wire:navigate>Accounts</a></li>
                        <li class="breadcrumb-item active" aria-current="page">User Wallets</li>
                    </ol>
                </nav>
                <div class="page-header-title"><h1>User Wallets</h1></div>
                <p class="page-header-subtitle">Cash floats and advances held by your team.</p>
            </div>
            @if ($canManage)
                <div class="page-header-actions">
                    <a href="{{ route('accountflow::users.wallets.create') }}" class="btn btn-primary" wire:navigate>
                        <i data-lucide="repeat"></i> Wallet Transfer
                    </a>
                </div>
            @endif
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-xl-4">
                <div class="card stat-tile h-100"><div class="card-body">
                    <div class="stat">
                        <div class="stat-head">
                            <div class="stat-label">Total Wallets</div>
                            <span class="icon-box icon-box-primary"><i data-lucide="wallet"></i></span>
                        </div>
                        <div class="stat-value">{{ number_format($totalWallets) }}</div>
                        <div class="stat-meta"><span>{{ $activeWallets }} active</span></div>
                    </div>
                </div></div>
            </div>
            <div class="col-6 col-xl-4">
                <div class="card stat-tile h-100"><div class="card-body">
                    <div class="stat">
                        <div class="stat-head">
                            <div class="stat-label">Total Outstanding</div>
                            <span class="icon-box icon-box-success"><i data-lucide="banknote"></i></span>
                        </div>
                        <div class="stat-value">{{ config('accountflow.currency_symbols.' . config('accountflow.currency', 'PKR'), config('accountflow.currency', 'PKR') . ' ') }}{{ number_format($totalOutstanding, 0) }}</div>
                        <div class="stat-meta"><span>Held across all wallets</span></div>
                    </div>
                </div></div>
            </div>
            <div class="col-12 col-xl-4">
                <div class="card stat-tile h-100"><div class="card-body">
                    <div class="stat">
                        <div class="stat-head">
                            <div class="stat-label">Frozen</div>
                            <span class="icon-box icon-box-warning"><i data-lucide="circle-alert"></i></span>
                        </div>
                        <div class="stat-value">{{ $totalWallets - $activeWallets }}</div>
                        <div class="stat-meta"><span>Wallets frozen</span></div>
                    </div>
                </div></div>
            </div>
        </div>
    @endunless

    <div class="{{ $standalone ? '' : 'card' }}">
        @unless($standalone)
            <div class="card-header">
                <h2 class="card-title">All Wallets</h2>
            </div>
            <div class="card-body">
        @endunless

        @livewire('aftable', [
            'model' => 'ArtflowStudio\AccountFlow\Models\UserWallet',
            'columns' => [
                ['key' => 'user_id', 'label' => 'Team Member', 'relation' => 'user:name'],
                ['key' => 'balance', 'label' => 'Balance', 'raw' => '<span class="font-mono cell-numeric">{{ config(\'accountflow.currency_symbols.\' . config(\'accountflow.currency\', \'PKR\'), config(\'accountflow.currency\', \'PKR\') . \' \') }}{{ number_format($row->balance, 2) }}</span>'],
                ['key' => 'status', 'label' => 'Status', 'raw' => '{!! (int) $row->status === 1 ? "<span class=\"badge badge-soft-success\">Active</span>" : "<span class=\"badge badge-soft-warning\">Frozen</span>" !!}'],
            ],
        ])

        @unless($standalone)
            </div>
        @endunless
    </div>
</div>
