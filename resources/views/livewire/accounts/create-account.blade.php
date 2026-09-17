<div>
    @unless($standalone)
        <div class="page-header">
            <div class="page-header-body">
                <nav class="page-breadcrumb" aria-label="Breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('accountflow::accounts') }}" wire:navigate>Accounts</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $isEdit ? 'Edit' : 'Add' }}</li>
                    </ol>
                </nav>
                <div class="page-header-title"><h1>{{ $isEdit ? 'Edit Account' : 'Add Account' }}</h1></div>
            </div>
            <div class="page-header-actions">
                <a href="{{ route('accountflow::accounts') }}" class="btn btn-soft-secondary" wire:navigate>
                    <i data-lucide="arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    @endunless

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-body">
                    <form wire:submit.prevent="save">
                        <div class="mb-3">
                            <label for="name" class="form-label">Account Name <span class="text-danger">*</span></label>
                            <input type="text" id="name" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Cash Account">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" wire:model="description" class="form-control @error('description') is-invalid @enderror" rows="3"></textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="active" class="form-label">Status</label>
                                <select id="active" wire:model="active" class="form-select @error('active') is-invalid @enderror">
                                    <option value="">Select Status</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                                @error('active') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="opening_balance" class="form-label">Opening Balance</label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ config('accountflow.currency', 'PKR') }}</span>
                                    <input type="number" id="opening_balance" wire:model="opening_balance" step="0.01" class="form-control @error('opening_balance') is-invalid @enderror">
                                </div>
                                @error('opening_balance') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-3">
                            <a href="{{ route('accountflow::accounts') }}" class="btn btn-soft-secondary" wire:navigate>Cancel</a>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove>{{ $isEdit ? 'Update' : 'Create' }} Account</span>
                                <span wire:loading>Saving…</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header"><h2 class="card-title">Preview</h2></div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="icon-box icon-box-primary"><i data-lucide="landmark"></i></span>
                        <div>
                            <div class="fw-semibold">{{ $name ?: 'Account name' }}</div>
                            <div class="text-body-secondary text-size-sm">{{ config('accountflow.currency_symbols.' . config('accountflow.currency', 'PKR'), config('accountflow.currency', 'PKR') . ' ') }}{{ number_format((float) ($opening_balance ?: 0), 2) }} opening balance</div>
                        </div>
                    </div>
                    <span class="badge badge-soft-{{ $active === '' || $active === null ? 'secondary' : ((int) $active === 1 ? 'success' : 'secondary') }}">
                        {{ (int) $active === 1 ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
