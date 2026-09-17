<div>
    <div class="page-header">
        <div class="page-header-body">
            <nav class="page-breadcrumb" aria-label="Breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('accountflow::dashboard') }}" wire:navigate>Accounts</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Settings</li>
                </ol>
            </nav>
            <div class="page-header-title">
                <h1>Account Settings</h1>
                @if ($isAdminManagementEnabled && $isAdmin)
                    <span class="badge badge-soft-success">Admin Mode</span>
                @endif
            </div>
            <p class="page-header-subtitle">Configure your accounting module defaults and features</p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i data-lucide="check-circle-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i data-lucide="circle-alert"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($isAdminManagementEnabled && !$isAdmin)
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i data-lucide="lock"></i>
            <strong>Admin Only:</strong> Feature management is restricted to administrators. You can view settings but cannot make changes.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- Modules Card --}}
        <div class="col-lg-6">
            <div class="card h-100 {{ !$isAdmin && $isAdminManagementEnabled ? 'opacity-75' : '' }}">
                <div class="card-header">
                    <div><h2 class="card-title"><i data-lucide="puzzle"></i> Modules</h2></div>
                    <div class="card-actions">
                        <span class="badge badge-soft-secondary">{{ count($featureSettings) }} Available</span>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-body-secondary text-size-sm mb-3">Enable or disable modules to show/hide features across the application</p>
                    <form wire:submit.prevent="saveSettings" {{ !$isAdmin && $isAdminManagementEnabled ? 'onsubmit=return false;' : '' }}>
                        <div class="row">
                            @php
                                $featureLabels = [
                                    'multi_accounts_module' => 'Multi Accounts Module',
                                    'custom_category' => 'Custom Category',
                                    'cashbook_module' => 'Cashbook Module',
                                    'trial_balance_module' => 'Trial Balance Module',
                                    'assets_module' => 'Assets Module',
                                    'purchase_module' => 'Purchase Module',
                                    'multi_payment_methods' => 'Multi Payment Methods',
                                    'loan_module' => 'Loan Module',
                                    'user_wallet_module' => 'User Wallet Module',
                                    'income_form' => 'Income Form',
                                    'equity_module' => 'Equity Module',
                                    'budgets_module' => 'Budgets Module',
                                    'planned_payments_module' => 'Planned Payments Module',
                                    'transaction_templates' => 'Transaction Templates',
                                    'audit_trail' => 'Audit Trail',
                                    'payment_methods_module' => 'Payment Methods Module',
                                    'categories_module' => 'Categories Module',
                                    'transfers_module' => 'Transfers Module',
                                    'profit_loss_report' => 'Profit & Loss Report',
                                    'trial_balance_report' => 'Trial Balance Report',
                                ];
                            @endphp
                            @foreach($featureSettings as $key => $value)
                                <div class="col-md-6 mb-3">
                                    <div class="form-check form-switch">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            role="switch"
                                            id="feature_{{ $key }}"
                                            wire:model.lazy="featureSettings.{{ $key }}"
                                            @if($value === 'enabled') checked @endif
                                            {{ !$isAdmin && $isAdminManagementEnabled ? 'disabled' : '' }}
                                            value="enabled">
                                        <label class="form-check-label {{ !$isAdmin && $isAdminManagementEnabled ? 'text-body-secondary' : '' }}" for="feature_{{ $key }}">
                                            {{ $featureLabels[$key] ?? ucwords(str_replace('_', ' ', $key)) }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary" {{ !$isAdmin && $isAdminManagementEnabled ? 'disabled' : '' }}>
                                <i data-lucide="save"></i> Update Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Actions & Defaults Card --}}
        <div class="col-lg-6">
            {{-- Quick Actions Card --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="card-title"><i data-lucide="zap"></i> Quick Actions</h2>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        @featureEnabled('categories')
                        <a href="{{ route('accountflow::categories.create') }}" class="btn btn-sm btn-soft-primary" wire:navigate>
                            <i data-lucide="plus"></i> Add Category
                        </a>
                        @endFeatureEnabled

                        <a href="{{ route('accountflow::accounts.create') }}" class="btn btn-sm btn-soft-primary" wire:navigate>
                            <i data-lucide="plus"></i> Add Account
                        </a>

                        @featureEnabled('payment_methods')
                        <a href="{{ route('accountflow::payment-methods.create') }}" class="btn btn-sm btn-soft-success" wire:navigate>
                            <i data-lucide="plus"></i> Add Payment Method
                        </a>
                        @endFeatureEnabled

                        @featureEnabled('budgets')
                        <a href="{{ route('accountflow::budgets.create') }}" class="btn btn-sm btn-soft-info" wire:navigate>
                            <i data-lucide="plus"></i> Add Budget
                        </a>
                        @endFeatureEnabled

                        @featureEnabled('planned_payments')
                        <a href="{{ route('accountflow::planned-payments.create') }}" class="btn btn-sm btn-soft-warning" wire:navigate>
                            <i data-lucide="plus"></i> Add Planned Payment
                        </a>
                        @endFeatureEnabled

                        @featureEnabled('assets')
                        <a href="{{ route('accountflow::assets.create') }}" class="btn btn-sm btn-soft-secondary" wire:navigate>
                            <i data-lucide="plus"></i> Add Asset
                        </a>
                        @endFeatureEnabled

                        @featureEnabled('transfers')
                        <a href="{{ route('accountflow::transfers.create') }}" class="btn btn-sm btn-soft-secondary" wire:navigate>
                            <i data-lucide="plus"></i> Add Transfer
                        </a>
                        @endFeatureEnabled
                    </div>
                </div>
            </div>

            {{-- Default Settings Card --}}
            <div class="card {{ !$isAdmin && $isAdminManagementEnabled ? 'opacity-75' : '' }}">
                <div class="card-header">
                    <h2 class="card-title"><i data-lucide="sliders-horizontal"></i> Default Settings</h2>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="saveSettings" {{ !$isAdmin && $isAdminManagementEnabled ? 'onsubmit=return false;' : '' }}>
                        <div class="mb-3">
                            <label class="form-label">Default Transaction Type</label>
                            <select class="form-select form-select-sm" wire:model="settings.default_transaction_type" {{ !$isAdmin && $isAdminManagementEnabled ? 'disabled' : '' }}>
                                @foreach($transactionTypes as $type)
                                    <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Default Sales Category</label>
                            <select class="form-select form-select-sm" wire:model="settings.default_sales_category_id" {{ !$isAdmin && $isAdminManagementEnabled ? 'disabled' : '' }}>
                                <option value="">Select sales category</option>
                                @foreach($salesCategories as $cat)
                                    <option value="{{ $cat['id'] }}">{{ $cat['name'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Default Account</label>
                            <select class="form-select form-select-sm" wire:model="settings.default_account_id" {{ !$isAdmin && $isAdminManagementEnabled ? 'disabled' : '' }}>
                                <option value="">Select account</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc['id'] }}">{{ $acc['name'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Default Expense Category</label>
                            <select class="form-select form-select-sm" wire:model="settings.default_expense_category_id" {{ !$isAdmin && $isAdminManagementEnabled ? 'disabled' : '' }}>
                                <option value="">Select expense category</option>
                                @foreach($expenseCategories as $cat)
                                    <option value="{{ $cat['id'] }}">{{ $cat['name'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Default Payment Method</label>
                            <select class="form-select form-select-sm" wire:model="settings.default_payment_method_id" {{ !$isAdmin && $isAdminManagementEnabled ? 'disabled' : '' }}>
                                <option value="">Select payment method</option>
                                @foreach($paymentMethods as $method)
                                    <option value="{{ $method['id'] }}">{{ $method['name'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Currency</label>
                            <select class="form-select form-select-sm" wire:model="settings.currency" {{ !$isAdmin && $isAdminManagementEnabled ? 'disabled' : '' }}>
                                @foreach(config('accountflow.currencies', ['PKR' => 'PKR — Pakistani Rupee']) as $code => $label)
                                    <option value="{{ $code }}" @selected(($settings['currency'] ?? 'PKR') === $code)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Sets the currency symbol used across the accounting module.</div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary" {{ !$isAdmin && $isAdminManagementEnabled ? 'disabled' : '' }}>
                                <i data-lucide="save"></i> Update Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
