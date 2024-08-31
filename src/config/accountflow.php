<?php

return [
    'view_path' => 'vendor.accountflow.',
    'layout' => 'layouts.app',
    
    // Define middlewares to be used in the route file
    'middlewares' => [
        'auth',          // Example middleware for authentication
        'verified',      // Example middleware for email verification
        'role:admin',    // Example middleware for role-based access control
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
];
