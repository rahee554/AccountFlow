<div>
          @include(config('accountflow.view_path') . '.blades.dashboard-header')

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Create Multiple Transactions</h3>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="storeTransactions">
                        
                        <!-- Transaction Type Selection -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <label class="form-label fw-bold">Transaction Type</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" wire:model="type" wire:change="changeType(1)" value="1" id="income_type">
                                    <label class="btn btn-outline-success" for="income_type">Income</label>
                                    
                                    <input type="radio" class="btn-check" wire:model="type" wire:change="changeType(2)" value="2" id="expense_type">
                                    <label class="btn btn-outline-danger" for="expense_type">Expense</label>
                                </div>
                            </div>
                        </div>

                        <!-- Common Fields -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Account</label>
                                <select wire:model="account_id" class="form-select form-select-sm">
                                    <option value="">Select Account</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                                    @endforeach
                                </select>
                                @error('account_id') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Payment Method</label>
                                <select wire:model="payment_method" class="form-select form-select-sm">
                                    <option value="">Select Payment Method</option>
                                    @foreach($payment_methods as $method)
                                        <option value="{{ $method->id }}">{{ $method->name }}</option>
                                    @endforeach
                                </select>
                                @error('payment_method') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Dynamic Transaction Rows -->
                        <div class="border rounded p-3 mb-4">
                            <h5 class="mb-3">{{ $type == 1 ? 'Income' : 'Expense' }} Transactions</h5>
                            
                            @foreach($transactions as $index => $transaction)
                                <div class="row mb-3 border-bottom pb-3" wire:key="transaction-{{ $index }}">
                                    <div class="col-md-2">
                                        <label class="form-label">Amount</label>
                                        <input type="number" 
                                               wire:model="transactions.{{ $index }}.amount" 
                                               class="form-control form-control-sm" 
                                               placeholder="0.00" 
                                               step="0.01">
                                        @error("transactions.{$index}.amount") 
                                            <span class="text-danger small">{{ $message }}</span> 
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label class="form-label">Category</label>
                                        <select wire:model="transactions.{{ $index }}.category_id" class="form-select form-select-sm">
                                            <option value="">Select Category</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                        @error("transactions.{$index}.category_id") 
                                            <span class="text-danger small">{{ $message }}</span> 
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-2">
                                        <label class="form-label">Date</label>
                                        <input type="date" 
                                               wire:model="transactions.{{ $index }}.date" 
                                               class="form-control form-control-sm">
                                        @error("transactions.{$index}.date") 
                                            <span class="text-danger small">{{ $message }}</span> 
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label">Description</label>
                                        <input type="text" 
                                               wire:model="transactions.{{ $index }}.description" 
                                               class="form-control form-control-sm" 
                                               placeholder="Transaction description">
                                        @error("transactions.{$index}.description") 
                                            <span class="text-danger small">{{ $message }}</span> 
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-1 d-flex align-items-end">
                                        @if(count($transactions) > 1)
                                            <button type="button" 
                                                    wire:click="removeTransaction({{ $index }})" 
                                                    class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            
                            <div class="text-center">
                                <button type="button" 
                                        wire:click="addTransaction" 
                                        class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-plus"></i> Add Another Transaction
                                </button>
                            </div>
                        </div>

                        <!-- Global Description -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <label class="form-label">Global Description (Optional)</label>
                                <textarea wire:model="description" 
                                          class="form-control form-control-sm" 
                                          rows="3" 
                                          placeholder="General notes for all transactions"></textarea>
                                @error('description') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="row">
                            <div class="col-12 text-end">
                                <a href="{{ route('accountflow::transactions') }}" class="btn btn-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <span wire:loading.remove>Save All Transactions</span>
                                    <span wire:loading>
                                        <span class="spinner-border spinner-border-sm me-2"></span>
                                        Saving...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>