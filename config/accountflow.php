<?php

return [
    // Alias the pre-0.3.0 `App\Models\AccountFlow\*` class names to their current
    // `ArtflowStudio\AccountFlow\*` equivalents. Set this to false once your
    // application has been updated to the current namespaces.
    'legacy_aliases' => true,

    /*
     | Optional host-application models AccountFlow can relate to.
     |
     | Set `invoice_payment` to your own model to get a `$transaction->invoicePayment`
     | relation. Leave it null and the relation is simply not registered.
     */
    'models' => [
        'invoice_payment' => null,
    ],

    /*
     | Transfers.
     |
     | A transfer posts two linked ledger entries — an expense on the source
     | account and an income on the destination — so balances move and can be
     | rebuilt from the ledger. Both legs are booked under this category and
     | excluded from profit & loss, because moving your own cash is neither
     | revenue nor a cost. Leave it null and a "Transfers" category is created
     | once and reused.
     */
    'transfer_category_id' => null,

    /*
     | Create a category on the fly when money is recorded against a name that
     | does not exist yet.
     |
     | On by default: making someone pre-create a category before they can
     | write down an expense is exactly the friction this package removes. Set
     | false to fall back to the configured default category instead.
     */
    'auto_create_categories' => true,

    /*
     | Allow a transfer (or any posting) to take an account below zero.
     | Off by default: overdrawing is usually a data-entry mistake.
     */
    'allow_negative_balance' => false,

    'view_path' => 'accountflow::',
    // 'layout' => 'layouts.branch.app-fluid',
    'layout' => 'accountflow::layout.app',
    'print_layout' => 'accountflow::layouts.print',
    'asset_path' => 'vendor/artflow-studio/accountflow/assets/',
    'business_name' => 'Artflow ERP',

    /*
     | Middleware applied to every AccountFlow route.
     |
     | 'auth' is included deliberately. These routes expose the whole ledger —
     | balances, transactions, reports — so they must never be public. Remove it
     | only if your application authenticates through some other middleware.
     */
    'middlewares' => [
        'web',
        'auth',
    ],

    /*
     | Authorization.
     |
     | Each screen and action maps to an ability in
     | ArtflowStudio\AccountFlow\Enums\Ability. Resolution order:
     |
     |   1. disabled here                  -> allow
     |   2. host app defined the gate      -> the gate decides
     |   3. ability writes (manage-*)      -> admin_management.check below
     |   4. otherwise                      -> any authenticated user
     |
     | Override any ability by defining its gate:
     |
     |   Gate::define('accountflow.manage-transactions', fn ($user) => $user->isAccountant());
     */
    'authorization' => [
        'enabled' => true,
        'gate_prefix' => 'accountflow',
    ],

    /*
     | Who may manage AccountFlow — every "manage-*" ability, and the settings
     | screen. Two independent ways to grant it; either is enough on its own.
     |
     | 'roles' is the simplest option if you use spatie/laravel-permission (or
     | any user model with hasRole() / hasAnyRole()): give it a role name or a
     | list, and that role becomes an admin here. This is how a host app can
     | say "the 'business' role manages AccountFlow" without editing a single
     | route or Livewire component:
     |
     |   'roles' => 'business',
     |   'roles' => ['admin', 'business'],
     |
     | 'check' is the fallback for anything else: a method name on your user
     | model ('isAdmin'), an invokable class name, or a [Class::class, 'method']
     | pair. Closures are NOT supported — they cannot be serialized, and a
     | closure here makes `php artisan config:cache` fail.
     |
     | If neither matches, nobody is an admin — a safe failure, not a silent
     | wildcard.
     */
    'admin_management' => [
        'enabled' => true,
        'roles' => null,
        'check' => 'isAdmin',
        'redirect_to' => null, // Route name to redirect non-admins to; null aborts instead.
        'abort_code' => 403,
    ],

    // Feature-based route protection
    'feature_middlewares' => [
        'budgets' => ['budgets_module'],
        'planned-payments' => ['planned_payments_module'],
        'audit-trail' => ['audit_trail'],
        'equity' => ['equity_module'],
        'assets' => ['assets_module'],
        'categories' => ['categories_module'],
        'users-wallets' => ['user_wallet_module'],
        'transfers' => ['transfers_module'],
        'payment-methods' => ['payment_methods_module'],
        'loans' => ['loan_module'],
        'transactions/templates' => ['transaction_templates'],
        'report/cashbook' => ['cashbook_module'],
        'report/profit-and-loss' => ['profit_loss_report'],
        'report/trial-balance' => ['trial_balance_report'],
    ],

    'categories' => [
        'income' => [
            'Income' => [
                'Sales Income',
                // Add more income categories here
            ],
        ],
        'expense' => [
            'Regular Expense' => [
                'Food',
                'Refreshment',
                'Guests',
                'Cleaning',
            ],
            'Purchases' => [
                'Furniture',
                'Assets',
                'Electronics',
                'Accessories',
                'Stationery, Tools',
            ],
            'Bills & Utilities' => [
                'Electricity',
                'Internet',
                'Mobile, Phone',
            ],
            'Rentals' => [
                'Office Rent',
            ],
            'Promotion & Advertisement' => [
                'Social Media Promotion',
                'Print Media Promotion',
            ],
            'Other Expenses' => [
                'Charity & Donation',
                'Grocery',
                'Maintenance, Repairs',
                'Transport',
                'Others',
                'Renovation',
                'Labour Cost',
            ],
            'Financial Expenses' => [
                'Fees & Charges',
                'Repayment',
                'Staff Salaries',
            ],
        ],
    ],
    'accounts' => [
        'Cash Account',
        'Bank Account',
        'Mobile Wallet',
    ],
    'payment_methods' => [
        'Cash Payment',
        'Bank Account Payment',
        'EasyPaisa / Mobile Wallet',
    ],
    'dummy_data_seed' => false,
    'default_sales_category_id' => 1,
    'default_account_id' => 1,
    'default_expense_category_id' => 1,
    'route_prefix' => 'accounts',

    // Currency symbol/code used across all views and reports
    'currency' => 'PKR',

    // Display symbols for supported currencies (used in views and reports)
    'currency_symbols' => [
        'PKR' => 'Rs. ',
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'AED' => 'AED ',
        'SAR' => 'SAR ',
        'INR' => '₹',
        'BDT' => '৳',
    ],

    // Supported currencies list (used in settings dropdown)
    'currencies' => [
        'PKR' => 'PKR — Pakistani Rupee',
        'USD' => 'USD — US Dollar',
        'EUR' => 'EUR — Euro',
        'GBP' => 'GBP — British Pound',
        'AED' => 'AED — UAE Dirham',
        'SAR' => 'SAR — Saudi Riyal',
        'INR' => 'INR — Indian Rupee',
        'BDT' => 'BDT — Bangladeshi Taka',
    ],
];
