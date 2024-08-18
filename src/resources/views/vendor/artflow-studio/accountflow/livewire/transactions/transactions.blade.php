<div>
    @include(config('accountflow.view_path') . '.blades.dashboard-header')

    <div class="px-2 px-md-5 px-lg-10">

        <div class="d-flex flex-stack my-2">
            <h1>Transactions</h1>
            <div>
                <a href="{{route('accountflow::transaction.create')}}" class="btn btn-sm btn-primary" wire:navigate>Add
                    Record</a>
                <a href="{{route('accountflow::transactions.create')}}" class="btn btn-sm btn-light" wire:navigate>Add
                    Multiple Records</a>
            </div>
        </div>
        @livewire('aftable', [
            'model' => 'App\Models\AccountFlow\Transaction',
            'columns' => [
                [
                    'key' => 'amount',
                    'label' => 'Amount',
                    'raw' => '<span class="fw-bold {{ $row->type == 1 ? "text-success" : "text-danger" }}">Rs: {{ $row->amount }}</span>'
                ],
                ['key' => 'date', 'label' => 'Date', 'raw' => '{{ \Carbon\Carbon::parse($row->date)->format("d M Y") }}'],
                ['key' => 'description', 'label' => 'Description'],

                [
                    'key' => 'category_id',
                    'relation' => 'category:name',
                    'label' => 'Category',
                    'raw' => '<span><img src="{{ asset(config(\'accountflow.asset_path\') . "icons/accounts_icons/" . $row->category->icon) }}" alt="{{ $row->category->name }}" class="h-30px me-2">{{ $row->category->name }}</span>'
                ],
                ['key' => 'account_id', 'relation' => 'account:name', 'label' => 'Account'],
            ],
            'actions' => [
                'raw' => '<a href="{{ route(\'accountflow::transactions.edit\', [\'id\' => base64_encode($row->id)]) }}" class="btn btn-sm btn-light-info">Edit</a>'
            ]
        ])
    </div>
</div>