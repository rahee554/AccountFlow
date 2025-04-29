

<div>


    <div class="d-flex flex-stack my-2">
        <h1>Accounts Categories</h1>
        <div>
            <a href="{{route('accountflow::transaction.create')}}" class="btn btn-sm btn-primary" wire:navigate>Add Category</a>
        </div>
    </div>
    @livewire('aftable', [
        'model' => 'App\Models\AccountFlow\Category',
        'columns' => [
            ['key' => 'id', 'label' => 'ID'],
            ['key' => 'flow_type', 'label' => 'Type', 'raw' => '{{ $row->flow_type == 1 ? "Income" : "Expense" }}'],
            ['key' => 'name', 'label' => 'Name'],
            [
                'key' => 'parent_id',
                'label' => 'Parent',
                'raw' => '{{ optional(\App\Models\AccountFlow\Category::find($row->parent_id))->name ?? "-" }}'
            ],
            ['key' => 'privacy', 'label' => 'Privacy', 'raw' => '{{ $row->privacy == 1 ? "Locked" : "Unlocked" }}'],
            ['key' => 'status', 'label' => 'Status', 'raw' => '{{ $row->status == 1 ? "Active" : "Inactive" }}'],
        ],
  'actions' => [
    '{{ $row->privacy == 1 ? "Active" : "Inactive" }}',
    '<a href="/categories/{{$row->id}}/edit" class="btn btn-sm btn-light">Edit</a>'
]

    ])
    
    
</div>
