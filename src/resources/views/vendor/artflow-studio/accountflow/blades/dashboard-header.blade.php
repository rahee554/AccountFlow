@php
    use Illuminate\Support\Str;

    $route = request()->route();
    $prefix = $route ? $route->getPrefix() : null;
    $path = '/'.ltrim(request()->path(), '/');

    // Show when route prefix or URL path contains "accounts"
    $isActualRoute = ($prefix && Str::contains($prefix, 'accounts')) 
                     || Str::contains($path, '/accounts') 
                     || Str::startsWith($path, 'accounts');
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
                            <li><a class="dropdown-item" href="{{ route('accountflow::assets.transactions') }}" wire:navigate.hover>
                                <i class="fas fa-arrow-up me-2 text-success"></i>Assets Transactions</a></li>
                            <li><a class="dropdown-item" href="{{ route('accountflow::transactions') }}?type=expense" wire:navigate.hover>
                                <i class="fas fa-arrow-down me-2 text-danger"></i>Loans Transactions</a></li>
                            <li><a class="dropdown-item" href="{{ route('accountflow::transfers.list') }}" wire:navigate.hover>
                                <i class="fas fa-exchange-alt me-2 text-info"></i>Transfers List</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('accountflow::transactions.templates') }}" wire:navigate.hover>
                                <i class="fas fa-copy me-2 text-info"></i>Transactions Template</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('accountflow::transaction.create') }}" wire:navigate.hover>
                                <i class="fas fa-plus-circle me-2"></i>Add Transaction</a></li>
                            <li><a class="dropdown-item" href="{{ route('accountflow::transactions.create') }}" wire:navigate.hover>
                                <i class="fas fa-layer-group me-2"></i>Add Multiple Transactions</a></li>
                        </ul>
                    </li>

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

                    <li class="nav-item dropdown d-none d-xl-block">
                        <button class="nav-link dropdown-toggle {{ request()->routeIs('accountflow::report*') ? 'active' : '' }}"
                            id="reportsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-chart-bar"></i>
                            <span class="ms-2">Reports</span>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="reportsDropdown">
                            <li><a class="dropdown-item" href="{{ route('accountflow::report') }}" wire:navigate.hover>
                                <i class="fas fa-chart-line me-2"></i>Financial Summary</a></li>
                            <li><a class="dropdown-item" href="{{ route('accountflow::report.cashbook') }}" wire:navigate.hover>
                                <i class="fas fa-book-open me-2"></i>Cashbook</a></li>
                            <li><a class="dropdown-item" href="{{ route('accountflow::report.trial-balance') }}" wire:navigate.hover>
                                <i class="fas fa-balance-scale me-2"></i>Trial Balance</a></li>
                        </ul>
                    </li>

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

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('accountflow::audittrail*') ? 'active' : '' }}"
                            href="{{ route('accountflow::audittrail') }}"
                            wire:navigate.hover>
                            <i class="fas fa-user-shield"></i>
                            <span class="d-none d-xl-inline ms-2">Audit Trail</span>
                        </a>
                    </li>

                    <li class="nav-item dropdown d-none d-lg-block">
                        <button class="nav-link dropdown-toggle" id="settingsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-cog"></i>
                            <span class="d-none d-xl-inline ms-2">Settings</span>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="settingsDropdown">
                            <li><a class="dropdown-item" href="{{ route('accountflow::settings') }}" wire:navigate.hover>
                                <i class="fas fa-user-cog me-2"></i>Account Settings</a></li>
                            <li><a class="dropdown-item" href="{{ route('accountflow::payment-methods') }}" wire:navigate.hover>
                                <i class="fas fa-credit-card me-2"></i>Payment Methods</a></li>
                            <li><a class="dropdown-item" href="{{ route('accountflow::categories') }}" wire:navigate.hover>
                                <i class="fas fa-tags me-2"></i>Categories</a></li>
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
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::assets.transactions') }}" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-arrow-up me-2 text-success"></i>Assets Transactions</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::transactions') }}?type=expense" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-arrow-down me-2 text-danger"></i>Loans Transactions</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::transfers.list') }}" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-exchange-alt me-2 text-info"></i>Transfers List</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::transactions.templates') }}" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-copy me-2 text-info"></i>Transactions Template</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::transaction.create') }}" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-plus-circle me-2"></i>Add Transaction</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::transactions.create') }}" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-layer-group me-2"></i>Add Multiple</a></li>
                        </ul>
                    </div>
                </li>

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

                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#reportsCollapse" role="button" aria-expanded="false">
                        <i class="fas fa-chart-bar me-2"></i>Reports<i class="fas fa-chevron-right ms-auto"></i>
                    </a>
                    <div class="collapse" id="reportsCollapse">
                        <ul class="navbar-nav ps-3">
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::report') }}" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-chart-line me-2"></i>Financial Summary</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::report.cashbook') }}" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-book-open me-2"></i>Cashbook</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::report.trial-balance') }}" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-balance-scale me-2"></i>Trial Balance</a></li>
                        </ul>
                    </div>
                </li>

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

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('accountflow::audittrail*') ? 'active' : '' }}"
                        href="{{ route('accountflow::audittrail') }}"
                        wire:navigate.hover
                        data-bs-dismiss="offcanvas">
                        <i class="fas fa-user-shield me-2"></i>Audit Trail
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#settingsCollapse" role="button" aria-expanded="false">
                        <i class="fas fa-cog me-2"></i>Settings<i class="fas fa-chevron-right ms-auto"></i>
                    </a>
                    <div class="collapse" id="settingsCollapse">
                        <ul class="navbar-nav ps-3">
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::settings') }}" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-user-cog me-2"></i>Account Settings</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::payment-methods') }}" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-credit-card me-2"></i>Payment Methods</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('accountflow::categories') }}" wire:navigate.hover data-bs-dismiss="offcanvas"><i class="fas fa-tags me-2"></i>Categories</a></li>
                        </ul>
                    </div>
                </li>
            </ul>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Close offcanvas when clicking on links
                document.querySelectorAll('.offcanvas-body .nav-link[data-bs-dismiss="offcanvas"]').forEach(link => {
                    link.addEventListener('click', function () {
                        const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('accountsNavOffcanvas'));
                        if (offcanvas) {
                            offcanvas.hide();
                        }
                    });
                });
            });
        </script>
    @endpush

    @push('styles')
        <style>
            /* Gradient Background */
            .bg-gradient-primary {
                background: linear-gradient(135deg, #00aaff 0%, #0077cc 100%);
            }

            .accounts-nav-wrapper {
                margin-bottom: 2rem;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            }

            /* Navbar Customization */
            .navbar {
                padding: 1rem 0;
            }

            .navbar .nav-link {
                color: #ffffff !important;
                padding: 0.5rem 0.75rem;
                border-radius: 0.375rem;
                transition: all 0.15s ease;
                font-size: 0.875rem;
                font-weight: 500;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .navbar .nav-link:hover {
                background-color: rgba(255, 255, 255, 0.2);
                color: #ffffff;
            }

            .navbar .nav-link.active {
                background-color: rgba(255, 255, 255, 0.3);
                font-weight: 600;
            }

            .navbar .dropdown-menu {
                border: 1px solid #e4e6ef;
                box-shadow: 0 0.5rem 1.5rem 0.5rem rgba(0, 0, 0, 0.075);
                border-radius: 0.475rem;
                padding: 0.5rem 0;
                margin-top: 0.5rem;
            }

            .navbar .dropdown-item {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.5rem 1rem;
                color: #5e6278;
                font-size: 0.875rem;
                transition: all 0.15s ease;
            }

            .navbar .dropdown-item:hover {
                background-color: #f1faff;
                color: #009ef7;
            }

            .navbar .dropdown-item i {
                width: 16px;
                text-align: center;
            }

            /* Navbar Toggler Customization */
            .navbar-toggler {
                color: white !important;
                padding: 0.5rem 1rem;
                display: flex;
                align-items: center;
                gap: 0.5rem;
                font-size: 0.875rem;
                font-weight: 500;
            }

            .navbar-toggler .menu-text {
                display: none;
                white-space: nowrap;
            }

            @media (max-width: 991.98px) {
                .navbar-toggler .menu-text {
                    display: inline-block;
                }
            }

            /* Offcanvas Customization */
            .offcanvas {
                width: 280px !important;
            }

            .offcanvas-header {
                border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            }

            .offcanvas-body {
                padding: 0;
            }

            .offcanvas-body .nav-link {
                color: #5e6278;
                padding: 0.75rem 1rem;
                border-radius: 0;
                font-size: 0.875rem;
                transition: all 0.15s ease;
                display: flex;
                align-items: center;
            }

            .offcanvas-body .nav-link:hover {
                background-color: #f1faff;
                color: #009ef7;
                padding-left: 1.25rem;
            }

            .offcanvas-body .nav-link.active {
                background-color: #f1faff;
                color: #009ef7;
                font-weight: 600;
                border-left: 3px solid #009ef7;
                padding-left: calc(1rem - 3px);
            }

            .offcanvas-body .nav-link[data-bs-toggle="collapse"] {
                justify-content: space-between;
                cursor: pointer;
            }

            .offcanvas-body .collapse .nav-link {
                padding-left: 2rem;
                font-size: 0.8125rem;
            }

            .offcanvas-body .collapse .nav-link:hover {
                padding-left: 2.25rem;
            }

            .offcanvas-body .navbar-nav {
                gap: 0;
            }

            .offcanvas-body .navbar-nav .nav-item {
                width: 100%;
            }

            /* Responsive Breakpoints */
            @media (min-width: 992px) {
                .navbar-expand-lg .navbar-collapse {
                    display: flex !important;
                    flex-basis: auto;
                }

                .navbar-expand-lg .navbar-toggler {
                    display: none !important;
                }

                .navbar-toggler {
                    display: none !important;
                }
            }

            @media (max-width: 991.98px) {
                .navbar .nav-link {
                    padding: 0.75rem 1rem;
                    font-size: 0.8rem;
                }

                .navbar .nav-link span {
                    display: none;
                }

                .d-none.d-xl-block {
                    display: none !important;
                }

                .d-none.d-xxl-block {
                    display: none !important;
                }
            }

            @media (max-width: 767.98px) {
                .offcanvas {
                    width: 250px !important;
                }

                .container-fluid {
                    padding-right: 0;
                    padding-left: 0;
                }
            }

            /* Mobile optimized */
            @media (max-width: 576px) {
                .offcanvas {
                    width: 100% !important;
                }

                .navbar {
                    padding: 0.75rem 0;
                }
            }

            /* Animation for collapse */
            .collapse {
                transition: all 0.15s ease;
            }

            /* Icons styling */
            .navbar i,
            .offcanvas-body i {
                width: 18px;
                text-align: center;
            }
        
            /* Enhanced Gradient Cards */
            .gradient-card {
                background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
                color: white;
                border: none;
                box-shadow: 0 0.5rem 1.5rem 0.5rem rgba(0, 0, 0, 0.15);
                transform: scale(1);
                transition: all 0.3s ease;
            }

            .gradient-card:hover {
                transform: scale(1.02);
                box-shadow: 0 1rem 2rem 0.5rem rgba(0, 0, 0, 0.2);
            }

            .gradient-primary {
                --gradient-start: #3b82f6;
                --gradient-end: #1e40af;
            }

            .gradient-success {
                --gradient-start: #10b981;
                --gradient-end: #059669;
            }

            .gradient-danger {
                --gradient-start: #ef4444;
                --gradient-end: #dc2626;
            }

            .gradient-info {
                --gradient-start: #06b6d4;
                --gradient-end: #0891b2;
            }

            .gradient-warning {
                --gradient-start: #f59e0b;
                --gradient-end: #d97706;
            }

            /* Enhanced Quick Actions */
            .quick-action-item {
                display: flex;
                flex-direction: column;
                align-items: center;
                text-align: center;
                padding: 1.5rem 1rem;
                background: #fff;
                border-radius: 0.75rem;
                transition: all 0.3s ease;
                cursor: pointer;
                border: 1px solid #e4e6ef;
                height: 100%;
                min-height: 120px;
            }

            .quick-action-item:hover {
                transform: translateY(-5px);
                box-shadow: 0 0.5rem 1.5rem 0.5rem rgba(0, 0, 0, 0.1);
                border-color: #009ef7;
            }

            .quick-action-icon {
                width: 50px;
                height: 50px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 0.75rem;
                color: white;
                font-size: 1.25rem;
            }

            .quick-action-label {
                font-weight: 600;
                color: #181c32;
                margin-bottom: 0.25rem;
                font-size: 0.875rem;
            }

            .quick-action-description {
                color: #5e6278;
                font-size: 0.75rem;
                line-height: 1.4;
            }

            /* Enhanced Stats Cards */
            .stats-icon {
                width: 50px;
                height: 50px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                background: rgba(255, 255, 255, 0.2);
                font-size: 1.5rem;
            }

            .stats-value {
                font-size: 2.5rem;
                font-weight: 700;
                line-height: 1;
                text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            .stats-change {
                font-size: 0.875rem;
                font-weight: 600;
                opacity: 0.9;
            }

            /* Enhanced Card Styles */
            .card-flush {
                box-shadow: 0 0.1rem 1.5rem 0.1rem rgba(0, 0, 0, 0.075);
                border: 1px solid #e4e6ef;
                border-radius: 0.75rem;
                overflow: hidden;
            }

            .card-flush .card-header {
                border-bottom: 1px solid #e4e6ef;
                padding-bottom: 1.5rem;
                background: #f9f9f9;
            }

            .hover-elevate-up {
                transition: all 0.15s ease;
            }

            .hover-elevate-up:hover {
                transform: translateY(-5px);
                box-shadow: 0 0.5rem 1.5rem 0.5rem rgba(0, 0, 0, 0.1);
            }

            /* Enhanced Progress Bars */
            .progress {
                border-radius: 0.375rem;
                background-color: #f1f3f6;
                height: 6px;
            }

            .progress-bar {
                border-radius: 0.375rem;
                transition: width 0.6s ease;
            }


            /* Enhanced Badge Styles */
            .badge {
                font-weight: 500;
                font-size: 0.75rem;
                padding: 0.35em 0.65em;
                border-radius: 0.375rem;
            }

            /* Enhanced Symbol Styles */
            .symbol-label {
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 0.375rem;
                background: #f1f3f6;
            }

            /* Account Summary Enhancements */
            .account-summary-item {
                transition: all 0.3s ease;
            }

            .account-summary-item:hover {
                transform: translateX(5px);
            }

            .account-summary-item .border-dashed {
                border-style: dashed !important;
                border-width: 2px !important;
                transition: all 0.3s ease;
            }

            .account-summary-item:hover .border-dashed {
                border-style: solid !important;
                border-color: #009ef7 !important;
            }

            /* Category Analysis Enhancements */
            .category-item {
                transition: all 0.3s ease;
                padding: 1rem;
                border-radius: 0.5rem;
                border: 1px solid transparent;
            }

            .category-item:hover {
                background-color: #f8f9fa;
                border-color: #e4e6ef;
            }

            .expense-category-item {
                transition: all 0.3s ease;
                padding: 1rem;
                border-radius: 0.5rem;
                border: 1px solid transparent;
            }

            .expense-category-item:hover {
                background-color: #f8f9fa;
                border-color: #e4e6ef;
                transform: translateX(5px);
            }

            /* Enhanced Toolbar */
            .bg-gradient-primary {
                background: linear-gradient(135deg, #3b82f6, #1e40af);
            }

            /* Animation for counting numbers */
            @keyframes countUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .stats-value {
                animation: countUp 1s ease-out;
            }

            /* Responsive Design */
            @media (max-width: 768px) {
                .accounts-nav {
                    padding: 0.5rem 0;
                }

                .accounts-nav .nav-link {
                    padding: 0.5rem 1rem;
                    font-size: 0.8rem;
                }

                .accounts-nav .nav-link span {
                    display: none;
                }

                .accounts-nav .nav-link i {
                    margin: 0;
                }

                .quick-action-item {
                    min-height: 100px;
                    padding: 1rem 0.5rem;
                }

                .quick-action-icon {
                    width: 40px;
                    height: 40px;
                }

                .stats-value {
                    font-size: 1.8rem;
                }
            }

            /* Loading Animation */
            .loading-animation {
                position: relative;
                overflow: hidden;
            }

            .loading-animation::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
                animation: shimmer 1.5s infinite;
            }

            @keyframes shimmer {
                0% {
                    left: -100%;
                }

                100% {
                    left: 100%;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                function handleResponsiveNav() {
                    const nav = document.querySelector('.accounts-nav');
                    const moreDropdown = document.querySelector('.more-dropdown');
                    const moreDropdownMenu = document.getElementById('moreDropdownMenu');
                    const collapsibleItems = document.querySelectorAll('.nav-collapsible');

                    if (!nav || !moreDropdown || !moreDropdownMenu) return;

                    // Reset all items to visible
                    collapsibleItems.forEach(item => {
                        item.style.display = 'block';
                    });
                    moreDropdown.classList.add('d-none');
                    moreDropdownMenu.innerHTML = '';

                    // Check if overflow
                    if (nav.scrollWidth > nav.clientWidth) {
                        const navItems = Array.from(collapsibleItems);
                        let movedItems = [];

                        // Move items to "More" dropdown until no overflow
                        for (let i = navItems.length - 1; i >= 0; i--) {
                            if (nav.scrollWidth <= nav.clientWidth) break;

                            const item = navItems[i];
                            const clone = item.cloneNode(true);
                            clone.classList.remove('nav-item', 'dropdown');
                            clone.classList.add('dropdown-item');

                            const link = clone.querySelector('.nav-link');
                            if (link) {
                                link.classList.remove('nav-link', 'dropdown-toggle');
                                link.classList.add('dropdown-item');
                                link.removeAttribute('data-bs-toggle');
                            }

                            moreDropdownMenu.appendChild(clone);
                            item.style.display = 'none';
                            movedItems.push(item);
                        }

                        if (movedItems.length > 0) {
                            moreDropdown.classList.remove('d-none');
                        }
                    }
                }

                // Run on load and resize
                handleResponsiveNav();
                window.addEventListener('resize', handleResponsiveNav);

                // Close mobile navigation when clicking on a link
                document.querySelectorAll('.mobile-nav-item, .mobile-nav-subitem').forEach(link => {
                    link.addEventListener('click', function () {
                        const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById(
                            'mobileNavOffcanvas'));
                        if (offcanvas) {
                            offcanvas.hide();
                        }
                    });
                });
            });
        </script>
    @endpush
</div>
<!--end::Enhanced Accounts Navigation-->
@endif


