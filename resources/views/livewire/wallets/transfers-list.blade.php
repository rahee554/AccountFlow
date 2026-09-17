<div>
    @unless($standalone)
        <div class="page-header">
            <div class="page-header-body">
                <nav class="page-breadcrumb" aria-label="Breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('accountflow::dashboard') }}" wire:navigate>Accounts</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('accountflow::users.wallets') }}" wire:navigate>User Wallets</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Transfers</li>
                    </ol>
                </nav>
                <div class="page-header-title"><h1>Wallet Transfers</h1></div>
                <p class="page-header-subtitle">Money passed directly between people's wallets.</p>
            </div>
            @if ($canManage)
                <div class="page-header-actions">
                    <a href="{{ route('accountflow::users.wallets.create') }}" class="btn btn-primary" wire:navigate>
                        <i data-lucide="plus"></i> Add Transfer
                    </a>
                </div>
            @endif
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-xl-6">
                <div class="card stat-tile h-100"><div class="card-body">
                    <div class="stat">
                        <div class="stat-head">
                            <div class="stat-label">Total Transfers</div>
                            <span class="icon-box icon-box-primary"><i data-lucide="repeat"></i></span>
                        </div>
                        <div class="stat-value">{{ number_format($totalTransfers) }}</div>
                        <div class="stat-meta"><span>Wallet to wallet</span></div>
                    </div>
                </div></div>
            </div>
            <div class="col-6 col-xl-6">
                <div class="card stat-tile h-100"><div class="card-body">
                    <div class="stat">
                        <div class="stat-head">
                            <div class="stat-label">Total Amount</div>
                            <span class="icon-box icon-box-success"><i data-lucide="banknote"></i></span>
                        </div>
                        <div class="stat-value">{{ config('accountflow.currency_symbols.' . config('accountflow.currency', 'PKR'), config('accountflow.currency', 'PKR') . ' ') }}{{ number_format($totalAmount, 0) }}</div>
                        <div class="stat-meta"><span>Moved between wallets</span></div>
                    </div>
                </div></div>
            </div>
        </div>
    @endunless

    <div class="{{ $standalone ? '' : 'card' }}">
        @unless($standalone)
            <div class="card-header">
                <h2 class="card-title">All Wallet Transfers</h2>
            </div>
            <div class="card-body">
        @endunless

        @livewire('aftable', [
            'model' => 'ArtflowStudio\AccountFlow\Models\UserTransfer',
            'columns' => [
                ['key' => 'from', 'label' => 'From', 'relation' => 'fromUser:name'],
                ['key' => 'to', 'label' => 'To', 'relation' => 'toUser:name'],
                ['key' => 'amount', 'label' => 'Amount', 'raw' => '<span class="font-mono cell-numeric">{{ config(\'accountflow.currency_symbols.\' . config(\'accountflow.currency\', \'PKR\'), config(\'accountflow.currency\', \'PKR\') . \' \') }}{{ number_format($row->amount, 2) }}</span>'],
                ['key' => 'date', 'label' => 'Date', 'raw' => '{{ \Carbon\Carbon::parse($row->date)->format("d M Y") }}'],
            ],
        ])

        @unless($standalone)
            </div>
        @endunless
    </div>
</div>
