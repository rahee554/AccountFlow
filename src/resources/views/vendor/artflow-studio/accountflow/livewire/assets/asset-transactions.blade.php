<div>
       @include(config('accountflow.view_path') . '.blades.dashboard-header')

        @livewire('aftable', [
        'model' => 'App\Models\AccountFlow\AssetTransaction',
        'columns' => [

            [
                'key' => 'name',
                'label' => 'Name',
                'realation' => 'asset:name',
            ],
            [
                'key' => 'description',
                'label' => 'Description',
                'relation' => 'asset:description',
            ],
            [
                'key' => 'value',
                'label' => 'Total Value',
                'raw' => '<span class="fw-bold">Rs: {{ $row->value }}</span>'
            ],

            [
                'key' => 'status',
                'label' => 'Status',
                'raw' => '{!! $row->status == 1 ? "<span class=\"badge badge-sm badge-light-success\">Operating Asset</span>" : ($row->status == 2 ? "<span class=\"badge badge-sm badge-light-danger\">Not Operating</span>" : "<span class=\"badge badge-sm badge-light-info\">Sold Out</span>") !!}'
            ],

            [
                'key' => 'amount',
                'label' => 'Amount',
                'raw' => '<span class="fw-bold {{ $row->type == 1 ? "text-success" : "text-danger" }}">Rs: {{ $row->amount }}</span>'
            ],
            ['key' => 'date', 'label' => 'Date', 'raw' => '{{ \Carbon\Carbon::parse($row->date)->format("d M Y") }}'],

        ],
        'actions' => [
            'raw' => '<a href="{{ route(\'accountflow::transactions.edit\', [\'id\' => base64_encode($row->id)]) }}" class="btn btn-sm btn-light-info">Edit</a>'
        ]
    ])
</div>
