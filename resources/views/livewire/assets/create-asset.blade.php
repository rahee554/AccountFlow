<div>
    <div class="page-header">
        <div class="page-header-body">
            <nav class="page-breadcrumb" aria-label="Breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('accountflow::assets') }}" wire:navigate>Assets</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Add</li>
                </ol>
            </nav>
            <div class="page-header-title"><h1>Add Asset</h1></div>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('accountflow::assets') }}" class="btn btn-soft-secondary" wire:navigate>
                <i data-lucide="arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form wire:submit.prevent="storeAsset">
                        <div class="row g-3">
                            <div class="col-12 col-sm-6">
                                <label for="name" class="form-label">Asset Name <span class="text-danger">*</span></label>
                                <input type="text" id="name" class="form-control @error('name') is-invalid @enderror" wire:model="name" placeholder="e.g. Delivery Van">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-sm-6">
                                <label for="value" class="form-label">Value <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ config('accountflow.currency', 'PKR') }}</span>
                                    <input type="number" id="value" class="form-control @error('value') is-invalid @enderror" wire:model="value" step="0.01" min="0" placeholder="0.00">
                                </div>
                                @error('value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-sm-6">
                                <label for="category" class="form-label">Category</label>
                                <select id="category" class="form-select @error('category') is-invalid @enderror" wire:model="category">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-sm-6">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select id="status" class="form-select @error('status') is-invalid @enderror" wire:model="status">
                                    <option value="">Select Status</option>
                                    <option value="1">Operating</option>
                                    <option value="2">Not Operating</option>
                                </select>
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-sm-6">
                                <label for="date" class="form-label">Acquisition Date <span class="text-danger">*</span></label>
                                <input type="date" id="date" class="form-control @error('date') is-invalid @enderror" wire:model="date">
                                @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea id="description" class="form-control @error('description') is-invalid @enderror" wire:model="description" rows="3"></textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                                <a href="{{ route('accountflow::assets') }}" class="btn btn-soft-secondary" wire:navigate>Cancel</a>
                                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                    <span wire:loading.remove>Create Asset</span>
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
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="icon-box icon-box-primary"><i data-lucide="package"></i></span>
                        <div>
                            <div class="fw-semibold">{{ $name ?: 'Asset name' }}</div>
                            <div class="text-body-secondary text-size-sm">{{ config('accountflow.currency_symbols.' . config('accountflow.currency', 'PKR'), config('accountflow.currency', 'PKR') . ' ') }}{{ number_format((float) ($value ?: 0), 2) }}</div>
                        </div>
                    </div>
                    <span class="badge badge-soft-{{ (int) $status === 1 ? 'success' : ((int) $status === 2 ? 'danger' : 'secondary') }}">
                        {{ (int) $status === 1 ? 'Operating' : ((int) $status === 2 ? 'Not Operating' : 'Status pending') }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
