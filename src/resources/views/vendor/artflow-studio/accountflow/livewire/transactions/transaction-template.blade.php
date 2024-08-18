<div>
    @include(config('accountflow.view_path') . '.blades.dashboard-header')

    <div class="px-2 px-md-5 px-lg-10">

        <div class="d-flex flex-stack my-2">
            <h1>Transaction Templates</h1>
            <div>
                <a href="{{route('accountflow::transactions.templates.create')}}" class="btn btn-sm btn-primary"
                    wire:navigate>Add
                    Transaction Template</a>
            </div>
        </div>


        @livewire('aftable', [
            'model' => 'App\Models\AccountFlow\TransactionTemplate',
            'columns' => [
                ['key' => 'name', 'label' => 'Template Name'],
                 ['key' => 'amount', 'label' => 'Amount'],
                 ['key' => 'account_id', 'relation' => 'account:name', 'label' => 'Account'],
               
                 
                 [
                     'key' => 'type',
                     'label' => 'Type',
                     'raw' => '<span class="badge bg-{{ $row->type == 1 ? "success" : "danger" }}">{{ $row->type == 1 ? "Income" : "Expense" }}</span>'
                 ],
                
    ],
            'actions' => [
                '<a href="" class="btn btn-sm btn-light-info">Post Now</a>
                 <a href="" class="btn btn-sm btn-light-info">Edit</a>'
            ]
        ])
    
    </div>
</div>