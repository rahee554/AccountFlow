<div>
    @if(!$standalone)
        @include(config('accountflow.view_path') . 'blades.dashboard-header')
    @endif


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
                'key'   => 'amount',
                'label' => 'Amount',
                'raw'   => '<span class="fw-bold">{{ config(\'accountflow.currency_symbols.\' . config(\'accountflow.currency\', \'PKR\'), config(\'accountflow.currency\', \'PKR\') . \' \') }}{{ number_format($row->amount, 2) }}</span>',
            ],
            [
                'key'      => 'category_id',
                'relation' => 'category:name',
                'label'    => 'Category',
                'raw'      => '<span><img src="{{ asset(config(\'accountflow.asset_path\') . \'icons/accounts_icons/\' . $row->category->icon) }}" alt="{{ $row->category->name }}" class="h-30px me-2">{{ $row->category->name }}</span>',
            ],
            [
                'key'   => 'due_date',
                'label' => 'Due Date',
                'raw'   => '{{ \Carbon\Carbon::parse($row->due_date)->format("d M Y") }}',
            ],
            [
                'key'   => 'trx_id',
                'label' => 'Status',
                'raw'   => '{!! $row->trx_id ? "<span class=\"badge badge-light-success\">Posted</span>" : "<span class=\"badge badge-light-warning\">Pending</span>" !!}',
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
