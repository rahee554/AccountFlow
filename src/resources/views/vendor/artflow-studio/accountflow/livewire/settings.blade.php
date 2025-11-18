<div>
    @include(config('accountflow.view_path') . '.blades.dashboard-header')

    <div class="container-fluid py-4">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($isAdminManagementEnabled && !$isAdmin)
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-lock me-2"></i>
                <strong>Admin Only:</strong> Feature management is restricted to administrators. You can view settings but cannot make changes.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-1">
                            <i class="fas fa-cog me-2 text-primary"></i>
                            Account Settings
                            @if ($isAdminManagementEnabled && $isAdmin)
                                <span class="badge bg-success ms-2">Admin Mode</span>
                            @endif
                        </h2>
                        <p class="text-muted mb-0">Configure your accounting module defaults and features</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Modules Card -->
            <div class="col-lg-6">
                <div class="card shadow-sm h-100 {{ !$isAdmin && $isAdminManagementEnabled ? 'opacity-75' : '' }}">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-puzzle-piece me-2"></i>Modules</h5>
                        <span class="badge bg-light text-dark">{{ count($featureSettings) }} Available</span>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Enable or disable modules to show/hide features across the application</p>
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
                                            <label class="form-check-label {{ !$isAdmin && $isAdminManagementEnabled ? 'text-muted' : '' }}" for="feature_{{ $key }}">
                                                {{ $featureLabels[$key] ?? ucwords(str_replace('_', ' ', $key)) }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-4 text-end">
                                <button type="submit" class="btn btn-primary" {{ !$isAdmin && $isAdminManagementEnabled ? 'disabled' : '' }}>
                                    <i class="fas fa-save me-2"></i>Update Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Actions & Defaults Card -->
            <div class="col-lg-6">
                <!-- Quick Actions Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            @featureEnabled('categories')
                            <a href="{{ route('accountflow::categories.create') }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-plus me-1"></i> Add Category
                            </a>
                            @endFeatureEnabled

                            <a href="{{ route('accountflow::accounts.create') }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-plus me-1"></i> Add Account
                            </a>

                            @featureEnabled('payment_methods')
                            <a href="{{ route('accountflow::payment-methods.create') }}" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-plus me-1"></i> Add Payment Method
                            </a>
                            @endFeatureEnabled

                            @featureEnabled('budgets')
                            <a href="{{ route('accountflow::budgets.create') }}" class="btn btn-sm btn-outline-info">
                                <i class="fas fa-plus me-1"></i> Add Budget
                            </a>
                            @endFeatureEnabled

                            @featureEnabled('planned_payments')
                            <a href="{{ route('accountflow::planned-payments.create') }}" class="btn btn-sm btn-outline-warning">
                                <i class="fas fa-plus me-1"></i> Add Planned Payment
                            </a>
                            @endFeatureEnabled

                            @featureEnabled('assets')
                            <a href="{{ route('accountflow::assets.create') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-plus me-1"></i> Add Asset
                            </a>
                            @endFeatureEnabled

                            @featureEnabled('transfers')
                            <a href="{{ route('accountflow::transfers.create') }}" class="btn btn-sm btn-outline-dark">
                                <i class="fas fa-plus me-1"></i> Add Transfer
                            </a>
                            @endFeatureEnabled
                        </div>
                    </div>
                </div>

                <!-- Default Settings Card -->
                <div class="card shadow-sm {{ !$isAdmin && $isAdminManagementEnabled ? 'opacity-75' : '' }}">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-sliders-h me-2"></i>Default Settings</h5>
                    </div>
                    <div class="card-body">
                        <form wire:submit.prevent="saveSettings" {{ !$isAdmin && $isAdminManagementEnabled ? 'onsubmit=return false;' : '' }}>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Default Transaction Type</label>
                                <select class="form-select form-select-sm" wire:model="settings.default_transaction_type" {{ !$isAdmin && $isAdminManagementEnabled ? 'disabled' : '' }}>
                                    @foreach($transactionTypes as $type)
                                        <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Default Sales Category</label>
                                <select class="form-select form-select-sm" wire:model="settings.default_sales_category_id" {{ !$isAdmin && $isAdminManagementEnabled ? 'disabled' : '' }}>
                                    <option value="">Select sales category</option>
                                    @foreach($salesCategories as $cat)
                                        <option value="{{ $cat['id'] }}">{{ $cat['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Default Account</label>
                                <select class="form-select form-select-sm" wire:model="settings.default_account_id" {{ !$isAdmin && $isAdminManagementEnabled ? 'disabled' : '' }}>
                                    <option value="">Select account</option>
                                    @foreach($accounts as $acc)
                                        <option value="{{ $acc['id'] }}">{{ $acc['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Default Expense Category</label>
                                <select class="form-select form-select-sm" wire:model="settings.default_expense_category_id" {{ !$isAdmin && $isAdminManagementEnabled ? 'disabled' : '' }}>
                                    <option value="">Select expense category</option>
                                    @foreach($expenseCategories as $cat)
                                        <option value="{{ $cat['id'] }}">{{ $cat['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Default Payment Method</label>
                                <select class="form-select form-select-sm" wire:model="settings.default_payment_method_id" {{ !$isAdmin && $isAdminManagementEnabled ? 'disabled' : '' }}>
                                    <option value="">Select payment method</option>
                                    @foreach($paymentMethods as $method)
                                        <option value="{{ $method['id'] }}">{{ $method['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mt-4 text-end">
                                <button type="submit" class="btn btn-primary" {{ !$isAdmin && $isAdminManagementEnabled ? 'disabled' : '' }}>
                                    <i class="fas fa-save me-2"></i>Update Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
