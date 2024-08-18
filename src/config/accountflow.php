<?php

return [
    'view_path' => 'vendor.artflow-studio.accountflow.',
    'layout' => 'layouts.admin.app-fluid',
    'print_layout' => 'layouts.public.app',
    'asset_path' => 'vendor/artflow-studio/accountflow/assets/',
    'business_name' => fn () => appSettings()?->agency_name ?? '',

    // Define middlewares to be used in the route file
    'middlewares' => [
        'tenant.web',
        'auth',
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
