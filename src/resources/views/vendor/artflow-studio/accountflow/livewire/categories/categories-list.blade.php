<div>
    @include(config('accountflow.view_path') . '.blades.dashboard-header')

   <div class="px-2 px-md-5 px-lg-10">
    <div class="d-flex flex-stack my-2">
        <h1>Accounts Categories</h1>
        <div>
            <a href="{{route('accountflow::categories.create')}}" class="btn btn-sm btn-primary" wire:navigate>Add
                Category</a>
        </div>
    </div>
    @livewire('aftable-simple', [
        'model' => 'App\Models\AccountFlow\Category',
        'columns' => [

            [
                'key' => 'name',
                'label' => 'Name',
                'raw' => '<span class="fw-bold">{{ $row->name }}</span>'
            ],
            [
                'key' => 'parent_id',
                'label' => 'Parent',
                'raw' => '{{ optional(\App\Models\AccountFlow\Category::find($row->parent_id))->name ?? "-" }}'
            ],
            [
                'key' => 'type',
                'label' => 'Type',
                'raw' => '{!! $row->type == 1 
                    ? "<span class=\"badge badge-light-success\">Income</span>" 
                    : "<span class=\"badge badge-light-danger\">Expense</span>" !!}'
            ],
            [
                'key' => 'status',
                'label' => 'Status',
                'raw' => '{!! $row->status == 1 
                    ? "<span class=\"badge badge-light-success\">Active</span>" 
                    : "<span class=\"badge badge-light-danger\">Inactive</span>" !!}'
            ],
        ],
        'actions' => [
            'raw' => "{!! \$row->privacy == 2 ? \"<a href='\" . route('accountflow::categories.edit', base64_encode(\$row->id)) . \"' class='btn btn-sm p-1 px-2 btn-light'>  Edit</a>\" : '' !!}"
        ]
    ])
   </div>
    
</div>
