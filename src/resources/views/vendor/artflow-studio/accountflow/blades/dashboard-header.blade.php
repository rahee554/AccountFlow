<!--begin::Enhanced Accounts Navigation-->
<div class="accounts-nav-wrapper P-5">
    <!-- Mobile Navigation Toggle -->
    <div class="d-lg-none my-2">
        <button class="btn btn-transparent w-100" type="button" data-bs-toggle="offcanvas"
            data-bs-target="#mobileNavOffcanvas">
            <i class="fas fa-bars me-2"></i><span class="d-inline d-md-none">Menu</span><span
                class="d-none d-md-inline">Navigation Menu</span>
        </button>
    </div>

    <!-- Desktop Navigation -->
    <div class="accounts-nav p-5 d-none d-lg-flex flex-wrap">


        <div class="nav-item dropdown">
            <a class="nav-link {{ request()->routeIs('accountflow::dashboard') ? 'active' : '' }}"
                href="{{ route('accountflow::dashboard') }}"
                wire:navigate.hover="'{{ route('accountflow::dashboard') }}'">
                <i class="fas fa-home fs-5"></i>
                <span class="d-none d-xl-inline">Dashboard</span>
            </a>
        </div>

        <div class="nav-item dropdown nav-visible">
            <button class="nav-link dropdown-toggle {{ request()->routeIs('accountflow::accounts*') ? 'active' : '' }}"
                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-university fs-5"></i>
                <span class="d-none d-xl-inline">Accounts</span>
                <i class="fas fa-caret-down ms-1"></i>
            </button>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="{{ route('accountflow::accounts') }}"
                    wire:navigate.hover="'{{ route('accountflow::accounts') }}'">
                    <i class="fas fa-list me-2"></i>Accounts List
                </a>
                {{-- <a class="dropdown-item" href="{{ route('accountflow::users.wallets') }}"
                    wire:navigate.hover="'{{ route('accountflow::users.wallets') }}'">
                    <i class="fas fa-wallet me-2"></i>User Wallets
                </a> --}}
                <a class="dropdown-item" href="{{ route('accountflow::accounts.create') }}"
                    wire:navigate.hover="'{{ route('accountflow::accounts.create') }}'">
                    <i class="fas fa-plus-circle me-2"></i>Add Account
                </a>
            </div>
        </div>
        <div class="nav-item dropdown nav-visible">
            <button
                class="nav-link dropdown-toggle {{ request()->routeIs('accountflow::transactions*') ? 'active' : '' }}"
                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-exchange-alt fs-5"></i>
                <span class="d-none d-xl-inline">Transactions</span>
                <i class="fas fa-caret-down ms-1"></i>
            </button>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="{{ route('accountflow::transactions') }}"
                    wire:navigate.hover="'{{ route('accountflow::transactions') }}'">
                    <i class="fas fa-list me-2"></i>All Transactions
                </a>
                <a class="dropdown-item" href="{{ route('accountflow::assets.transactions') }}"
                    wire:navigate.hover="'{{ route('accountflow::assets.transactions') }}'">
                    <i class="fas fa-arrow-up me-2 text-success"></i>Assets Transactions
                </a>
                <a class="dropdown-item" href="{{ route('accountflow::transactions') }}?type=expense"
                    wire:navigate.hover="'{{ route('accountflow::transactions') }}?type=expense'">
                    <i class="fas fa-arrow-down me-2 text-danger"></i>Loans Transactions
                </a>
                <a class="dropdown-item" href="{{ route('accountflow::transfers.list') }}"
                    wire:navigate.hover="'{{ route('accountflow::transfers.list') }}'">
                    <i class="fas fa-exchange-alt me-2 text-info"></i>Transfers List
                </a>
                <div class="dropdown-divider"></div>

                <a class="dropdown-item" href="{{ route('accountflow::transactions.templates') }}"
                    wire:navigate.hover="'{{ route('accountflow::transactions.templates') }}'">
                    <i class="fas fa-copy me-2 text-info"></i>Transactions Template
                </a>

                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="{{ route('accountflow::transaction.create') }}"
                    wire:navigate.hover="'{{ route('accountflow::transaction.create') }}'">
                    <i class="fas fa-plus-circle me-2"></i>Add Transaction
                </a>
                <a class="dropdown-item" href="{{ route('accountflow::transactions.create') }}"
                    wire:navigate.hover="'{{ route('accountflow::transactions.create') }}'">
                    <i class="fas fa-layer-group me-2"></i>Add Multiple Transactions
                </a>
            </div>
        </div>
        <div class="nav-item dropdown nav-visible">
            <button class="nav-link dropdown-toggle {{ request()->routeIs('accountflow::budgets*') ? 'active' : '' }}"
                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-wallet fs-5"></i>
                <span class="d-none d-xl-inline">Budgets</span>
                <i class="fas fa-caret-down ms-1"></i>
            </button>
            <div class="dropdown-menu">

                <a class="dropdown-item" href="{{ route('accountflow::budgets') }}"
                    wire:navigate.hover="'{{ route('accountflow::budgets') }}'">
                    <i class="fas fa-list me-2"></i>Overview
                </a>
                <a class="dropdown-item" href="{{ route('accountflow::budgets.create') }}"
                    wire:navigate.hover="'{{ route('accountflow::budgets.create') }}'">
                    <i class="fas fa-plus-circle me-2"></i>Add Budget
                </a>
            </div>
        </div>


        <div class="nav-item dropdown nav-collapsible">
            <button class="nav-link dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-calendar-check fs-5"></i>
                <span class="d-none d-xl-inline">Planned Payments</span>
                <i class="fas fa-caret-down ms-1"></i>
            </button>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="{{ route('accountflow::planned-payments') }}"
                    wire:navigate.hover="'{{ route('accountflow::planned-payments') }}'">
                    <i class="fas fa-list me-2"></i>Planned Payments List
                </a>
                <a class="dropdown-item" href="{{ route('accountflow::planned-payments.create') }}"
                    wire:navigate.hover="'{{ route('accountflow::planned-payments.create') }}'">
                    <i class="fas fa-plus-circle me-2"></i>Plan a Payment
                </a>
            </div>
        </div>
        <div class="nav-item dropdown nav-collapsible">
            <button class="nav-link dropdown-toggle {{ request()->routeIs('accountflow::report*') ? 'active' : '' }}"
                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-chart-bar fs-5"></i>
                <span class="d-none d-xl-inline">Reports</span>
                <i class="fas fa-caret-down ms-1"></i>
            </button>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="{{ route('accountflow::report') }}"
                    wire:navigate.hover="'{{ route('accountflow::report') }}'">
                    <i class="fas fa-chart-line me-2"></i>Financial Summary
                </a>
                <a class="dropdown-item" href="{{ route('accountflow::report.cashbook') }}"
                    wire:navigate.hover="'{{ route('accountflow::report.cashbook') }}'">
                    <i class="fas fa-book-open me-2"></i>Cashbook
                </a>
                <a class="dropdown-item" href="{{ route('accountflow::report.trial-balance') }}"
                    wire:navigate.hover="'{{ route('accountflow::report.trial-balance') }}'">
                    <i class="fas fa-balance-scale me-2"></i>Trial Balance
                </a>
                <a class="dropdown-item" href="#" wire:navigate.hover="'#'">
                    <i class="fas fa-chart-pie me-2"></i>Expense Analysis
                </a>
                <a class="dropdown-item" href="#" wire:navigate.hover="'#'">
                    <i class="fas fa-chart-area me-2"></i>Income Analysis
                </a>
                <a class="dropdown-item" href="#" wire:navigate.hover="'#'">
                    <i class="fas fa-balance-scale me-2"></i>Balance Sheet
                </a>
                <a class="dropdown-item" href="#" wire:navigate.hover="'#'">
                    <i class="fas fa-file-invoice-dollar me-2"></i>P&L Statement
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" wire:navigate.hover="'#'">
                    <i class="fas fa-calendar-alt me-2"></i>Monthly Report
                </a>
            </div>
        </div>
        <div class="nav-item dropdown nav-collapsible">
            <button class="nav-link dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-gem fs-5"></i>
                <span class="d-none d-xl-inline">Assets</span>
                <i class="fas fa-caret-down ms-1"></i>
            </button>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="{{ route('accountflow::assets') }}"
                    wire:navigate.hover="'{{ route('accountflow::assets') }}'">
                    <i class="fas fa-list me-2"></i>Assets List
                </a>
                <a class="dropdown-item" href="{{ route('accountflow::assets.create') }}"
                    wire:navigate.hover="'{{ route('accountflow::assets.create') }}'">
                    <i class="fas fa-plus-circle me-2"></i>Add Asset
                </a>
            </div>
        </div>



        <div class="nav-item dropdown nav-collapsible">
            <button class="nav-link dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-users fs-5"></i>
                <span class="d-none d-xl-inline">Equity</span>
                <i class="fas fa-caret-down ms-1"></i>
            </button>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="{{ route('accountflow::equity.partners') }}"
                    wire:navigate.hover="'{{ route('accountflow::equity.partners') }}'">
                    <i class="fas fa-user-friends me-2"></i>Equity Partners
                </a>
                <a class="dropdown-item" href="{{ route('accountflow::equity.transactions') }}"
                    wire:navigate.hover="'{{ route('accountflow::equity.transactions') }}'">
                    <i class="fas fa-exchange-alt me-2"></i>Equity Transactions
                </a>
                <a class="dropdown-item" href="{{ route('accountflow::equity.partners.create') }}"
                    wire:navigate.hover="'{{ route('accountflow::equity.partners.create') }}'">
                    <i class="fas fa-user-plus me-2"></i>Add Equity Partner
                </a>
                <a class="dropdown-item" href="{{ route('accountflow::equity.partners.create') }}"
                    wire:navigate.hover="'{{ route('accountflow::equity.partners.create') }}'">
                    <i class="fas fa-plus-circle me-2"></i>Add Equity Transaction
                </a>
            </div>
        </div>

        <div class="nav-item dropdown nav-collapsible">
            <button class="nav-link dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-hand-holding-usd fs-5"></i>
                <span class="d-none d-xl-inline">Loans & Credits</span>
                <i class="fas fa-caret-down ms-1"></i>
            </button>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="{{ route('accountflow::loans') }}"
                    wire:navigate.hover="'{{ route('accountflow::loans') }}'">
                    <i class="fas fa-users me-2"></i>Overview
                </a>

                <a class="dropdown-item" href="{{ route('accountflow::loans.partners') }}"
                    wire:navigate.hover="'{{ route('accountflow::loans.partners') }}'">
                    <i class="fas fa-users me-2"></i>Loan Partners
                </a>
                <a class="dropdown-item" href="{{ route('accountflow::loans.create') }}"
                    wire:navigate.hover="'{{ route('accountflow::loans.create') }}'">
                    <i class="fas fa-plus-circle me-2"></i>Create Loan
                </a>
                <a class="dropdown-item" href="{{ route('accountflow::loans.partners') }}"
                    wire:navigate.hover="'{{ route('accountflow::loans.partners') }}'">
                    <i class="fas fa-exchange-alt me-2"></i>Add Loan Transaction
                </a>
            </div>
        </div>

        <div class="nav-item dropdown nav-visible">
            <a class="nav-link {{ request()->routeIs('accountflow::audittrail*') ? 'active' : '' }}"
                href="{{ route('accountflow::audittrail') }}"
                wire:navigate.hover="'{{ route('accountflow::audittrail') }}'">
                <i class="fas fa-user-shield fs-5"></i>
                <span class="d-none d-xl-inline">Audit Trail</span>
            </a>
        </div>


        <div class="nav-item dropdown nav-collapsible">
            <button class="nav-link dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-cog fs-5"></i>
                <span class="d-none d-xl-inline">Settings</span>
                <i class="fas fa-caret-down ms-1"></i>
            </button>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="{{ route('accountflow::settings') }}"
                    wire:navigate.hover="'{{ route('accountflow::settings') }}'">
                    <i class="fas fa-user-cog me-2"></i>Account Settings
                </a>
                <a class="dropdown-item" href="{{route('accountflow::payment-methods')}}" wire:navigate.hover="'#'">
                    <i class="fas fa-credit-card me-2"></i>Payment Methods
                </a>
                <a class="dropdown-item" href="{{ route('accountflow::categories') }}"
                    wire:navigate.hover="'{{ route('accountflow::categories') }}'">
                    <i class="fas fa-tags me-2"></i>Categories
                </a>
                <a class="dropdown-item" href="#" wire:navigate.hover="'#'">
                    <i class="fas fa-file-export me-2"></i>Export/Import
                </a>
            </div>
        </div>
    </div>

    <!-- Mobile Offcanvas Navigation -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="mobileNavOffcanvas">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">
                <i class="fas fa-chart-pie me-2"></i>Accounts Menu
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <div class="mobile-nav-menu">
                <a class="mobile-nav-item {{ request()->routeIs('accountflow::dashboard') ? 'active' : '' }}"
                    href="{{ route('accountflow::dashboard') }}">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <div class="mobile-nav-section">
                    <div class="mobile-nav-title">
                        <i class="fas fa-university"></i>
                        <span>Accounts</span>
                    </div>
                    <a class="mobile-nav-subitem" href="{{ route('accountflow::accounts') }}">
                        <i class="fas fa-list"></i>Accounts List
                    </a>
                    <a class="mobile-nav-subitem" href="{{ route('accountflow::accounts.create') }}">
                        <i class="fas fa-plus-circle"></i>Add Account
                    </a>
                </div>
                <div class="mobile-nav-section">
                    <div class="mobile-nav-title">
                        <i class="fas fa-wallet"></i>
                        <span>Budgets</span>
                    </div>
                    <a class="mobile-nav-subitem" href="{{ route('accountflow::budgets') }}">
                        <i class="fas fa-list"></i>Budgets List
                    </a>
                    <a class="mobile-nav-subitem" href="{{ route('accountflow::budgets.create') }}">
                        <i class="fas fa-plus-circle"></i>Add Budget
                    </a>
                </div>
                <div class="mobile-nav-section">
                    <div class="mobile-nav-title">
                        <i class="fas fa-user-shield"></i>
                        <span>Audit Trail</span>
                    </div>
                    <a class="mobile-nav-subitem" href="{{ route('accountflow::audittrail') }}">
                        <i class="fas fa-list"></i>Audit Trail Logs
                    </a>
                </div>
                <div class="mobile-nav-section">
                    <div class="mobile-nav-title">
                        <i class="fas fa-exchange-alt"></i>
                        <span>Transactions</span>
                    </div>
                    <a class="mobile-nav-subitem" href="{{ route('accountflow::transactions') }}?type=transfer">
                        <i class="fas fa-exchange-alt"></i>Transfers List
                    </a>
                    <a class="mobile-nav-subitem" href="#">
                        <i class="fas fa-plus-circle"></i>Create Transfer
                    </a>
                </div>
                <div class="mobile-nav-section">
                    <div class="mobile-nav-title">
                        <i class="fas fa-tags"></i>
                        <span>Categories</span>
                    </div>
                    <a class="mobile-nav-subitem" href="{{ route('accountflow::categories') }}">
                        <i class="fas fa-list"></i>All Categories
                    </a>
                    <a class="mobile-nav-subitem" href="{{ route('accountflow::categories') }}?type=income">
                        <i class="fas fa-arrow-up text-success"></i>Income Categories
                    </a>
                    <a class="mobile-nav-subitem" href="{{ route('accountflow::categories') }}?type=expense">
                        <i class="fas fa-arrow-down text-danger"></i>Expense Categories
                    </a>
                    <a class="mobile-nav-subitem" href="#">
                        <i class="fas fa-plus-circle"></i>Add Category
                    </a>
                </div>
                <div class="mobile-nav-section">
                    <div class="mobile-nav-title">
                        <i class="fas fa-calendar-check"></i>
                        <span>Planned Payments</span>
                    </div>
                    <a class="mobile-nav-subitem" href="#">
                        <i class="fas fa-list"></i>Planned Payments List
                    </a>
                    <a class="mobile-nav-subitem" href="#">
                        <i class="fas fa-plus-circle"></i>Plan a Payment
                    </a>
                </div>
                <div class="mobile-nav-section">
                    <div class="mobile-nav-title">
                        <i class="fas fa-hand-holding-usd"></i>
                        <span>Loans & Credits</span>
                    </div>
                    <a class="mobile-nav-subitem" href="#">
                        <i class="fas fa-users"></i>Loan Users
                    </a>
                    <a class="mobile-nav-subitem" href="#">
                        <i class="fas fa-plus-circle"></i>Create Loan
                    </a>
                    <a class="mobile-nav-subitem" href="#">
                        <i class="fas fa-exchange-alt"></i>Add Loan Transaction
                    </a>
                </div>
                <div class="mobile-nav-section">
                    <div class="mobile-nav-title">
                        <i class="fas fa-gem"></i>
                        <span>Assets</span>
                    </div>
                    <a class="mobile-nav-subitem" href="#">
                        <i class="fas fa-list"></i>Assets List
                    </a>
                    <a class="mobile-nav-subitem" href="#">
                        <i class="fas fa-plus-circle"></i>Add Asset
                    </a>
                </div>
                <div class="mobile-nav-section">
                    <div class="mobile-nav-title">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reports</span>
                    </div>
                    <a class="mobile-nav-subitem" href="{{ route('accountflow::report') }}">
                        <i class="fas fa-chart-line"></i>Financial Summary
                    </a>
                    <a class="mobile-nav-subitem" href="#">
                        <i class="fas fa-chart-pie"></i>Expense Analysis
                    </a>
                    <a class="mobile-nav-subitem" href="#">
                        <i class="fas fa-chart-area"></i>Income Analysis
                    </a>
                    <a class="mobile-nav-subitem" href="#">
                        <i class="fas fa-balance-scale"></i>Balance Sheet
                    </a>
                    <a class="mobile-nav-subitem" href="#">
                        <i class="fas fa-file-invoice-dollar"></i>P&L Statement
                    </a>
                    <a class="mobile-nav-subitem" href="#">
                        <i class="fas fa-calendar-alt"></i>Monthly Report
                    </a>
                </div>
                <div class="mobile-nav-section">
                    <div class="mobile-nav-title">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </div>
                    <a class="mobile-nav-subitem" href="#">
                        <i class="fas fa-user-cog"></i>Account Settings
                    </a>
                    <a class="mobile-nav-subitem" href="{{route('accountflow::payment-methods')}}">
                        <i class="fas fa-credit-card"></i>Payment Methodss
                    </a>
                    <a class="mobile-nav-subitem" href="#">
                        <i class="fas fa-tags"></i>Categories
                    </a>
                    <a class="mobile-nav-subitem" href="#">
                        <i class="fas fa-file-export"></i>Export/Import
                    </a>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            /* Ensure only the FontAwesome caret is shown (avoid duplicate Bootstrap caret) */
            .accounts-nav .nav-link.dropdown-toggle::after {
                display: none !important;
            }

            /* Enhanced Navigation Styles with Responsive Design */
            .accounts-nav-wrapper {
                background: linear-gradient(90deg, #00aaff 0%, #0077cc 100%);
                border-bottom: 1px solid #e4e6ef;
                margin-bottom: 2rem;
                position: relative;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            }

            .accounts-nav {
                display: flex;
                gap: 0;
                padding: 0.75rem 0;

                -webkit-overflow-scrolling: touch;
                scrollbar-width: none;
                -ms-overflow-style: none;
            }

            .accounts-nav::-webkit-scrollbar {
                display: none;
            }

            .accounts-nav .nav-item {
                position: relative;
                flex-shrink: 0;
                margin: 0 0.15rem;
            }

            .accounts-nav .nav-link {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.75rem 1.5rem;
                color: #ffffff;
                text-decoration: none;
                font-weight: 500;
                font-size: 0.875rem;
                border-radius: 0.375rem;
                transition: all 0.15s ease;
                white-space: nowrap;
                position: relative;
            }

            .accounts-nav .nav-link:hover {
                color: #009ef7;
                background-color: #f1faff;
            }

            .accounts-nav .nav-link.active {
                color: #009ef7;
                background-color: #f1faff;
                font-weight: 600;
            }

            .accounts-nav .nav-link.active::after {
                content: '';
                position: absolute;
                bottom: -0.75rem;
                left: 50%;
                transform: translateX(-50%);
                width: 6px;
                height: 6px;
                background: #009ef7;
                border-radius: 50%;
            }

            .accounts-nav .dropdown-menu {
                min-width: 200px;
                border: 1px solid #e4e6ef;
                box-shadow: 0 0.5rem 1.5rem 0.5rem rgba(0, 0, 0, 0.075);
                border-radius: 0.475rem;
                padding: 0.5rem 0;
                margin-top: 0.5rem;
                position: absolute;
            }

            .accounts-nav .dropdown-item {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.5rem 1rem;
                color: #5e6278;
                font-size: 0.875rem;
                transition: all 0.15s ease;
            }

            .accounts-nav .dropdown-item:hover {
                background-color: #f1faff;
                color: #009ef7;
            }

            .accounts-nav .dropdown-item i {
                width: 16px;
                text-align: center;
            }

            .accounts-nav .dropdown-divider {
                margin: 0.5rem 0;
            }

            /* Mobile Navigation Styles */
            .mobile-nav-menu {
                padding: 0;
            }

            .mobile-nav-item {
                display: flex;
                align-items: center;
                gap: 1rem;
                padding: 1rem;
                color: #5e6278;
                text-decoration: none;
                border-radius: 0.5rem;
                margin-bottom: 0.25rem;
                transition: all 0.15s ease;
            }

            .mobile-nav-item:hover,
            .mobile-nav-item.active {
                background-color: #f1faff;
                color: #009ef7;
            }

            .mobile-nav-section {
                margin-bottom: 1.5rem;
            }

            .mobile-nav-title {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                padding: 0.75rem 1rem;
                color: #181c32;
                font-weight: 600;
                font-size: 0.875rem;
                background-color: #f8f9fa;
                border-radius: 0.5rem;
                margin-bottom: 0.5rem;
            }

            .mobile-nav-subitem {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                padding: 0.75rem 1rem 0.75rem 2.5rem;
                color: #5e6278;
                text-decoration: none;
                border-radius: 0.5rem;
                margin-bottom: 0.25rem;
                transition: all 0.15s ease;
                font-size: 0.875rem;
            }

            .mobile-nav-subitem:hover {
                background-color: #f1faff;
                color: #009ef7;
            }

            .mobile-nav-subitem i {
                width: 16px;
                text-align: center;
            }

            /* Responsive Behavior */
            @media (max-width: 1400px) {
                .nav-collapsible:nth-last-child(n+3) {
                    display: none;
                }

                .more-dropdown {
                    display: block !important;
                }
            }

            @media (max-width: 1200px) {
                .nav-collapsible:nth-last-child(n+2) {
                    display: none;
                }
            }

            @media (max-width: 992px) {
                .nav-collapsible {
                    display: none;
                }
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