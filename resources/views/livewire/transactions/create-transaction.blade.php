<div>
    @unless($standalone)
        <div class="page-header">
            <div class="page-header-body">
                <nav class="page-breadcrumb" aria-label="Breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('accountflow::transactions') }}" wire:navigate>Transactions</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $isEdit ? 'Edit' : 'Add' }}</li>
                    </ol>
                </nav>
                <div class="page-header-title"><h1>{{ $isEdit ? 'Edit Transaction' : 'Add Transaction' }}</h1></div>
            </div>
            <div class="page-header-actions">
                <a href="{{ route('accountflow::transactions') }}" class="btn btn-soft-secondary" wire:navigate>
                    <i data-lucide="arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    @endunless

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

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form wire:submit.prevent="storeTransaction">
                        <div class="row g-3">
                            <div class="col-12 col-sm-6">
                                <label for="payment_method" class="form-label">
                                    Payment Method <span class="text-danger">*</span>
                                </label>
                                <select wire:model.change="payment_method"
                                    class="form-select @error('payment_method') is-invalid @enderror">
                                    <option value="">Select Payment Method</option>
                                    @foreach ($payment_methods as $method)
                                        <option value="{{ $method->id }}">{{ $method->name }}</option>
                                    @endforeach
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-sm-6">
                                <label for="account_id" class="form-label">
                                    Account <span class="text-danger">*</span>
                                </label>
                                @if($payment_method)
                                    {{--
                                        Looked up directly rather than found inside $accounts:
                                        $accounts only lists active accounts (correct for manual
                                        picking), but the payment method's linked account can be
                                        one that's since been deactivated — it must still display
                                        here since it's already the value being saved.
                                    --}}
                                    @php $selectedAccount = \ArtflowStudio\AccountFlow\Models\Account::find($account_id); @endphp
                                    <select class="form-select" disabled>
                                        @if($selectedAccount)
                                            <option value="{{ $selectedAccount->id }}" selected>{{ $selectedAccount->name }}</option>
                                        @endif
                                    </select>
                                    <input type="hidden" wire:model="account_id" value="{{ $account_id }}">
                                    <div class="form-text">
                                        <i data-lucide="lock"></i> Auto-selected from payment method
                                    </div>
                                @else
                                    <select wire:model="account_id"
                                        class="form-select @error('account_id') is-invalid @enderror">
                                        <option value="">Select Account</option>
                                        @foreach ($accounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('account_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                @endif

                                @if($account_id && $this->accountBalance !== null)
                                    <div class="form-text {{ $this->accountBalance >= 0 ? 'text-success' : 'text-danger' }}">
                                        <i data-lucide="wallet"></i>
                                        Current balance: {{ config('accountflow.currency_symbols.' . config('accountflow.currency', 'PKR'), config('accountflow.currency', 'PKR') . ' ') }}{{ number_format($this->accountBalance, 2) }}
                                    </div>
                                @endif
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label">
                                    Type <span class="text-danger">*</span>
                                </label>
                                <div class="nav nav-segmented w-100">
                                    <button type="button" class="nav-link flex-fill @if((int) $type === 1) active @endif" wire:click="$set('type', 1)">Income</button>
                                    <button type="button" class="nav-link flex-fill @if((int) $type === 2) active @endif" wire:click="$set('type', 2)">Expense</button>
                                </div>
                                @if($isEdit)
                                    <div class="form-text text-warning">
                                        <i data-lucide="triangle-alert"></i> Changing type resets the category
                                    </div>
                                @endif
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label">
                                    Category <span class="text-danger">*</span>
                                </label>
                                <select wire:model="category_id"
                                    class="form-select @error('category_id') is-invalid @enderror">
                                    <option value="">Select Category</option>
                                    @foreach ($categories->groupBy(fn ($category) => $category->parent?->name ?? 'Other') as $group => $groupCategories)
                                        <optgroup label="{{ $group }}">
                                            @foreach ($groupCategories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-sm-6">
                                <label for="date" class="form-label">
                                    Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" wire:model="date"
                                    class="form-control @error('date') is-invalid @enderror">
                                @error('date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-sm-6">
                                <label for="amount" class="form-label">
                                    Amount <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ config('accountflow.currency', 'PKR') }}</span>
                                    <input type="number" wire:model="amount"
                                        class="form-control @error('amount') is-invalid @enderror"
                                        placeholder="0.00" step="0.01" min="0.01">
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea wire:model="description"
                                    class="form-control @error('description') is-invalid @enderror"
                                    rows="3" placeholder="Details / description"></textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                                <a href="{{ route('accountflow::transactions') }}" class="btn btn-soft-secondary" wire:navigate>
                                    {{ $isEdit ? 'Back to List' : 'Cancel' }}
                                </a>
                                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                    <span wire:loading.remove>{{ $isEdit ? 'Update Transaction' : 'Save Transaction' }}</span>
                                    <span wire:loading>{{ $isEdit ? 'Updating…' : 'Saving…' }}</span>
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
                    @if($amount && $category_id)
                        @php $categoryName = $categories->firstWhere('id', $category_id)->name ?? 'Unknown'; @endphp
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="icon-box icon-box-{{ (int) $type === 1 ? 'success' : 'danger' }}">
                                <i data-lucide="{{ (int) $type === 1 ? 'trending-up' : 'trending-down' }}"></i>
                            </span>
                            <div>
                                <div class="fs-5 fw-semibold">{{ config('accountflow.currency_symbols.' . config('accountflow.currency', 'PKR'), config('accountflow.currency', 'PKR') . ' ') }}{{ number_format($amount, 2) }}</div>
                                <div class="text-body-secondary text-size-sm">{{ $date ? \Carbon\Carbon::parse($date)->format('d M Y') : '' }}</div>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="badge badge-soft-{{ (int) $type === 1 ? 'success' : 'danger' }}">{{ (int) $type === 1 ? 'Income' : 'Expense' }}</span>
                            <span class="badge badge-soft-secondary">{{ $categoryName }}</span>
                        </div>
                        @if($description)
                            <p class="text-body-secondary text-size-sm mb-0">{{ Str::limit($description, 140) }}</p>
                        @endif
                    @else
                        <p class="text-body-secondary text-size-sm mb-0">Fill in the amount and category to see a preview here.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
