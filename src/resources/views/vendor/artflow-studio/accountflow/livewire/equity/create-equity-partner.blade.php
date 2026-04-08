<div>
    @include(config('accountflow.view_path') . '.blades.dashboard-header')

    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">

                {{-- Toolbar --}}
                <div class="d-flex align-items-center justify-content-between mb-6">
                    <div>
                        <h1 class="page-heading fw-bold fs-3 text-dark">
                            {{ $isEdit ? 'Edit Equity Partner' : 'Add Equity Partner' }}
                        </h1>
                        <div class="text-muted fw-semibold fs-7">
                            <a href="{{ route('accountflow::equity.partners') }}" wire:navigate class="text-muted text-hover-primary">
                                Equity Partners
                            </a> / {{ $isEdit ? 'Edit' : 'Create' }}
                        </div>
                    </div>
                    <a href="{{ route('accountflow::equity.partners') }}" wire:navigate
                       class="btn btn-sm btn-light-primary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Partners
                    </a>
                </div>

                <form wire:submit="save">
                    <div class="row g-5">
                        {{-- Main Info --}}
                        <div class="col-xl-8">
                            <div class="card card-flush mb-5">
                                <div class="card-header pt-5">
                                    <h3 class="card-title fw-bold text-dark">
                                        <i class="fas fa-user me-2 text-primary"></i>Partner Information
                                    </h3>
                                </div>
                                <div class="card-body pt-3">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label class="form-label required">Full Name</label>
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
                                </div>
                            </div>
                        </div>

                        {{-- Side Panel --}}
                        <div class="col-xl-4">
                            <div class="card card-flush mb-5">
                                <div class="card-header pt-5">
                                    <h3 class="card-title fw-bold text-dark">
                                        <i class="fas fa-chart-pie me-2 text-primary"></i>Equity Details
                                    </h3>
                                </div>
                                <div class="card-body pt-3">
                                    <div class="mb-4">
                                        <label class="form-label">Ownership Percentage (%)</label>
                                        <input wire:model="ownership_percentage" type="number" step="0.01" min="0" max="100"
                                               class="form-control @error('ownership_percentage') is-invalid @enderror"
                                               placeholder="0.00">
                                        @error('ownership_percentage') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label">Current Equity Amount</label>
                                        <input wire:model="current_equity" type="number" step="0.01" min="0"
                                               class="form-control @error('current_equity') is-invalid @enderror"
                                               placeholder="0.00">
                                        @error('current_equity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label">Date Joined</label>
                                        <input wire:model="joined_at" type="date"
                                               class="form-control @error('joined_at') is-invalid @enderror">
                                        @error('joined_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="mb-4">
                                        <div class="form-check form-switch">
                                            <input wire:model="is_active" class="form-check-input" type="checkbox" id="is_active">
                                            <label class="form-check-label fw-semibold" for="is_active">Active Partner</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card card-flush">
                                <div class="card-body py-4 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary flex-grow-1" wire:loading.attr="disabled">
                                        <span wire:loading.remove>
                                            <i class="fas fa-save me-1"></i>{{ $isEdit ? 'Update Partner' : 'Save Partner' }}
                                        </span>
                                        <span wire:loading>
                                            <i class="fas fa-spinner fa-spin me-1"></i>Saving...
                                        </span>
                                    </button>
                                    <a href="{{ route('accountflow::equity.partners') }}" wire:navigate
                                       class="btn btn-light-secondary">Cancel</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
