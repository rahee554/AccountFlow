<div>
    <div class="page-header">
        <div class="page-header-body">
            <nav class="page-breadcrumb" aria-label="Breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('accountflow::loans.partners') }}" wire:navigate>Loan Partners</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $isEdit ? 'Edit' : 'Create' }}</li>
                </ol>
            </nav>
            <div class="page-header-title"><h1>{{ $isEdit ? 'Edit Loan Partner' : 'Add Loan Partner' }}</h1></div>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('accountflow::loans.partners') }}" class="btn btn-soft-secondary" wire:navigate>
                <i data-lucide="arrow-left"></i> Back to Partners
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-body">
                    <form wire:submit="save">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input wire:model="name" type="text" class="form-control @error('name') is-invalid @enderror" placeholder="Partner full name">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact Number <span class="text-danger">*</span></label>
                                <input wire:model="contact" type="text" class="form-control @error('contact') is-invalid @enderror" placeholder="+1 234 567 8900">
                                @error('contact') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">CNIC / ID Number <span class="text-danger">*</span></label>
                                <input wire:model="cnic" type="text" class="form-control @error('cnic') is-invalid @enderror" placeholder="ID number">
                                @error('cnic') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Company (optional)</label>
                                <input wire:model="company" type="text" class="form-control @error('company') is-invalid @enderror" placeholder="Company name">
                                @error('company') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea wire:model="note" rows="3" class="form-control @error('note') is-invalid @enderror" placeholder="Optional notes..."></textarea>
                                @error('note') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-4">
                            <a href="{{ route('accountflow::loans.partners') }}" class="btn btn-soft-secondary" wire:navigate>Cancel</a>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove>{{ $isEdit ? 'Update Partner' : 'Save Partner' }}</span>
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
                        <span class="avatar avatar-lg avatar-primary">{{ $name ? strtoupper(substr($name, 0, 2)) : '?' }}</span>
                        <div>
                            <div class="fw-semibold">{{ $name ?: 'Partner name' }}</div>
                            <div class="text-body-secondary text-size-sm">{{ $company ?: 'No company' }}</div>
                        </div>
                    </div>
                    <div class="list-divided">
                        <div class="list-row justify-content-between">
                            <span class="text-body-secondary">Contact</span>
                            <span class="fw-semibold">{{ $contact ?: '—' }}</span>
                        </div>
                        <div class="list-row justify-content-between">
                            <span class="text-body-secondary">CNIC / ID</span>
                            <span class="fw-semibold">{{ $cnic ?: '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
