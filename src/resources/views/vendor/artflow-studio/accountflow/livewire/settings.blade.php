<div>
    @include(config('accountflow.view_path') . '.blades.dashboard-header')

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Account Module Default Settings</h5>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form wire:submit.prevent="saveSettings">

                        <div class="mb-3">
                            <a href="{{ route('accountflow::categories.create') }}" class="btn btn-primary btn-sm me-2">
                                + Add Category
                            </a>

                            <a href="{{ route('accountflow::accounts.create') }}" class="btn btn-outline-primary btn-sm me-2">
                                + Add Account
                            </a>

                            <!-- Payment Method button opens modal placeholder -->
                            <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#addPaymentMethodModal">
                                + Add Payment Method
                            </button>
                        </div>
                        <div class="row g-4">
                               <div class="col-md-6">
                                <label class="form-label fs-6 fw-bold">Default Transaction Type </label>
                                <select class="form-select form-select-sm" wire:model="settings.default_transaction_type">
                                    @foreach($transactionTypes as $type)
                                        <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fs-6 fw-bold">Default Sales Category</label>
                                <select class="form-select form-select-sm"
                                    wire:model="settings.default_sales_category_id">
                                    <option value="">Select sales category</option>
                                    @foreach($salesCategories as $cat)
                                        <option value="{{ $cat['id'] }}">{{ $cat['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fs-6 fw-bold">Default Account</label>
                                <select class="form-select form-select-sm"
                                    wire:model="settings.default_account_id">
                                    <option value="">Select account</option>
                                    @foreach($accounts as $acc)
                                        <option value="{{ $acc['id'] }}">{{ $acc['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fs-6 fw-bold">Default Expense Category</label>
                                <select class="form-select form-select-sm"
                                    wire:model="settings.default_expense_category_id">
                                    <option value="">Select expense category</option>
                                    @foreach($expenseCategories as $cat)
                                        <option value="{{ $cat['id'] }}">{{ $cat['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fs-6 fw-bold">Default Payment Method</label>
                                <select class="form-select form-select-sm"
                                    wire:model="settings.default_payment_method_id">
                                    <option value="">Select payment method</option>
                                    @foreach($paymentMethods as $method)
                                        <option value="{{ $method['id'] }}">{{ $method['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Module Toggles -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Accountflow Modules</label>
                            <div class="row">
                                @foreach($featureSettings as $key => $value)
                                    <div class="col-md-6 mb-2">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox"
                                                id="feature_{{ $key }}"
                                                wire:model.lazy="featureSettings.{{ $key }}"
                                                @if($value === 'enabled') checked @endif
                                                value="enabled">
                                            <label class="form-check-label" for="feature_{{ $key }}">
                                                {{ ucwords(str_replace('_', ' ', $key)) }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
