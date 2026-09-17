<div>
    @unless($standalone)
        <div class="page-header">
            <div class="page-header-body">
                <nav class="page-breadcrumb" aria-label="Breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('accountflow::dashboard') }}" wire:navigate>Accounts</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Payment Methods</li>
                    </ol>
                </nav>
                <div class="page-header-title"><h1>Payment Methods</h1></div>
                <p class="page-header-subtitle">Ways money moves in and out — cash, bank, wallets.</p>
            </div>
            @if ($canManage)
                <div class="page-header-actions">
                    <a href="{{ route('accountflow::payment-methods.create') }}" class="btn btn-primary">
                        <i data-lucide="plus"></i> Add Payment Method
                    </a>
                </div>
            @endif
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-xl-4">
                <div class="card stat-tile h-100"><div class="card-body">
                    <div class="stat">
                        <div class="stat-head">
                            <div class="stat-label">Total Methods</div>
                            <span class="icon-box icon-box-primary"><i data-lucide="credit-card"></i></span>
                        </div>
                        <div class="stat-value">{{ count($paymentMethods) }}</div>
                        <div class="stat-meta"><span>{{ $activeCount }} active</span></div>
                    </div>
                </div></div>
            </div>
            <div class="col-6 col-xl-4">
                <div class="card stat-tile h-100"><div class="card-body">
                    <div class="stat">
                        <div class="stat-head">
                            <div class="stat-label">Inactive</div>
                            <span class="icon-box icon-box-secondary"><i data-lucide="circle-alert"></i></span>
                        </div>
                        <div class="stat-value">{{ count($paymentMethods) - $activeCount }}</div>
                        <div class="stat-meta"><span>not accepting funds</span></div>
                    </div>
                </div></div>
            </div>
        </div>
    @endunless

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-3">
        @forelse($paymentMethods as $method)
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body d-flex gap-3">
                        <div class="flex-shrink-0">
                            @if($method->logo_icon)
                                <img src="{{ $method->logo_icon }}" alt="{{ $method->name }}" class="rounded" style="width:56px;height:56px;object-fit:cover">
                            @else
                                <span class="icon-box icon-box-primary" style="width:56px;height:56px">
                                    <i data-lucide="credit-card"></i>
                                </span>
                            @endif
                        </div>

                        <div class="flex-grow-1">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <h6 class="mb-1">{{ $method->name }}</h6>
                                    @if($method->account)
                                        <div class="text-size-sm text-body-secondary">Account: {{ $method->account->name }}</div>
                                    @endif
                                </div>
                                <div>
                                    @if($method->isActive())
                                        <span class="badge badge-soft-success">Active</span>
                                    @else
                                        <span class="badge badge-soft-secondary">Inactive</span>
                                    @endif
                                </div>
                            </div>

                            @if($method->info)
                                <p class="text-size-sm text-body-secondary mb-0">{{ Str::limit($method->info, 140) }}</p>
                            @endif

                            @if ($canManage)
                                <div class="d-flex gap-2 mt-2">
                                    <a href="{{ route('accountflow::payment-methods.edit', $method->id) }}" class="btn btn-sm btn-ghost" wire:navigate.hover>Edit</a>
                                    <button type="button" class="btn btn-sm {{ $method->isActive() ? 'btn-soft-danger' : 'btn-soft-success' }}" wire:click="toggleStatus({{ $method->id }})" wire:loading.attr="disabled">
                                        {{ $method->isActive() ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center text-body-secondary">No payment methods found.</div>
                </div>
            </div>
        @endforelse
    </div>
</div>
