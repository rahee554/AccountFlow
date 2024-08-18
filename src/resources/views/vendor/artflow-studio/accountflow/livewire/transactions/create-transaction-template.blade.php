<div>
    @include(config('accountflow.view_path') . '.blades.dashboard-header')

    <div class="px-2 px-md-5 px-lg-10">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    <i class="fas fa-file-alt me-2"></i> Create Transaction Template
                </h4>
            </div>

            <div class="card-body">
                <form wire:submit.prevent="storeTemplate">
                    <div class="row g-3">
                        <!-- Template Name -->
                        <div class="col-12">
                            <label class="form-label fw-bold">Template Name <span class="text-danger">*</span></label>
                            <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Payment Method -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Payment Method <span class="text-danger">*</span></label>
                            <select wire:model="payment_method" class="form-select @error('payment_method') is-invalid @enderror">
                                <option value="">Select</option>
                                @foreach ($payment_methods as $method)
                                    <option value="{{ $method->id }}">{{ $method->name }}</option>
                                @endforeach
                            </select>
                            @error('payment_method') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Account -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Account <span class="text-danger">*</span></label>
                            <select wire:model="account_id" class="form-select @error('account_id') is-invalid @enderror">
                                <option value="">Select</option>
                                @foreach ($accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                                @endforeach
                            </select>
                            @error('account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Type -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Type</label>
                            <select wire:model="type" class="form-select">
                                <option value="1">Income</option>
                                <option value="2">Expense</option>
                            </select>
                        </div>

                        <!-- Category -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                            <select wire:model="category_id" class="form-select @error('category_id') is-invalid @enderror">
                                <option value="">Select</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Amount -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Default Amount</label>
                            <input type="number" step="0.01" min="0.01" wire:model="amount" class="form-control @error('amount') is-invalid @enderror">
                            @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label class="form-label fw-bold">Description</label>
                            <textarea wire:model="description" rows="3" class="form-control @error('description') is-invalid @enderror"></textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Buttons -->
                        <div class="col-12 d-flex justify-content-end gap-2">
                            <a href="{{ route('accountflow::transactions.templates') }}" class="btn btn-secondary btn-sm" wire:navigate>
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-save me-1"></i> Save Template
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
