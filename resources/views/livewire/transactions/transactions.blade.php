<div>
    @unless($standalone)
        <div class="page-header">
            <div class="page-header-body">
                <nav class="page-breadcrumb" aria-label="Breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('accountflow::dashboard') }}" wire:navigate>Accounts</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Transactions</li>
                    </ol>
                </nav>
                <div class="page-header-title"><h1>Transactions</h1></div>
                <p class="page-header-subtitle">Every posted income and expense entry.</p>
            </div>
            @if ($canManage)
                <div class="page-header-actions">
                    <a href="{{ route('accountflow::transactions.create') }}" class="btn btn-soft-secondary" wire:navigate>
                        <i data-lucide="list-checks"></i> Add Multiple
                    </a>
                    <a href="{{ route('accountflow::transaction.create') }}" class="btn btn-primary" wire:navigate>
                        <i data-lucide="plus"></i> Add Transaction
                    </a>
                </div>
            @endif
        </div>
    @endunless

    <div class="{{ $standalone ? '' : 'card' }}">
        @unless($standalone)
            <div class="card-header">
                <h2 class="card-title">All Transactions</h2>
            </div>
            <div class="card-body">
        @endunless

        {{--
            Column definitions live in ArtflowStudio\AccountFlow\Support\TableColumns
            rather than inline here. Every relation column declares `relation`, which
            is what makes AFTable eager-load it instead of querying once per row.
        --}}
        @livewire('aftable', [
            'model'         => \ArtflowStudio\AccountFlow\Models\Transaction::class,
            'columns'       => \ArtflowStudio\AccountFlow\Support\TableColumns::transactions(),
            'vars'          => \ArtflowStudio\AccountFlow\Support\TableColumns::vars(),
            'actions'       => $actions,
            'sortBy'        => 'date',
            'sortDirection' => 'desc',
        ])

        @unless($standalone)
            </div>
        @endunless
    </div>
</div>
