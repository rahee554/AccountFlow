<div>
    @include(config('accountflow.view_path') . '.blades.dashboard-header')

    <!-- Success/Error Messages -->
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

    <div class="card shadow-sm">
        <div class="card-header {{ $isEdit ? 'bg-warning text-dark' : 'bg-primary text-white' }}">
            <h4 class="mb-0">
                <i class="fas fa-{{ $isEdit ? 'edit' : 'plus-circle' }} me-2"></i>
                {{ $isEdit ? 'Edit Transaction' : 'Create New Transaction' }}
            </h4>
        </div>

        <div class="card-body">
            <form wire:submit.prevent="storeTransaction">
                <div class="row justify-content-center">
                    <div class="col-12 col-md-8 col-lg-6">
                        <div class="row g-3">
                            <!-- Payment Method -->
                            <div class="col-12 col-sm-6">
                                <label for="payment_method" class="form-label text-uppercase fw-bold text-dark fs-sm">
                                    Payment Method <span class="text-danger">*</span>
                                </label>
                                <select wire:model.change="payment_method"
                                    class="form-select form-select-sm @error('payment_method') is-invalid @enderror">
                                    <option value="">Select Payment Method</option>
                                    @foreach ($payment_methods as $method)
                                        <option value="{{ $method->id }}">{{ $method->name }}</option>
                                    @endforeach
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Account Selection -->
                            <div class="col-12 col-sm-6">
                                <label for="account_id" class="form-label text-uppercase fw-bold text-dark fs-sm">
                                    Select Account <span class="text-danger">*</span>
                                </label>
                                @if($payment_method)
                                    <!-- Read-only display when payment method is selected -->
                                    <select class="form-select form-select-sm" disabled>
                                        @foreach ($accounts as $account)
                                            @if($account->id == $account_id)
                                                <option value="{{ $account->id }}" selected>{{ $account->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    <!-- Hidden input to persist the value -->
                                    <input type="hidden" wire:model="account_id" value="{{ $account_id }}">
                                    <div class="form-text text-info">
                                        <i class="fas fa-lock me-1"></i>
                                        Account auto-selected from payment method
                                    </div>
                                @else
                                    <!-- Enabled select when no payment method is selected -->
                                    <select wire:model="account_id"
                                        class="form-select form-select-sm @error('account_id') is-invalid @enderror">
                                        <option value="">Select Account</option>
                                        @foreach ($accounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('account_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                @endif
                            </div>

                            <!-- Transaction Type -->
                            <div class="col-12 col-sm-6">
                                <label class="form-label text-uppercase fw-bold text-dark fs-sm">
                                    Transaction Type <span class="text-danger">*</span>
                                </label>
                                <select wire:model.change="type"
                                    class="form-select form-select-sm">
                                    <option value="1" {{ $type == 1 ? 'selected' : '' }}>Income</option>
                                    <option value="2" {{ $type == 2 ? 'selected' : '' }}>Expense</option>
                                </select>
                                @if($isEdit)
                                    <div class="form-text text-warning">
                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                        Changing type will reset category selection
                                    </div>
                                @endif
                            </div>

                            <!-- Category -->
                            <div class="col-12 col-sm-6">
                                <label class="form-label text-uppercase fw-bold text-dark fs-sm">
                                    Category <span class="text-danger">*</span>
                                </label>
                                <select wire:model="category_id"
                                    class="form-select form-select-sm @error('category_id') is-invalid @enderror">
                                    <option value="">Select Category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    Only <span
                                        class="badge bg-{{ $type == 1 ? 'success' : 'danger' }}">{{ $type == 1 ? 'Income' : 'Expense' }}</span>
                                    categories are shown
                                </div>
                            </div>

                            <!-- Date -->
                            <div class="col-12 col-sm-6">
                                <label for="date" class="form-label text-uppercase fw-bold text-dark fs-sm">
                                    Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" wire:model="date"
                                    class="form-control form-control-sm @error('date') is-invalid @enderror">
                                @error('date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Amount -->
                            <div class="col-12 col-sm-6">
                                <label for="amount" class="form-label text-uppercase fw-bold text-dark fs-sm">
                                    Amount <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">PKR</span>
                                    <input type="number" wire:model="amount"
                                        class="form-control @error('amount') is-invalid @enderror"
                                        placeholder="Enter Amount" step="0.01" min="0.01">
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="col-12">
                                <label class="form-label text-uppercase fw-bold text-dark fs-sm">Description</label>
                                <textarea wire:model="description"
                                    class="form-control form-control-sm @error('description') is-invalid @enderror"
                                    rows="3" placeholder="Details / Description"></textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Buttons -->
                            <div class="col-12 mt-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Fields marked with <span class="text-danger">*</span> are required
                                    </small>
                                    <div class="d-flex gap-2">
                                        <a href="{{route('accountflow::transactions')}}"
                                            class="btn btn-secondary btn-sm px-4" wire:navigate>
                                            <i class="fas fa-arrow-left me-1"></i>
                                            {{ $isEdit ? 'Back to List' : 'Cancel' }}
                                        </a>
                                        <button type="submit"
                                            class="btn btn-{{ $isEdit ? 'warning' : 'primary' }} btn-sm px-4"
                                            wire:loading.attr="disabled">
                                            <span wire:loading.remove>
                                                <i class="fas fa-save me-1"></i>
                                                {{ $isEdit ? 'Update Transaction' : 'Save Transaction' }}
                                            </span>
                                            <span wire:loading>
                                                <i class="fas fa-spinner fa-spin me-1"></i>
                                                {{ $isEdit ? 'Updating...' : 'Saving...' }}
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Transaction Preview -->
    @if($amount && $category_id)
        <div class="card mt-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="fas fa-eye me-2"></i>{{ $isEdit ? 'Updated Transaction Preview' : 'Transaction Preview' }}
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <div class="icon-preview bg-{{ $type == 1 ? 'success' : 'danger' }} text-white p-3 rounded-circle"
                            style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-{{ $type == 1 ? 'arrow-up' : 'arrow-down' }} fa-lg"></i>
                        </div>
                    </div>
                    <div>
                        <h5 class="mb-1">PKR {{ number_format($amount, 2) }}</h5>
                        <div class="d-flex gap-2 mb-2">
                            <span class="badge bg-{{ $type == 1 ? 'success' : 'danger' }}">
                                {{ $type == 1 ? 'Income' : 'Expense' }}
                            </span>
                            @if($category_id)
                                @php
                                    $categoryName = $categories->firstWhere('id', $category_id)->name ?? 'Unknown';
                                @endphp
                                <span class="badge bg-secondary">{{ $categoryName }}</span>
                            @endif
                            @if($date)
                                <span class="badge bg-info">{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</span>
                            @endif
                        </div>
                        @if($description)
                            <div class="small text-muted">
                                <strong>Description:</strong> {{ Str::limit($description, 100) }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>