@php
    use Illuminate\Support\Str;

    $route = request()->route();
    $prefix = $route ? $route->getPrefix() : null;
    $path = '/'.ltrim(request()->path(), '/');
    $routePrefix = trim((string) config('accountflow.route_prefix', 'accounts'), '/');

    // Show only for AccountFlow routes, using the configured package prefix.
    $isActualRoute = ($prefix && Str::contains($prefix, $routePrefix))
                     || Str::contains($path, '/' . $routePrefix)
                     || Str::startsWith($path, $routePrefix);
@endphp

@if($isActualRoute)
<!--begin::Enhanced Accounts Navigation-->
<div class="accounts-nav-wrapper">
    <!-- Desktop Navigation with Responsive Offcanvas -->
    <nav class="navbar navbar-expand-lg navbar-light bg-gradient-primary">
        <div class="container-fluid px-lg-4">
            <!-- Mobile Toggle Button -->
            <button class="navbar-toggler border-0 d-flex align-items-center gap-2" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#accountsNavOffcanvas" aria-controls="accountsNavOffcanvas">
                <i class="fas fa-bars fs-5"></i>
                <span class="menu-text">Menu</span>
            </button>

            <!-- Desktop Navigation Menu -->
            <div class="collapse navbar-collapse" id="navbarAccountsMenu">
                <ul class="navbar-nav mx-auto flex-wrap gap-1">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('accountflow::dashboard') ? 'active' : '' }}"
                            href="{{ route('accountflow::dashboard') }}"
                            wire:navigate.hover>
                            <i class="fas fa-home"></i>
                            <span class="d-none d-xl-inline ms-2">Dashboard</span>
                        </a>
                    </li>

                    <li class="nav-item dropdown">
                        <button class="nav-link dropdown-toggle {{ request()->routeIs('accountflow::accounts*') ? 'active' : '' }}"
                            id="accountsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-university"></i>
                            <span class="d-none d-xl-inline ms-2">Accounts</span>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="accountsDropdown">
                            <li><a class="dropdown-item" href="{{ route('accountflow::accounts') }}" wire:navigate.hover>
                                <i class="fas fa-list me-2"></i>Accounts List</a></li>
                            <li><a class="dropdown-item" href="{{ route('accountflow::accounts.create') }}" wire:navigate.hover>
                                <i class="fas fa-plus-circle me-2"></i>Add Account</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <button class="nav-link dropdown-toggle {{ request()->routeIs('accountflow::transactions*') ? 'active' : '' }}"
                            id="transactionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-exchange-alt"></i>
                            <span class="d-none d-xl-inline ms-2">Transactions</span>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="transactionsDropdown">
                            <li><a class="dropdown-item" href="{{ route('accountflow::transactions') }}" wire:navigate.hover>
                                <i class="fas fa-list me-2"></i>All Transactions</a></li>
                            @featureEnabled('assets')
                            <li><a class="dropdown-item" href="{{ route('accountflow::assets.transactions') }}" wire:navigate.hover>
                                <i class="fas fa-arrow-up me-2 text-success"></i>Assets Transactions</a></li>
                            @endFeatureEnabled
                            <li><a class="dropdown-item" href="{{ route('accountflow::transactions') }}?type=expense" wire:navigate.hover>
                                <i class="fas fa-arrow-down me-2 text-danger"></i>Loans Transactions</a></li>
                            @featureEnabled('transfers')
                            <li><a class="dropdown-item" href="{{ route('accountflow::transfers.list') }}" wire:navigate.hover>
                                <i class="fas fa-exchange-alt me-2 text-info"></i>Transfers List</a></li>
                            @endFeatureEnabled
                            <li><hr class="dropdown-divider"></li>
                            @featureEnabled('templates')
                            <li><a class="dropdown-item" href="{{ route('accountflow::transactions.templates') }}" wire:navigate.hover>
                                <i class="fas fa-copy me-2 text-info"></i>Transactions Template</a></li>
                            <li><hr class="dropdown-divider"></li>
                            @endFeatureEnabled
                            <li><a class="dropdown-item" href="{{ route('accountflow::transaction.create') }}" wire:navigate.hover>
                                <i class="fas fa-plus-circle me-2"></i>Add Transaction</a></li>
                            <li><a class="dropdown-item" href="{{ route('accountflow::transactions.create') }}" wire:navigate.hover>
                                <i class="fas fa-layer-group me-2"></i>Add Multiple Transactions</a></li>
                        </ul>
                    </li>

                    @featureEnabled('budgets')
                    <li class="nav-item dropdown">
                        <button class="nav-link dropdown-toggle {{ request()->routeIs('accountflow::budgets*') ? 'active' : '' }}"
                            id="budgetsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-wallet"></i>
                            <span class="d-none d-xl-inline ms-2">Budgets</span>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="budgetsDropdown">
                            <li><a class="dropdown-item" href="{{ route('accountflow::budgets') }}" wire:navigate.hover>
                                <i class="fas fa-list me-2"></i>Overview</a></li>
                            <li><a class="dropdown-item" href="{{ route('accountflow::budgets.create') }}" wire:navigate.hover>
                                <i class="fas fa-plus-circle me-2"></i>Add Budget</a></li>
                        </ul>
                    </li>
                    @endFeatureEnabled

                    @featureEnabled('planned_payments')
                    <li class="nav-item dropdown d-none d-xxl-block">
                        <button class="nav-link dropdown-toggle" id="plannedDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-calendar-check"></i>
                            <span class="d-none d-xl-inline ms-2">Planned Payments</span>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="plannedDropdown">
                            <li><a class="dropdown-item" href="{{ route('accountflow::planned-payments') }}" wire:navigate.hover>
                                <i class="fas fa-list me-2"></i>Planned Payments List</a></li>
                            <li><a class="dropdown-item" href="{{ route('accountflow::planned-payments.create') }}" wire:navigate.hover>
                                <i class="fas fa-plus-circle me-2"></i>Plan a Payment</a></li>
                        </ul>
                    </li>
                    @endFeatureEnabled

                    @featureEnabled('reports')
                    <li class="nav-item dropdown d-none d-xl-block">
                        <button class="nav-link dropdown-toggle {{ request()->routeIs('accountflow::report*') ? 'active' : '' }}"
                            id="reportsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-chart-bar"></i>
                            <span class="ms-2">Reports</span>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="reportsDropdown">
                            <li><a class="dropdown-item" href="{{ route('accountflow::report') }}" wire:navigate.hover>
                                <i class="fas fa-chart-line me-2"></i>Financial Summary</a></li>
                            @featureEnabled('profit_loss')
                            <li><a class="dropdown-item" href="{{ route('accountflow::report.profitLoss') }}" wire:navigate.hover>
                                <i class="fas fa-chart-area me-2"></i>Profit &amp; Loss</a></li>
                            @endFeatureEnabled
                            @featureEnabled('cashbook')
                            <li><a class="dropdown-item" href="{{ route('accountflow::report.cashbook') }}" wire:navigate.hover>
                                <i class="fas fa-book-open me-2"></i>Cashbook</a></li>
                            @endFeatureEnabled
                            @featureEnabled('trial_balance')
                            <li><a class="dropdown-item" href="{{ route('accountflow::report.trial-balance') }}" wire:navigate.hover>
                                <i class="fas fa-balance-scale me-2"></i>Trial Balance</a></li>
                            @endFeatureEnabled
                            <li><a class="dropdown-item" href="{{ route('accountflow::report.balance-sheet') }}" wire:navigate.hover>
                                <i class="fas fa-file-invoice me-2"></i>Balance Sheet</a></li>
                        </ul>
                    </li>
                    @endFeatureEnabled

                    @featureEnabled('assets')
                    <li class="nav-item dropdown d-none d-lg-block">
                        <button class="nav-link dropdown-toggle" id="assetsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-gem"></i>
                            <span class="d-none d-xl-inline ms-2">Assets</span>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="assetsDropdown">
                            <li><a class="dropdown-item" href="{{ route('accountflow::assets') }}" wire:navigate.hover>
                                <i class="fas fa-list me-2"></i>Assets List</a></li>
                            <li><a class="dropdown-item" href="{{ route('accountflow::assets.create') }}" wire:navigate.hover>
                                <i class="fas fa-plus-circle me-2"></i>Add Asset</a></li>
                        </ul>
                    </li>
                    @endFeatureEnabled

                    @featureEnabled('equity')
                    <li class="nav-item dropdown d-none d-lg-block">
                        <button class="nav-link dropdown-toggle" id="equityDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-users"></i>
                            <span class="d-none d-xl-inline ms-2">Equity</span>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="equityDropdown">
                            <li><a class="dropdown-item" href="{{ route('accountflow::equity.partners') }}" wire:navigate.hover>
                                <i class="fas fa-user-friends me-2"></i>Equity Partners</a></li>
                            <li><a class="dropdown-item" href="{{ route('accountflow::equity.transactions') }}" wire:navigate.hover>
                                <i class="fas fa-exchange-alt me-2"></i>Equity Transactions</a></li>
                            <li><a class="dropdown-item" href="{{ route('accountflow::equity.partners.create') }}" wire:navigate.hover>
                                <i class="fas fa-user-plus me-2"></i>Add Equity Partner</a></li>
                        </ul>
                    </li>
                    @endFeatureEnabled

                    @featureEnabled('loans')
                    <li class="nav-item dropdown d-none d-lg-block">
                        <button class="nav-link dropdown-toggle" id="loansDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-hand-holding-usd"></i>
                            <span class="d-none d-xl-inline ms-2">Loans & Credits</span>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="loansDropdown">
                            <li><a class="dropdown-item" href="{{ route('accountflow::loans') }}" wire:navigate.hover>
                                <i class="fas fa-users me-2"></i>Overview</a></li>
                            <li><a class="dropdown-item" href="{{ route('accountflow::loans.partners') }}" wire:navigate.hover>
                                <i class="fas fa-users me-2"></i>Loan Partners</a></li>
                            <li><a class="dropdown-item" href="{{ route('accountflow::loans.create') }}" wire:navigate.hover>
                                <i class="fas fa-plus-circle me-2"></i>Create Loan</a></li>
                        </ul>
                    </li>
                    @endFeatureEnabled

                    @featureEnabled('audit')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('accountflow::audittrail*') ? 'active' : '' }}"
                            href="{{ route('accountflow::audittrail') }}"
                            wire:navigate.hover>
                            <i class="fas fa-user-shield"></i>
                            <span class="d-none d-xl-inline ms-2">Audit Trail</span>
                        </a>
                    </li>
                    @endFeatureEnabled

                    <li class="nav-item dropdown d-none d-lg-block">
                        <button class="nav-link dropdown-toggle" id="settingsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-cog"></i>
                            <span class="d-none d-xl-inline ms-2">Settings</span>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="settingsDropdown">
                            <li><a class="dropdown-item" href="{{ route('accountflow::settings') }}" wire:navigate.hover>
                                <i class="fas fa-user-cog me-2"></i>Account Settings</a></li>
                            @featureEnabled('payment_methods')
                            <li><a class="dropdown-item" href="{{ route('accountflow::payment-methods') }}" wire:navigate.hover>
                                <i class="fas fa-credit-card me-2"></i>Payment Methods</a></li>
                            @endFeatureEnabled
                            @featureEnabled('categories')
                            <li><a class="dropdown-item" href="{{ route('accountflow::categories') }}" wire:navigate.hover>
                                <i class="fas fa-tags me-2"></i>Categories</a></li>
                            @endFeatureEnabled
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Mobile Offcanvas Navigation - Automatically triggered on small screens -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="accountsNavOffcanvas">
        <div class="offcanvas-header bg-gradient-primary text-white">
            <h5 class="offcanvas-title">
                <i class="fas fa-chart-pie me-2"></i>Accounts Menu
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body p-0">
            <ul class="navbar-nav flex-column w-100">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('accountflow::dashboard') ? 'active' : '' }}"
                        href="{{ route('accountflow::dashboard') }}"
                        wire:navigate.hover
                        data-bs-dismiss="offcanvas">
                        <i class="fas fa-home me-2"></i>Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#accountsCollapse" role="button" aria-expanded="false">
                        <i class="fas fa-university me-2"></i>Accounts<i class="fas fa-chevron-right ms-auto"></i>
                    </a>
                    <div class="collapse" id="accountsCollapse">
                        <ul class="navbar-nav ps-3">
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::accounts') }}" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-list me-2"></i>Accounts List</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::accounts.create') }}" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-plus-circle me-2"></i>Add Account</a></li>
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#transactionsCollapse" role="button" aria-expanded="false">
                        <i class="fas fa-exchange-alt me-2"></i>Transactions<i class="fas fa-chevron-right ms-auto"></i>
                    </a>
                    <div class="collapse" id="transactionsCollapse">
                        <ul class="navbar-nav ps-3">
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::transactions') }}" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-list me-2"></i>All Transactions</a></li>
                            @featureEnabled('assets')
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::assets.transactions') }}" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-arrow-up me-2 text-success"></i>Assets Transactions</a></li>
                            @endFeatureEnabled
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::transactions') }}?type=expense" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-arrow-down me-2 text-danger"></i>Loans Transactions</a></li>
                            @featureEnabled('transfers')
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::transfers.list') }}" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-exchange-alt me-2 text-info"></i>Transfers List</a></li>
                            @endFeatureEnabled
                            @featureEnabled('templates')
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::transactions.templates') }}" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-copy me-2 text-info"></i>Transactions Template</a></li>
                            @endFeatureEnabled
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::transaction.create') }}" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-plus-circle me-2"></i>Add Transaction</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::transactions.create') }}" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-layer-group me-2"></i>Add Multiple</a></li>
                        </ul>
                    </div>
                </li>

                @featureEnabled('budgets')
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#budgetsCollapse" role="button" aria-expanded="false">
                        <i class="fas fa-wallet me-2"></i>Budgets<i class="fas fa-chevron-right ms-auto"></i>
                    </a>
                    <div class="collapse" id="budgetsCollapse">
                        <ul class="navbar-nav ps-3">
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::budgets') }}" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-list me-2"></i>Overview</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::budgets.create') }}" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-plus-circle me-2"></i>Add Budget</a></li>
                        </ul>
                    </div>
                </li>
                @endFeatureEnabled

                @featureEnabled('planned_payments')
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#plannedCollapse" role="button" aria-expanded="false">
                        <i class="fas fa-calendar-check me-2"></i>Planned Payments<i class="fas fa-chevron-right ms-auto"></i>
                    </a>
                    <div class="collapse" id="plannedCollapse">
                        <ul class="navbar-nav ps-3">
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::planned-payments') }}" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-list me-2"></i>Planned Payments</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::planned-payments.create') }}" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-plus-circle me-2"></i>Plan a Payment</a></li>
                        </ul>
                    </div>
                </li>
                @endFeatureEnabled

                @featureEnabled('reports')
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#reportsCollapse" role="button" aria-expanded="false">
                        <i class="fas fa-chart-bar me-2"></i>Reports<i class="fas fa-chevron-right ms-auto"></i>
                    </a>
                    <div class="collapse" id="reportsCollapse">
                        <ul class="navbar-nav ps-3">
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::report') }}" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-chart-line me-2"></i>Financial Summary</a></li>
                            @featureEnabled('profit_loss')
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::report.profitLoss') }}" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-chart-area me-2"></i>Profit &amp; Loss</a></li>
                            @endFeatureEnabled
                            @featureEnabled('cashbook')
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::report.cashbook') }}" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-book-open me-2"></i>Cashbook</a></li>
                            @endFeatureEnabled
                            @featureEnabled('trial_balance')
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::report.trial-balance') }}" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-balance-scale me-2"></i>Trial Balance</a></li>
                            @endFeatureEnabled
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::report.balance-sheet') }}" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-file-invoice me-2"></i>Balance Sheet</a></li>
                        </ul>
                    </div>
                </li>
                @endFeatureEnabled

                @featureEnabled('assets')
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#assetsCollapse" role="button" aria-expanded="false">
                        <i class="fas fa-gem me-2"></i>Assets<i class="fas fa-chevron-right ms-auto"></i>
                    </a>
                    <div class="collapse" id="assetsCollapse">
                        <ul class="navbar-nav ps-3">
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::assets') }}" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-list me-2"></i>Assets List</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::assets.create') }}" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-plus-circle me-2"></i>Add Asset</a></li>
                        </ul>
                    </div>
                </li>
                @endFeatureEnabled

                @featureEnabled('equity')
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#equityCollapse" role="button" aria-expanded="false">
                        <i class="fas fa-users me-2"></i>Equity<i class="fas fa-chevron-right ms-auto"></i>
                    </a>
                    <div class="collapse" id="equityCollapse">
                        <ul class="navbar-nav ps-3">
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::equity.partners') }}" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-user-friends me-2"></i>Equity Partners</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::equity.transactions') }}" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-exchange-alt me-2"></i>Equity Transactions</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::equity.partners.create') }}" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-user-plus me-2"></i>Add Equity Partner</a></li>
                        </ul>
                    </div>
                </li>
                @endFeatureEnabled

                @featureEnabled('loans')
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#loansCollapse" role="button" aria-expanded="false">
                        <i class="fas fa-hand-holding-usd me-2"></i>Loans & Credits<i class="fas fa-chevron-right ms-auto"></i>
                    </a>
                    <div class="collapse" id="loansCollapse">
                        <ul class="navbar-nav ps-3">
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::loans') }}" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-users me-2"></i>Overview</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::loans.partners') }}" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-users me-2"></i>Loan Partners</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::loans.create') }}" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-plus-circle me-2"></i>Create Loan</a></li>
                        </ul>
                    </div>
                </li>
                @endFeatureEnabled

                @featureEnabled('audit')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('accountflow::audittrail*') ? 'active' : '' }}"
                        href="{{ route('accountflow::audittrail') }}"
                        wire:navigate.hover
                        data-bs-dismiss="offcanvas">
                        <i class="fas fa-user-shield me-2"></i>Audit Trail
                    </a>
                </li>
                @endFeatureEnabled

                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#settingsCollapse" role="button" aria-expanded="false">
                        <i class="fas fa-cog me-2"></i>Settings<i class="fas fa-chevron-right ms-auto"></i>
                    </a>
                    <div class="collapse" id="settingsCollapse">
                        <ul class="navbar-nav ps-3">
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::settings') }}" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-user-cog me-2"></i>Account Settings</a></li>
                            @featureEnabled('payment_methods')
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::payment-methods') }}" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-credit-card me-2"></i>Payment Methods</a></li>
                            @endFeatureEnabled
                            @featureEnabled('categories')
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::categories') }}" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-tags me-2"></i>Categories</a></li>
                            @endFeatureEnabled
                        </ul>
                    </div>
                </li>
            </ul>
        </div>
    </div>

    @push('scripts')
        {{-- Behaviour lives in public/assets/js/accountflow.js --}}
        <script src="{{ asset(config('accountflow.asset_path').'js/accountflow.js') }}" defer></script>
    @endpush

    @push('styles')
        {{-- Styles live in public/assets/css/accountflow.css. They were 510 lines
             of CSS inlined here, re-sent on every page render. --}}
        <link rel="stylesheet" href="{{ asset(config('accountflow.asset_path').'css/accountflow.css') }}">
    @endpush
</div>
<!--end::Enhanced Accounts Navigation-->
@endif


