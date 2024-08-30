<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        
        $categories = [
            ['flow_type' => 1, 'name' => 'Income', 'parent_id' => NULL, 'privacy' => 1, 'status' => 1],
            ['flow_type' => 2, 'name' => 'Regular Expense', 'parent_id' => NULL, 'privacy' => 1, 'status' => 1],
            ['flow_type' => 2, 'name' => 'Purchases', 'parent_id' => NULL, 'privacy' => 1, 'status' => 1],
            ['flow_type' => 2, 'name' => 'Bills & Utilities', 'parent_id' => NULL, 'privacy' => 1, 'status' => 1],
            ['flow_type' => 2, 'name' => 'Rentals', 'parent_id' => NULL, 'privacy' => 1, 'status' => 1],
            ['flow_type' => 2, 'name' => 'Promotion & Advertisement', 'parent_id' => NULL, 'privacy' => 1, 'status' => 1],
            ['flow_type' => 2, 'name' => 'Other Expenses', 'parent_id' => NULL, 'privacy' => 1, 'status' => 1],
            ['flow_type' => 2, 'name' => 'Financial Expenses', 'parent_id' => NULL, 'privacy' => 1, 'status' => 1],
            ['flow_type' => 1, 'name' => 'Sales Income', 'parent_id' => 1, 'privacy' => 1, 'status' => 1],
            ['flow_type' => 2, 'name' => 'Food', 'parent_id' => 2, 'privacy' => 1, 'status' => 1],
            ['flow_type' => 2, 'name' => 'Refreshment', 'parent_id' => 2, 'privacy' => 1, 'status' => 1],
            ['flow_type' => 2, 'name' => 'Guests', 'parent_id' => 2, 'privacy' => 1, 'status' => 1],
            ['flow_type' => 2, 'name' => 'Furniture', 'parent_id' => 3, 'privacy' => 1, 'status' => 1],
            ['flow_type' => 2, 'name' => 'Assets', 'parent_id' => 3, 'privacy' => 1, 'status' => 1],
            ['flow_type' => 2, 'name' => 'Electronics', 'parent_id' => 3, 'privacy' => 1, 'status' => 1],
            ['flow_type' => 2, 'name' => 'Accessories', 'parent_id' => 3, 'privacy' => 1, 'status' => 1],
            ['flow_type' => 2, 'name' => 'Stationery, Tools', 'parent_id' => 3, 'privacy' => 1, 'status' => 1],
            ['flow_type' => 2, 'name' => 'Electricity', 'parent_id' => 4, 'privacy' => 1, 'status' => 1],
            ['flow_type' => 2, 'name' => 'Internet', 'parent_id' => 4, 'privacy' => 1, 'status' => 1],
            ['flow_type' => 2, 'name' => 'Mobile, Phone', 'parent_id' => 4, 'privacy' => 1, 'status' => 1],
            ['flow_type' => 2, 'name' => 'Office Rent', 'parent_id' => 5, 'privacy' => 1, 'status' => 1],
            ['flow_type' => 2, 'name' => 'Social Media Promotion', 'parent_id' => 6, 'privacy' => 1, 'status' => 1],
            ['flow_type' => 2, 'name' => 'Print Media Promotion', 'parent_id' => 6, 'privacy' => 1, 'status' => 1],
            ['flow_type' => 2, 'name' => 'Charity & Donation', 'parent_id' => 7, 'privacy' => 1, 'status' => 1],
            ['flow_type' => 2, 'name' => 'Grocery', 'parent_id' => 7, 'privacy' => 1, 'status' => 1],
            ['flow_type' => 2, 'name' => 'Maintenance, Repairs', 'parent_id' => 7, 'privacy' => 1, 'status' => 1],
            ['flow_type' => 2, 'name' => 'Transport', 'parent_id' => 7, 'privacy' => 1, 'status' => 1],
            ['flow_type' => 2, 'name' => 'Others', 'parent_id' => 7, 'privacy' => 1, 'status' => 1],
            ['flow_type' => 2, 'name' => 'Fees & Charges', 'parent_id' => 8, 'privacy' => 1, 'status' => 1],
            ['flow_type' => 2, 'name' => 'Repayment', 'parent_id' => 8, 'privacy' => 1, 'status' => 1],
            ['flow_type' => 2, 'name' => 'Staff Salaries', 'parent_id' => 8, 'privacy' => 1, 'status' => 1],
            ['flow_type' => 2, 'name' => 'Renovation', 'parent_id' => 7, 'privacy' => 1, 'status' => 1],
            ['flow_type' => 2, 'name' => 'Labour Cost', 'parent_id' => 7, 'privacy' => 1, 'status' => 1],
            ['flow_type' => 2, 'name' => 'Cleaning', 'parent_id' => 2, 'privacy' => 1, 'status' => 1],
        ];
        DB::table('ac_categories')->delete();
        foreach ($categories as $index => $category) {
            $category['id'] = $index + 1;
            DB::table('ac_categories')->insert($category);
        }
        

        // Accounts Table Seed Data
        DB::table('accounts')->delete();
        DB::table('accounts')->insert([
            [
                'name' => 'Cash Account',
            ],
            [
                'name' => 'Bank Account',
            ],
            [
                'name' => 'Mobile Wallet',
            ],
        ]);

        // Payment Method Seeders

        DB::table('ac_payment_methods')->delete();
        DB::table('ac_payment_methods')->insert([
            [
                'name' => 'Cash Payment',
            ],
            [
                'name' => 'Bank Account Payment',
            ],
            [
                'name' => 'EasyPaisa / Mobile Wallet',
            ],
        ]);
        DB::table('ac_transactions')->delete();
        DB::table('ac_transactions')->insert([
            [
                'unique_id' => '556488',
                'account_id' => '1',
                'category_id' => '10',
                'amount' => '12345',
            ],
        ]);

        DB::table('ac_assets')->delete();
        DB::table('ac_assets')->insert([
            [
                'name' => 'Dummy Asset Name',
                'description' => 'This is Asset Description',
                'value' => '12345',
                'category_id' => '16',
                'status' => '1',
                'acquisition_date' => '2020-12-10',
            ],
        ]);
        DB::table('ac_assets_trx')->delete();
        DB::table('ac_assets_trx')->insert([
            [
                'unique_id' => '1',
                'asset_id' => '1',
                'trx_id' => '1'
            ],
        ]);

        DB::table('ac_transfers')->delete();
        DB::table('ac_transfers')->insert([
            [
                'unique_id' => '546132',
                'from_account' => '2',
                'to_account' => '3',
                'description' => 'this is description',
                'date' => '2023-12-10',
                'created_by' => '1',
            ],
        ]);
        DB::table('ac_settings')->delete();
        $settings = [
            //modules
            'multi_accounts_module' => 'enabled',
            'custom_catetgory' => 'enabled',
            'ledger_module' => 'enabled',
            'cashbook_module' => 'enabled',
            'trial_balance_module' => 'enabled',
            'assets_module' => 'enabled',
            'purchase_module' => 'enabled',
            'multi_payment_methods' => 'enabled',
            'loan_module' => 'enabled',
            'user_wallet_module' => 'enabled',
            'income_form' => 'enabled',
            
            
            // Add more settings here
        ];

        foreach ($settings as $key => $value) {
            DB::table('ac_settings')->insert([
                'key' => $key,
                'value' => $value,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]);
        }
        

        DB::table('ac_user_wallets')->delete();
        DB::table('ac_user_wallets')->insert([
            [
                'user_id' => '3',
                'balance' => '2500',
                'status' => '1'
            ],
        ]);
        DB::table('ac_loan_users')->delete();
        DB::table('ac_loan_users')->insert([
            [
                'name' => 'Loan User',
                'contact' => '213456',
                'cnic' => '123456789'
            ],
        ]);
        DB::table('ac_loans')->delete();
        DB::table('ac_loans')->insert([
            [
                'name' => 'Name',
                'amount' => '2500',
                'loan_type' => '1',
                'loan_user_id' => '1',
                'status' => '3',
                'date' => '2012-12-10'
            ],
        ]);
    }
}
