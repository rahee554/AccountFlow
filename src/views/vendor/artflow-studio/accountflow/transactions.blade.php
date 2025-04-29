

@livewire('aftable',[
    'model' => 'App\Models\AccountFlow\Transaction',
    'columns' => [
        ['key' => 'unique_id', 'label' => 'ID'],
        [
            'key' => 'amount',
            'label' => 'Amount',
            'raw' => '<span class="{{ $row->type == 1 ? "text-success" : "text-danger" }}">Rs: {{ $row->amount }}</span>'
        ],
        ['key' => 'date', 'label' => 'Date', 'raw' => '{{ \Carbon\Carbon::parse($row->date)->format("d M Y") }}'],
        [
            'key' => 'category_id',
            'relation' => 'category:name',
            'label' => 'Category',
            'raw' => '<span><img src="{{ asset(config(\'accountflow.asset_path\') . "icons/accounts_icons/" . $row->category->icon) }}" alt="{{ $row->category->name }}" class="h-30px me-2">{{ $row->category->name }}</span>'
         ],
        ['key' => 'account_id', 'relation' => 'account:name', 'label' => 'Account'],
         ['key' => 'actions', 'label' => 'Actions', 'raw' => '<button>{{$row->id}}</button>'],
    ],
    'filter' => [
        'amount' => 'number',
        'date' => 'daterange',
        'category' => 'select'
    ],
    'searchable' => true,
    'dateSearch' => 'false', // Column Name which is date
    'exportable' => false,
    'checkbox' => false,
    'printable' => false,
])
