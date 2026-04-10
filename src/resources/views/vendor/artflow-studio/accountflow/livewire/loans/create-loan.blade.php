<div>
    @include(config('accountflow.view_path') . 'blades.dashboard-header')
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="d-flex align-items-center justify-content-between mb-6">
                    <div>
                        <h1 class="page-heading fw-bold fs-3 text-dark">{{ $isEdit ? 'Edit Loan' : 'Create Loan' }}</h1>
                        <div class="text-muted fw-semibold fs-7">
                            <a href="{{ route('accountflow::loans') }}" wire:navigate class="text-muted text-hover-primary">Loans</a> / {{ $isEdit ? 'Edit' : 'Create' }}
                        </div>
                    </div>
                    <a href="{{ route('accountflow::loans') }}" wire:navigate class="btn btn-sm btn-light-primary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Loans
                    </a>
                </div>
                <form wire:submit="save">
                    <div class="row g-5">
                        <div class="col-xl-8">
                            <div class="card card-flush mb-5">
                                <div class="card-header pt-5">
                                    <h3 class="card-title fw-bold text-dark"><i class="fas fa-hand-holding-usd me-2 text-primary"></i>Loan Details</h3>
                                </div>
                                <div class="card-body pt-3">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label class="form-label required">Loan Name</label>
                                            <input wire:model="name" type="text" class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Business Expansion Loan">
                                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label required">Amount</label>
                                            <input wire:model="amount" type="number" step="0.01" min="0" class="form-control @error('amount') is-invalid @enderror" placeholder="0.00">
                                            @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label required">Loan Type</label>
                                            <select wire:model="loan_type" class="form-select @error('loan_type') is-invalid @enderror">
                                                <option value="1">Lended Out (I gave money)</option>
                                                <option value="2">Borrowed (I received money)</option>
                                            </select>
                                            @error('loan_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label required">Loan Partner</label>
                                            <select wire:model="loan_partner_id" class="form-select @error('loan_partner_id') is-invalid @enderror">
                                                <option value="">Select partner...</option>
                                                @foreach($loanPartners as $lp)
                                                    <option value="{{ $lp['id'] }}">{{ $lp['name'] }}</option>
                                                @endforeach
                                            </select>
                                            @error('loan_partner_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            <div class="mt-1"><a href="{{ route('accountflow::loans.partners.create') }}" class="fs-8 text-primary">+ Add new partner</a></div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Interest Rate (ROI %)</label>
                                            <input wire:model="roi" type="number" min="0" max="100" class="form-control @error('roi') is-invalid @enderror" placeholder="0">
                                            @error('roi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Installments</label>
                                            <input wire:model="installments" type="number" min="1" class="form-control @error('installments') is-invalid @enderror" placeholder="12">
                                            @error('installments') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Installment Type</label>
                                            <select wire:model="installment_type" class="form-select @error('installment_type') is-invalid @enderror">
                                                <option value="">None</option>
                                                <option value="1">Monthly</option>
                                                <option value="2">Quarterly</option>
                                                <option value="3">Yearly</option>
                                            </select>
                                            @error('installment_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Description</label>
                                            <textarea wire:model="description" rows="3" class="form-control @error('description') is-invalid @enderror" placeholder="Optional notes about this loan..."></textarea>
                                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4">
                            <div class="card card-flush mb-5">
                                <div class="card-header pt-5">
                                    <h3 class="card-title fw-bold text-dark"><i class="fas fa-calendar me-2 text-primary"></i>Timeline & Status</h3>
                                </div>
                                <div class="card-body pt-3">
                                    <div class="mb-4">
                                        <label class="form-label required">Loan Date</label>
                                        <input wire:model="date" type="date" class="form-control @error('date') is-invalid @enderror">
                                        @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label">Due Date</label>
                                        <input wire:model="due_date" type="date" class="form-control @error('due_date') is-invalid @enderror">
                                        @error('due_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label">Status</label>
                                        <select wire:model="status" class="form-select @error('status') is-invalid @enderror">
                                            <option value="1">Active</option>
                                            <option value="0">Closed</option>
                                        </select>
                                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="card card-flush">
                                <div class="card-body py-4 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary flex-grow-1" wire:loading.attr="disabled">
                                        <span wire:loading.remove><i class="fas fa-save me-1"></i>{{ $isEdit ? 'Update Loan' : 'Save Loan' }}</span>
                                        <span wire:loading><i class="fas fa-spinner fa-spin me-1"></i>Saving...</span>
                                    </button>
                                    <a href="{{ route('accountflow::loans') }}" wire:navigate class="btn btn-light-secondary">Cancel</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
