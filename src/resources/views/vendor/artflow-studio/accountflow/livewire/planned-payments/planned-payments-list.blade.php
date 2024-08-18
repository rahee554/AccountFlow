<div>
    {{-- Success is as dangerous as failure. --}}
        @include(config('accountflow.view_path') . '.blades.dashboard-header')


        @livewire('aftable', [
        'model' => 'App\Models\AccountFlow\PlannedPayment',
        'columns' => [
            [
                'key' => 'name',
                'label' => 'Name',
            ],
            [
                'key' => 'description',
                'label' => 'Description',
            ],
            [
                'key' => 'value',
                'label' => 'Value',
                'raw' => '<span class="fw-bold">Rs: {{ $row->amount }}</span>'
            ],
            [
                'key' => 'category_id',
                'relation' => 'category:name',
                'label' => 'Category',
                'raw' => '<span><img src="{{ asset(config(\'accountflow.asset_path\') . "icons/accounts_icons/" . $row->category->icon) }}" alt="{{ $row->category->name }}" class="h-30px me-2">{{ $row->category->name }}</span>'
            ],

            [   
                'key' => 'due_date',
                'label' => 'Date',
                'raw' => '{{ \Carbon\Carbon::parse($row->due_date)->format("d M Y") }}'
            ],
            [
                'key' => 'transactions',
                'label' => 'Transactions',
                'raw' => '{{ \App\Models\AccountFlow\AssetTransaction::where("asset_id", $row->id)->sum("asset_id") }}'
            ],
        ],
        'actions' => [
            'raw' => '<span class="svg-icon">
                        <a href="#" data-id="{{ $row->id }}"><i class="fad fa-edit text-gray-600 mx-2"></i></a>
                        <a href="#" data-id="{{ $row->id }}"><i class="fad fa-user text-success mx-2"></i></a>
                        <a href="#" data-id="{{ $row->id }}"><i class="fad fa-trash text-danger mx-2"></i></a>
                    </span>'
        ]
    ])

</div>
