<div>
    <div class="page-header">
        <div class="page-header-body">
            <nav class="page-breadcrumb" aria-label="Breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('accountflow::transactions.templates') }}" wire:navigate>Templates</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Add</li>
                </ol>
            </nav>
            <div class="page-header-title"><h1>Create Transaction Template</h1></div>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('accountflow::transactions.templates') }}" class="btn btn-soft-secondary" wire:navigate>
                <i data-lucide="arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form wire:submit.prevent="storeTemplate">
                        <div class="row g-3">
                            <!-- Template Name -->
                            <div class="col-12">
                                <label class="form-label">Template Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- Payment Method -->
                            <div class="col-12 col-sm-6">
                                <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                                <select wire:model="payment_method" class="form-select @error('payment_method') is-invalid @enderror">
                                    <option value="">Select</option>
                                    @foreach ($payment_methods as $method)
                                        <option value="{{ $method->id }}">{{ $method->name }}</option>
                                    @endforeach
                                </select>
                                @error('payment_method') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- Account -->
                            <div class="col-12 col-sm-6">
                                <label class="form-label">Account <span class="text-danger">*</span></label>
                                <select wire:model="account_id" class="form-select @error('account_id') is-invalid @enderror">
                                    <option value="">Select</option>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                                    @endforeach
                                </select>
                                @error('account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- Type -->
                            <div class="col-12 col-sm-6">
                                <label class="form-label">Type</label>
                                <div class="nav nav-segmented w-100">
                                    <button type="button" class="nav-link flex-fill @if((int) $type === 1) active @endif" wire:click="$set('type', 1)">Income</button>
                                    <button type="button" class="nav-link flex-fill @if((int) $type === 2) active @endif" wire:click="$set('type', 2)">Expense</button>
                                </div>
                            </div>

                            <!-- Category -->
                            <div class="col-12 col-sm-6">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                <select wire:model="category_id" class="form-select @error('category_id') is-invalid @enderror">
                                    <option value="">Select</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- Amount -->
                            <div class="col-12 col-sm-6">
                                <label class="form-label">Default Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ config('accountflow.currency', 'PKR') }}</span>
                                    <input type="number" step="0.01" min="0.01" wire:model="amount" class="form-control @error('amount') is-invalid @enderror">
                                </div>
                                @error('amount') <div class="text-danger text-size-sm mt-1">{{ $message }}</div> @enderror
                            </div>

                            <!-- Description -->
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea wire:model="description" rows="3" class="form-control @error('description') is-invalid @enderror"></textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <!-- Buttons -->
                            <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                                <a href="{{ route('accountflow::transactions.templates') }}" class="btn btn-soft-secondary" wire:navigate>
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                    <span wire:loading.remove><i data-lucide="save"></i> Save Template</span>
                                    <span wire:loading>Saving…</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h2 class="card-title">Preview</h2></div>
                <div class="card-body">
                    @if($name)
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="icon-box icon-box-{{ (int) $type === 1 ? 'success' : 'danger' }}">
                                <i data-lucide="{{ (int) $type === 1 ? 'trending-up' : 'trending-down' }}"></i>
                            </span>
                            <div>
                                <div class="fw-semibold">{{ $name }}</div>
                                @if($amount)
                                    <div class="text-body-secondary text-size-sm">{{ config('accountflow.currency_symbols.' . config('accountflow.currency', 'PKR'), config('accountflow.currency', 'PKR') . ' ') }}{{ number_format($amount, 2) }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge badge-soft-{{ (int) $type === 1 ? 'success' : 'danger' }}">{{ (int) $type === 1 ? 'Income' : 'Expense' }}</span>
                            @if($category_id)
                                @php $categoryName = $categories->firstWhere('id', $category_id)->name ?? 'Unknown'; @endphp
                                <span class="badge badge-soft-secondary">{{ $categoryName }}</span>
                            @endif
                        </div>
                    @else
                        <p class="text-body-secondary text-size-sm mb-0">Enter a name to see a preview here.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
