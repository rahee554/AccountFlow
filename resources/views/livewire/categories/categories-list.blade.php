<div>
    @unless($standalone)
        <div class="page-header">
            <div class="page-header-body">
                <nav class="page-breadcrumb" aria-label="Breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('accountflow::dashboard') }}" wire:navigate>Accounts</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Categories</li>
                    </ol>
                </nav>
                <div class="page-header-title"><h1>Categories</h1></div>
                <p class="page-header-subtitle">Group income and expenses so reports break down cleanly.</p>
            </div>
            @if ($canManage)
                <div class="page-header-actions">
                    <a href="{{ route('accountflow::categories.create') }}" class="btn btn-primary" wire:navigate>
                        <i data-lucide="plus"></i> Add Category
                    </a>
                </div>
            @endif
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-xl-3">
                <div class="card stat-tile h-100"><div class="card-body">
                    <div class="stat">
                        <div class="stat-head">
                            <div class="stat-label">Total Categories</div>
                            <span class="icon-box icon-box-primary"><i data-lucide="tag"></i></span>
                        </div>
                        <div class="stat-value">{{ number_format($totalCategories) }}</div>
                        <div class="stat-meta"><span>{{ $activeCategories }} active</span></div>
                    </div>
                </div></div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="card stat-tile h-100"><div class="card-body">
                    <div class="stat">
                        <div class="stat-head">
                            <div class="stat-label">Income</div>
                            <span class="icon-box icon-box-success"><i data-lucide="trending-up"></i></span>
                        </div>
                        <div class="stat-value">{{ number_format($incomeCategories) }}</div>
                        <div class="stat-meta"><span>categories</span></div>
                    </div>
                </div></div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="card stat-tile h-100"><div class="card-body">
                    <div class="stat">
                        <div class="stat-head">
                            <div class="stat-label">Expense</div>
                            <span class="icon-box icon-box-danger"><i data-lucide="trending-down"></i></span>
                        </div>
                        <div class="stat-value">{{ number_format($expenseCategories) }}</div>
                        <div class="stat-meta"><span>categories</span></div>
                    </div>
                </div></div>
            </div>
        </div>
    @endunless

    <div class="{{ $standalone ? '' : 'card' }}">
        @unless($standalone)
            <div class="card-header">
                <h2 class="card-title">All Categories</h2>
            </div>
            <div class="card-body">
        @endunless

        @livewire('aftable-simple', [
            'model' => 'ArtflowStudio\AccountFlow\Models\Category',
            'columns' => [
                [
                    'key' => 'name',
                    'label' => 'Name',
                    'raw' => '<span class="fw-semibold">{{ $row->name }}</span>'
                ],
                [
                    'key' => 'parent_id',
                    'label' => 'Parent',
                    'relation' => 'parent:name',
                ],
                [
                    'key' => 'type',
                    'label' => 'Type',
                    'raw' => '{!! $row->type == 1
                        ? "<span class=\"badge badge-soft-success\">Income</span>"
                        : "<span class=\"badge badge-soft-danger\">Expense</span>" !!}'
                ],
                [
                    'key' => 'status',
                    'label' => 'Status',
                    'raw' => '{!! $row->status == 1
                        ? "<span class=\"badge badge-soft-success\">Active</span>"
                        : "<span class=\"badge badge-soft-secondary\">Inactive</span>" !!}'
                ],
            ],
            'actions' => [
                'raw' => "{!! \$row->privacy == 2 ? \"<a href='\" . route('accountflow::categories.edit', base64_encode(\$row->id)) . \"' class='btn btn-sm btn-ghost'>Edit</a>\" : '' !!}"
            ]
        ])

        @unless($standalone)
            </div>
        @endunless
    </div>
</div>
