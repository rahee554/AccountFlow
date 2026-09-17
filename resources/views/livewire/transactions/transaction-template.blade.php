<div>
    <div class="page-header">
        <div class="page-header-body">
            <nav class="page-breadcrumb" aria-label="Breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('accountflow::transactions') }}" wire:navigate>Transactions</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Templates</li>
                </ol>
            </nav>
            <div class="page-header-title"><h1>Transaction Templates</h1></div>
            <p class="page-header-subtitle">Reusable presets for quickly posting repeat transactions.</p>
        </div>
        @if ($canManage)
            <div class="page-header-actions">
                <a href="{{ route('accountflow::transactions.templates.create') }}" class="btn btn-primary" wire:navigate>
                    <i data-lucide="plus"></i> Add Transaction Template
                </a>
            </div>
        @endif
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">All Templates</h2>
        </div>
        <div class="card-body">
            @livewire('aftable', [
                'model' => 'ArtflowStudio\AccountFlow\Models\TransactionTemplate',
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
</div>
