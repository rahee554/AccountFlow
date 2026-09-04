<div>
    @if(!$standalone)
        @include(config('accountflow.view_path') . 'blades.dashboard-header')
    @endif

    <div class="px-2 px-md-5 px-lg-10" style="@if($standalone) padding: 0 !important; @endif">

        <div class="d-flex flex-stack my-2">
            <h1>Transactions</h1>
            <div>
                @if($canManage)
                    <a href="{{ route('accountflow::transaction.create') }}" class="btn btn-sm btn-primary" wire:navigate>
                        Add Record
                    </a>
                    <a href="{{ route('accountflow::transactions.create') }}" class="btn btn-sm btn-light" wire:navigate>
                        Add Multiple Records
                    </a>
                @endif
            </div>
        </div>

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
    </div>
</div>
