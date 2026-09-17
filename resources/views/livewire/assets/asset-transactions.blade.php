<div>
    @unless($standalone)
        <div class="page-header">
            <div class="page-header-body">
                <nav class="page-breadcrumb" aria-label="Breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('accountflow::dashboard') }}" wire:navigate>Accounts</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('accountflow::assets') }}" wire:navigate>Assets</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Transactions</li>
                    </ol>
                </nav>
                <div class="page-header-title"><h1>Asset Transactions</h1></div>
                <p class="page-header-subtitle">Purchases and disposals posted against your assets.</p>
            </div>
            @if ($canManage)
                <div class="page-header-actions">
                    <a href="{{ route('accountflow::assets.transactions.create') }}" class="btn btn-primary" wire:navigate>
                        <i data-lucide="plus"></i> Add Transaction
                    </a>
                </div>
            @endif
        </div>
    @endunless

    <div class="{{ $standalone ? '' : 'card' }}">
        @unless($standalone)
            <div class="card-header">
                <h2 class="card-title">All Asset Transactions</h2>
            </div>
            <div class="card-body">
        @endunless

        @livewire('aftable', [
            'model' => 'ArtflowStudio\AccountFlow\Models\AssetTransaction',
            'columns' => [

                [
                    'key' => 'name',
                    'label' => 'Name',
                    'relation' => 'asset:name',
                ],
                [
                    'key' => 'description',
                    'label' => 'Description',
                    'relation' => 'asset:description',
                ],
                [
                    'key'   => 'value',
                    'label' => 'Total Value',
                    'raw'   => '<span class="font-mono cell-numeric">{{ config(\'accountflow.currency_symbols.\' . config(\'accountflow.currency\', \'PKR\'), config(\'accountflow.currency\', \'PKR\') . \' \') }}{{ number_format($row->value, 2) }}</span>',
                ],

                [
                    'key' => 'status',
                    'label' => 'Status',
                    'raw' => '{!! $row->status == 1 ? "<span class=\"badge badge-soft-success\">Operating Asset</span>" : ($row->status == 2 ? "<span class=\"badge badge-soft-danger\">Not Operating</span>" : "<span class=\"badge badge-soft-secondary\">Sold Out</span>") !!}'
                ],

                [
                    'key'   => 'amount',
                    'label' => 'Amount',
                    'raw'   => '<span class="font-mono cell-numeric {{ $row->type == 1 ? \"text-success\" : \"text-danger\" }}">{{ config(\'accountflow.currency_symbols.\' . config(\'accountflow.currency\', \'PKR\'), config(\'accountflow.currency\', \'PKR\') . \' \') }}{{ number_format($row->amount, 2) }}</span>',
                ],
                ['key' => 'date', 'label' => 'Date', 'raw' => '{{ \Carbon\Carbon::parse($row->date)->format("d M Y") }}'],

            ],
            'actions' => [
                'raw' => '<a href="{{ route(\'accountflow::assets.transactions.edit\', [\'id\' => base64_encode($row->id)]) }}" class="btn btn-sm btn-ghost">Edit</a>'
            ]
        ])

        @unless($standalone)
            </div>
        @endunless
    </div>
</div>
