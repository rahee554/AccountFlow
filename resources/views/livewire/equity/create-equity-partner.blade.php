<div>
    <div class="page-header">
        <div class="page-header-body">
            <nav class="page-breadcrumb" aria-label="Breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('accountflow::equity.partners') }}" wire:navigate>Equity Partners</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $isEdit ? 'Edit' : 'Add' }}</li>
                </ol>
            </nav>
            <div class="page-header-title"><h1>{{ $isEdit ? 'Edit Equity Partner' : 'Add Equity Partner' }}</h1></div>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('accountflow::equity.partners') }}" class="btn btn-soft-secondary" wire:navigate>
                <i data-lucide="arrow-left"></i> Back to Partners
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
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
                                <label class="form-label">Email Address</label>
                                <input wire:model="email" type="email" class="form-control @error('email') is-invalid @enderror" placeholder="partner@example.com">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input wire:model="phone" type="text" class="form-control @error('phone') is-invalid @enderror" placeholder="+1 234 567 8900">
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Company / Organization</label>
                                <input wire:model="company" type="text" class="form-control @error('company') is-invalid @enderror" placeholder="Company name">
                                @error('company') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">National ID / CNIC / Passport</label>
                                <input wire:model="national_id" type="text" class="form-control @error('national_id') is-invalid @enderror" placeholder="ID number">
                                @error('national_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Address</label>
                                <input wire:model="address" type="text" class="form-control @error('address') is-invalid @enderror" placeholder="Street, city, country">
                                @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea wire:model="notes" rows="3" class="form-control @error('notes') is-invalid @enderror" placeholder="Optional notes about this partner..."></textarea>
                                @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <hr class="my-4">
                        <h2 class="card-title mb-3">Equity Details</h2>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Ownership Percentage (%)</label>
                                <input wire:model="ownership_percentage" type="number" step="0.01" min="0" max="100"
                                       class="form-control @error('ownership_percentage') is-invalid @enderror"
                                       placeholder="0.00">
                                @error('ownership_percentage') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Current Equity Amount</label>
                                <input wire:model="current_equity" type="number" step="0.01" min="0"
                                       class="form-control @error('current_equity') is-invalid @enderror"
                                       placeholder="0.00">
                                @error('current_equity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Date Joined</label>
                                <input wire:model="joined_at" type="date"
                                       class="form-control @error('joined_at') is-invalid @enderror">
                                @error('joined_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input wire:model="is_active" class="form-check-input" type="checkbox" id="is_active">
                                    <label class="form-check-label" for="is_active">Active Partner</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-4">
                            <a href="{{ route('accountflow::equity.partners') }}" class="btn btn-soft-secondary" wire:navigate>Cancel</a>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove>{{ $isEdit ? 'Update Partner' : 'Save Partner' }}</span>
                                <span wire:loading>Saving…</span>
                            </button>
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
                        <span class="icon-box icon-box-primary"><i data-lucide="handshake"></i></span>
                        <div>
                            <div class="fw-semibold">{{ $name ?: 'Partner name' }}</div>
                            <div class="text-body-secondary text-size-sm">{{ $company ?: 'No company' }}</div>
                        </div>
                    </div>
                    <div class="list-divided">
                        <div class="list-row justify-content-between">
                            <span class="text-body-secondary">Ownership</span>
                            <span class="fw-semibold">{{ number_format((float) ($ownership_percentage ?: 0), 2) }}%</span>
                        </div>
                        <div class="list-row justify-content-between">
                            <span class="text-body-secondary">Current Equity</span>
                            <span class="fw-semibold">{{ config('accountflow.currency_symbols.' . config('accountflow.currency', 'PKR'), config('accountflow.currency', 'PKR') . ' ') }}{{ number_format((float) ($current_equity ?: 0), 2) }}</span>
                        </div>
                        <div class="list-row justify-content-between">
                            <span class="text-body-secondary">Status</span>
                            <span class="badge badge-soft-{{ $is_active ? 'success' : 'secondary' }}">{{ $is_active ? 'Active' : 'Inactive' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
