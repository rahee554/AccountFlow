<div>
    <div class="page-header">
        <div class="page-header-body">
            <nav class="page-breadcrumb" aria-label="Breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('accountflow::loans') }}" wire:navigate>Loans</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $isEdit ? 'Edit' : 'Create' }}</li>
                </ol>
            </nav>
            <div class="page-header-title"><h1>{{ $isEdit ? 'Edit Loan' : 'Create Loan' }}</h1></div>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('accountflow::loans') }}" class="btn btn-soft-secondary" wire:navigate>
                <i data-lucide="arrow-left"></i> Back to Loans
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form wire:submit="save">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Loan Name <span class="text-danger">*</span></label>
                                <input wire:model="name" type="text" class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Business Expansion Loan">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ config('accountflow.currency', 'PKR') }}</span>
                                    <input wire:model="amount" type="number" step="0.01" min="0" class="form-control @error('amount') is-invalid @enderror" placeholder="0.00">
                                </div>
                                @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Loan Type <span class="text-danger">*</span></label>
                                <div class="nav nav-segmented w-100">
                                    <button type="button" class="nav-link flex-fill @if((int) $loan_type === 1) active @endif" wire:click="$set('loan_type', 1)">Lended Out (I gave money)</button>
                                    <button type="button" class="nav-link flex-fill @if((int) $loan_type === 2) active @endif" wire:click="$set('loan_type', 2)">Borrowed (I received money)</button>
                                </div>
                                @error('loan_type') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Loan Partner <span class="text-danger">*</span></label>
                                <select wire:model="loan_partner_id" class="form-select @error('loan_partner_id') is-invalid @enderror">
                                    <option value="">Select partner...</option>
                                    @foreach($loanPartners as $lp)
                                        <option value="{{ $lp['id'] }}">{{ $lp['name'] }}</option>
                                    @endforeach
                                </select>
                                @error('loan_partner_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <div class="form-text">
                                    <a href="{{ route('accountflow::loans.partners.create') }}">+ Add new partner</a>
                                </div>
                            </div>

                            {{-- Recording a loan moves real money, so we have to know which
                                 account it lands in (or comes out of). Left blank, the default
                                 account is used. --}}
                            @unless($isEdit)
                                <div class="col-md-6">
                                    <label class="form-label">Account</label>
                                    <select wire:model="account_id" class="form-select @error('account_id') is-invalid @enderror">
                                        <option value="">Use the default account</option>
                                        @foreach($accounts as $acc)
                                            <option value="{{ $acc['id'] }}">{{ $acc['name'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <div class="form-text">
                                        Money borrowed is added to this account; money you lend is taken from it.
                                    </div>
                                </div>
                            @endunless

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

                        <div class="d-flex justify-content-end gap-2 pt-4">
                            <a href="{{ route('accountflow::loans') }}" class="btn btn-soft-secondary" wire:navigate>Cancel</a>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove>{{ $isEdit ? 'Update Loan' : 'Save Loan' }}</span>
                                <span wire:loading>Saving…</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><h2 class="card-title">Timeline &amp; Status</h2></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Loan Date <span class="text-danger">*</span></label>
                            <input wire:model="date" type="date" class="form-control @error('date') is-invalid @enderror">
                            @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Due Date</label>
                            <input wire:model="due_date" type="date" class="form-control @error('due_date') is-invalid @enderror">
                            @error('due_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="form-label">Status</label>
                            <select wire:model="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="1">Active</option>
                                <option value="0">Closed</option>
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
