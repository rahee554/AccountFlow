<div>
    @unless($standalone)
        <div class="page-header">
            <div class="page-header-body">
                <nav class="page-breadcrumb" aria-label="Breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('accountflow::dashboard') }}" wire:navigate>Accounts</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Transfers</li>
                    </ol>
                </nav>
                <div class="page-header-title"><h1>Transfers</h1></div>
                <p class="page-header-subtitle">Money moved between your own accounts.</p>
            </div>
            @if ($canManage)
                <div class="page-header-actions">
                    <a href="{{ route('accountflow::transfers.create') }}" class="btn btn-primary" wire:navigate>
                        <i data-lucide="plus"></i> Add Transfer
                    </a>
                </div>
            @endif
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-xl-4">
                <div class="card stat-tile h-100"><div class="card-body">
                    <div class="stat">
                        <div class="stat-head">
                            <div class="stat-label">Total Transfers</div>
                            <span class="icon-box icon-box-primary"><i data-lucide="repeat"></i></span>
                        </div>
                        <div class="stat-value">{{ number_format($totalTransfers) }}</div>
                        <div class="stat-meta"><span>{{ $transfersThisMonth }} this month</span></div>
                    </div>
                </div></div>
            </div>
            <div class="col-6 col-xl-4">
                <div class="card stat-tile h-100"><div class="card-body">
                    <div class="stat">
                        <div class="stat-head">
                            <div class="stat-label">Total Amount</div>
                            <span class="icon-box icon-box-success"><i data-lucide="banknote"></i></span>
                        </div>
                        <div class="stat-value">{{ config('accountflow.currency_symbols.' . config('accountflow.currency', 'PKR'), config('accountflow.currency', 'PKR') . ' ') }}{{ number_format($totalAmount, 0) }}</div>
                        <div class="stat-meta"><span>Across all transfers</span></div>
                    </div>
                </div></div>
            </div>
            <div class="col-12 col-xl-4">
                <div class="card stat-tile h-100"><div class="card-body">
                    <div class="stat">
                        <div class="stat-head">
                            <div class="stat-label">This Month</div>
                            <span class="icon-box icon-box-secondary"><i data-lucide="calendar-days"></i></span>
                        </div>
                        <div class="stat-value">{{ number_format($transfersThisMonth) }}</div>
                        <div class="stat-meta"><span>Transfers processed</span></div>
                    </div>
                </div></div>
            </div>
        </div>
    @endunless

    <div class="{{ $standalone ? '' : 'card' }}">
        @unless($standalone)
            <div class="card-header">
                <h2 class="card-title">All Transfers</h2>
            </div>
            <div class="card-body">
        @endunless

        @livewire('aftable', [
            'model' => 'ArtflowStudio\AccountFlow\Models\Transfer',
            'columns' => [
                ['key' => 'unique_id', 'label' => 'Transfer ID'],
                [
                    'key'   => 'amount',
                    'label' => 'Amount',
                    'raw'   => '<span class="font-mono cell-numeric text-primary fw-semibold">{{ config(\'accountflow.currency_symbols.\' . config(\'accountflow.currency\', \'PKR\'), config(\'accountflow.currency\', \'PKR\') . \' \') }}{{ number_format($row->amount, 2) }}</span>',
                ],
                ['key' => 'from_account', 'label' => 'From Account', 'relation' => 'fromAccount:name'],
                ['key' => 'to_account', 'label' => 'To Account', 'relation' => 'toAccount:name'],
                ['key' => 'description', 'label' => 'Description', 'raw' => '{{ $row->description ?: "-" }}'],
                ['key' => 'date', 'label' => 'Date', 'raw' => '{{ \Carbon\Carbon::parse($row->date)->format("d M Y") }}'],
                ['key' => 'created_by', 'label' => 'Created By', 'relation' => 'user:name'],
            ],
            'actions' => [
                'raw' => "<a href='{{ route('accountflow::transfers.edit', \$row->id) }}' class='btn btn-sm btn-ghost'>Edit</a>"
            ]
        ])

        @unless($standalone)
            </div>
        @endunless
    </div>
</div>
