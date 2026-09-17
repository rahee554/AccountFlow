<div>
    @unless($standalone)
        <div class="page-header">
            <div class="page-header-body">
                <nav class="page-breadcrumb" aria-label="Breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('accountflow::payment-methods') }}" wire:navigate>Payment Methods</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $isEdit ? 'Edit' : 'Add' }}</li>
                    </ol>
                </nav>
                <div class="page-header-title"><h1>{{ $isEdit ? 'Edit Payment Method' : 'Add Payment Method' }}</h1></div>
            </div>
            <div class="page-header-actions">
                <a href="{{ route('accountflow::payment-methods') }}" class="btn btn-soft-secondary" wire:navigate>
                    <i data-lucide="arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    @endunless

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-body">
                    <form wire:submit.prevent="save" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('form.name') is-invalid @enderror"
                                wire:model="form.name" placeholder="Enter payment method name">
                            @error('form.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Info</label>
                            <textarea class="form-control @error('form.info') is-invalid @enderror"
                                wire:model="form.info" rows="3" placeholder="Optional description or notes"></textarea>
                            @error('form.info') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Logo / Icon</label>
                            <input type="file" class="form-control @error('logoUpload') is-invalid @enderror"
                                wire:model="logoUpload" accept="image/*">
                            @error('logoUpload') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            @if ($logoUpload)
                                <img src="{{ $logoUpload->temporaryUrl() }}" class="img-thumbnail mt-2" width="96">
                            @endif
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Account</label>
                                <select class="form-select @error('form.account_id') is-invalid @enderror" wire:model="form.account_id">
                                    <option value="">Select account</option>
                                    @foreach($accounts ?? [] as $account)
                                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                                    @endforeach
                                </select>
                                @error('form.account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select class="form-select @error('form.status') is-invalid @enderror" wire:model="form.status">
                                    <option value="1">Active</option>
                                    <option value="2">Inactive</option>
                                </select>
                                @error('form.status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-3">
                            <a href="{{ route('accountflow::payment-methods') }}" class="btn btn-soft-secondary" wire:navigate>Cancel</a>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove>{{ $isEdit ? 'Update' : 'Save' }}</span>
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
                        @if ($logoUpload)
                            <img src="{{ $logoUpload->temporaryUrl() }}" class="rounded" width="48" height="48" style="object-fit:cover">
                        @else
                            <span class="icon-box icon-box-primary"><i data-lucide="credit-card"></i></span>
                        @endif
                        <div>
                            <div class="fw-semibold">{{ $form['name'] ?? '' ?: 'Payment method name' }}</div>
                            <span class="badge badge-soft-{{ (int) ($form['status'] ?? 1) === 1 ? 'success' : 'secondary' }}">
                                {{ (int) ($form['status'] ?? 1) === 1 ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                    @if(!empty($form['info']))
                        <p class="text-body-secondary text-size-sm mb-0">{{ Str::limit($form['info'], 140) }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
