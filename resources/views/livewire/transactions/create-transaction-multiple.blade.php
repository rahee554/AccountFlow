<div>
    <div class="page-header">
        <div class="page-header-body">
            <nav class="page-breadcrumb" aria-label="Breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('accountflow::transactions') }}" wire:navigate>Transactions</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Add Multiple</li>
                </ol>
            </nav>
            <div class="page-header-title"><h1>Add Multiple Transactions</h1></div>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('accountflow::transactions') }}" class="btn btn-soft-secondary" wire:navigate>
                <i data-lucide="arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form wire:submit.prevent="storeTransactions">

                        <!-- Transaction Type Selection -->
                        <div class="row g-3 mb-1">
                            <div class="col-12">
                                <label class="form-label">Transaction Type</label>
                                <div class="nav nav-segmented w-100">
                                    <button type="button" class="nav-link flex-fill @if((int) $type === 1) active @endif" wire:click="changeType(1)">Income</button>
                                    <button type="button" class="nav-link flex-fill @if((int) $type === 2) active @endif" wire:click="changeType(2)">Expense</button>
                                </div>
                            </div>
                        </div>

                        <!-- Common Fields -->
                        <div class="row g-3 my-1">
                            <div class="col-12 col-sm-6">
                                <label class="form-label">Account</label>
                                <select wire:model="account_id" class="form-select @error('account_id') is-invalid @enderror">
                                    <option value="">Select Account</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                                    @endforeach
                                </select>
                                @error('account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 col-sm-6">
                                <label class="form-label">Payment Method</label>
                                <select wire:model="payment_method" class="form-select @error('payment_method') is-invalid @enderror">
                                    <option value="">Select Payment Method</option>
                                    @foreach($payment_methods as $method)
                                        <option value="{{ $method->id }}">{{ $method->name }}</option>
                                    @endforeach
                                </select>
                                @error('payment_method') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <!-- Dynamic Transaction Rows -->
                        <div class="border rounded p-3 my-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h2 class="card-title mb-0">{{ $type == 1 ? 'Income' : 'Expense' }} Transactions</h2>
                                <button type="button" wire:click="addTransaction" class="btn btn-sm btn-soft-secondary">
                                    <i data-lucide="plus"></i> Add Another Transaction
                                </button>
                            </div>

                            @foreach($transactions as $index => $transaction)
                                <div class="row g-3 mb-3 pb-3 border-bottom" wire:key="transaction-{{ $index }}">
                                    <div class="col-6 col-md-2">
                                        <label class="form-label">Amount</label>
                                        <input type="number"
                                               wire:model="transactions.{{ $index }}.amount"
                                               class="form-control"
                                               placeholder="0.00"
                                               step="0.01">
                                        @error("transactions.{$index}.amount")
                                            <div class="text-danger text-size-sm">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-6 col-md-3">
                                        <label class="form-label">Category</label>
                                        <select wire:model="transactions.{{ $index }}.category_id" class="form-select">
                                            <option value="">Select Category</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                        @error("transactions.{$index}.category_id")
                                            <div class="text-danger text-size-sm">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-6 col-md-2">
                                        <label class="form-label">Date</label>
                                        <input type="date"
                                               wire:model="transactions.{{ $index }}.date"
                                               class="form-control">
                                        @error("transactions.{$index}.date")
                                            <div class="text-danger text-size-sm">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-6 col-md-4">
                                        <label class="form-label">Description</label>
                                        <input type="text"
                                               wire:model="transactions.{{ $index }}.description"
                                               class="form-control"
                                               placeholder="Transaction description">
                                        @error("transactions.{$index}.description")
                                            <div class="text-danger text-size-sm">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12 col-md-1 d-flex align-items-end">
                                        @if(count($transactions) > 1)
                                            <button type="button"
                                                    wire:click="removeTransaction({{ $index }})"
                                                    class="btn btn-sm btn-outline-danger">
                                                <i data-lucide="trash-2"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Global Description -->
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Global Description (Optional)</label>
                                <textarea wire:model="description"
                                          class="form-control"
                                          rows="3"
                                          placeholder="General notes for all transactions"></textarea>
                                @error('description') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="row">
                            <div class="col-12 d-flex justify-content-end gap-2 pt-3">
                                <a href="{{ route('accountflow::transactions') }}" class="btn btn-soft-secondary" wire:navigate>Cancel</a>
                                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                    <span wire:loading.remove>Save All Transactions</span>
                                    <span wire:loading>Saving…</span>
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
                    @php $total = collect($transactions)->sum(fn ($row) => is_numeric($row['amount'] ?? null) ? (float) $row['amount'] : 0); @endphp
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="icon-box icon-box-{{ (int) $type === 1 ? 'success' : 'danger' }}">
                            <i data-lucide="{{ (int) $type === 1 ? 'trending-up' : 'trending-down' }}"></i>
                        </span>
                        <div>
                            <div class="fs-5 fw-semibold">{{ config('accountflow.currency_symbols.' . config('accountflow.currency', 'PKR'), config('accountflow.currency', 'PKR') . ' ') }}{{ number_format($total, 2) }}</div>
                            <div class="text-body-secondary text-size-sm">{{ count($transactions) }} {{ Str::plural('transaction', count($transactions)) }}</div>
                        </div>
                    </div>
                    <span class="badge badge-soft-{{ (int) $type === 1 ? 'success' : 'danger' }}">{{ (int) $type === 1 ? 'Income' : 'Expense' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
