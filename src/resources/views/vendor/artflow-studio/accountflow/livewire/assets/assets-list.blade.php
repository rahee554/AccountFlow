<div>
    @include(config('accountflow.view_path') . '.blades.dashboard-header')

    
    @livewire('aftable', [
        'model' => 'App\Models\AccountFlow\Asset',
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
                'raw' => '<span class="fw-bold">Rs: {{ $row->value }}</span>'
            ],
            [
                'key' => 'category_id',
                'relation' => 'category:name',
                'label' => 'Category',
            ],
            [
                'key' => 'status',
                'label' => 'Status',
                'raw' => '{!! $row->status == 1 ? "<span class=\"badge badge-sm badge-light-success\">Operating Asset</span>" : ($row->status == 2 ? "<span class=\"badge badge-sm badge-light-danger\">Not Operating</span>" : "<span class=\"badge badge-sm badge-light-info\">Sold Out</span>") !!}'
            ],
            [
                'key' => 'acquisition_date',
                'label' => 'Date',
                'raw' => '{{ \Carbon\Carbon::parse($row->acquisition_date)->format("d M Y") }}'
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
