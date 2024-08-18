<div>
    @include(config('accountflow.view_path') . '.blades.dashboard-header')
    <div class="px-2 px-md-5 px-lg-10">

        <div class="d-flex flex-stack my-2">
            <h1>Account Transfers</h1>
            <div>
                <a href="{{ route('accountflow::transfers.create') }}" class="btn btn-sm btn-primary" wire:navigate>Add
                    Transfer</a>
            </div>
        </div>

        @livewire('aftable', [
            'model' => 'App\Models\AccountFlow\Transfer',
            'columns' => [
                ['key' => 'unique_id', 'label' => 'Transfer ID'],
                [
                    'key' => 'amount',
                    'label' => 'Amount',
                    'raw' => '<span class="text-primary fw-bold">PKR {{ number_format($row->amount, 2) }}</span>'
                ],
                ['key' => 'from_account', 'label' => 'From Account', 'relation' => 'fromAccount:name'],
                ['key' => 'to_account', 'label' => 'To Account', 'relation' => 'toAccount:name'],
                ['key' => 'description', 'label' => 'Description', 'raw' => '{{ $row->description ?: "-" }}'],
                ['key' => 'date', 'label' => 'Date', 'raw' => '{{ \Carbon\Carbon::parse($row->date)->format("d M Y") }}'],
                ['key' => 'created_by', 'label' => 'Created By', 'relation' => 'user:name'],
            ],
            'actions' => [
                'raw' => "<a href='{{ route('accountflow::transfers.edit', \$row->id) }}' class='btn btn-sm btn-warning'>Edit</a>"
            ]
        ])
    </div>
</div>
