<div>
    @include(config('accountflow.view_path') . '.blades.dashboard-header')
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="d-flex align-items-center justify-content-between mb-6">
                    <div>
                        <h1 class="page-heading fw-bold fs-3 text-dark">{{ $isEdit ? 'Edit Loan Partner' : 'Add Loan Partner' }}</h1>
                        <div class="text-muted fw-semibold fs-7">
                            <a href="{{ route('accountflow::loans.partners') }}" wire:navigate class="text-muted text-hover-primary">Loan Partners</a> / {{ $isEdit ? 'Edit' : 'Create' }}
                        </div>
                    </div>
                    <a href="{{ route('accountflow::loans.partners') }}" wire:navigate class="btn btn-sm btn-light-primary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Partners
                    </a>
                </div>
                <form wire:submit="save">
                    <div class="row g-5 justify-content-center">
                        <div class="col-xl-7">
                            <div class="card card-flush mb-5">
                                <div class="card-header pt-5">
                                    <h3 class="card-title fw-bold text-dark"><i class="fas fa-user me-2 text-primary"></i>Partner Information</h3>
                                </div>
                                <div class="card-body pt-3">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label class="form-label required">Full Name</label>
                                            <input wire:model="name" type="text" class="form-control @error('name') is-invalid @enderror" placeholder="Partner full name">
                                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label required">Contact Number</label>
                                            <input wire:model="contact" type="text" class="form-control @error('contact') is-invalid @enderror" placeholder="+1 234 567 8900">
                                            @error('contact') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label required">CNIC / ID Number</label>
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
                                </div>
                            </div>
                            <div class="card card-flush">
                                <div class="card-body py-4 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary flex-grow-1" wire:loading.attr="disabled">
                                        <span wire:loading.remove><i class="fas fa-save me-1"></i>{{ $isEdit ? 'Update Partner' : 'Save Partner' }}</span>
                                        <span wire:loading><i class="fas fa-spinner fa-spin me-1"></i>Saving...</span>
                                    </button>
                                    <a href="{{ route('accountflow::loans.partners') }}" wire:navigate class="btn btn-light-secondary">Cancel</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
