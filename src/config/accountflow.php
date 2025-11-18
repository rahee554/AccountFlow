<?php

return [
    'view_path' => 'vendor.artflow-studio.accountflow.',
    //'layout' => 'layouts.branch.app-fluid',
    'layout' => 'vendor.artflow-studio.accountflow.layout.app',
    'print_layout' => 'vendor.artflow-studio.accountflow.layouts.print',
    'asset_path' => 'vendor/artflow-studio/accountflow/assets/',
    'business_name' => 'Artflow ERP',

    // Define middlewares to be used in the route file
    'middlewares' => [
        'web',
        //'auth',
    ],

    // Admin-only feature management configuration
    'admin_management' => [
        'enabled' => true, // Set to false to allow all users to manage features
        'check' => 'isAdmin', // Method name to check if user is admin (e.g., 'isAdmin' calls $user->isAdmin())
        // Alternative: use a callable
        // 'check' => fn($user) => $user->role === 'admin',
        'redirect_to' => 'dashboard', // Where to redirect non-admins
        'abort_code' => 403, // HTTP status code (403 = Forbidden, 401 = Unauthorized)
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
];
