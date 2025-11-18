<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $categories = config('accountflow.categories');

        DB::table('ac_categories')->delete();

        $index = 1;
        foreach ($categories as $type => $categoryGroup) {
            $flowType = ($type == 'income') ? 1 : 2;

            foreach ($categoryGroup as $parent => $children) {
                // Generate icon path for the parent category
                $parentIcon = strtolower(str_replace(' ', '_', $parent)).'.svg';

                // Insert parent category
                DB::table('ac_categories')->insert([
                    'id' => $index,
                    'type' => $flowType,
                    'name' => $parent,
                    'parent_id' => null,
                    'privacy' => 1,
                    'status' => 1,
                    'icon' => $parentIcon,
                ]);
                $parentId = $index;
                $index++;

                // Insert child categories
                foreach ($children as $child) {
                    // Generate icon path for the child category
                    $childIcon = strtolower(str_replace(' ', '_', $child)).'.svg';

                    DB::table('ac_categories')->insert([
                        'id' => $index,
                        'type' => $flowType,
                        'name' => $child,
                        'parent_id' => $parentId,
                        'privacy' => 1,
                        'status' => 1,
                        'icon' => $childIcon,
                    ]);
                    $index++;
                }
            }
        }

        // Accounts Table Seed Data
        $accounts = config('accountflow.accounts');

        DB::table('accounts')->delete();

        foreach ($accounts as $name) {
            DB::table('accounts')->insert([
                'name' => $name,
                // You can add a default opening balance if needed, e.g. 0
                'balance' => 0,
            ]);
        }

        // Payment Method Seeders

        $paymentMethods = config('accountflow.payment_methods');

        DB::table('ac_payment_methods')->delete();

        $index = 0;
        foreach ($paymentMethods as $method) {
            DB::table('ac_payment_methods')->insert([
                'name' => $method,
                'account_id' => $index + 1,
            ]);
            $index++;
        }

        DB::table('ac_settings')->delete();
        $settings = [
            // Modules and Features
            'multi_accounts_module' => 'enabled',
            'add_new_account' => true,
            'custom_category' => 'enabled',
            'cashbook_module' => 'enabled',
            'trial_balance_module' => 'enabled',
            'assets_module' => 'enabled',
            'purchase_module' => 'enabled',
            'multi_payment_methods' => 'enabled',
            'loan_module' => 'enabled',
            'user_wallet_module' => 'enabled',
            'income_form' => 'enabled',
            'equity_module' => 'enabled',
            'budgets_module' => 'enabled',
            'planned_payments_module' => 'enabled',

            'transaction_templates' => 'enabled',
            'audit_trail' => 'enabled',
            'payment_methods_module' => 'enabled',
            'categories_module' => 'enabled',
            'transfers_module' => 'enabled',
            'profit_loss_report' => 'enabled',
            'trial_balance_report' => 'enabled',

            // Default Values
            'default_transaction_type' => 2,
            'default_account_id' => 1,
            'default_payment_method_id' => 1,
            'default_sales_category_id' => 2,
            'default_expense_category_id' => 5,
            'route_prefix' => 'accounts',

            // Permissions
            'create_custom_category' => true,
            'create_multiple_transactions' => true,

        ];

        foreach ($settings as $key => $value) {
            $type = ($value === 'enabled' || $value === 'disabled') ? 1 : 2;
            DB::table('ac_settings')->insert([
                'key' => $key,
                'value' => $value,
                'type' => $type,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]);
        }

        if (config('accountflow.dummy_data_seed') === true) {

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

            // Budgets dummy data
            DB::table('ac_budgets')->delete();
            DB::table('ac_budgets')->insert([
                [
                    'account_id' => 1,
                    'category_id' => 10,
                    'amount' => 5000,
                    'period' => 'monthly',
                    'year' => 2025,
                    'month' => 7,
                    'description' => 'Monthly budget for category 10',
                    'created_by' => 1,
                ],
                [
                    'account_id' => 2,
                    'category_id' => 11,
                    'amount' => 60000,
                    'period' => 'yearly',
                    'year' => 2025,
                    'description' => 'Yearly budget for category 11',
                    'created_by' => 1,
                ],
            ]);

            // Audit trail dummy data
            DB::table('ac_audit_trail')->delete();
            DB::table('ac_audit_trail')->insert([
                [
                    'model_type' => 'Account',
                    'model_id' => 1,
                    'action' => 'created',
                    'before' => null,
                    'after' => json_encode(['name' => 'Account 1', 'balance' => 0]),
                    'user_id' => 1,
                    'created_at' => now(),
                ],
                [
                    'model_type' => 'Transaction',
                    'model_id' => 1,
                    'action' => 'updated',
                    'before' => json_encode(['amount' => 100]),
                    'after' => json_encode(['amount' => 200]),
                    'user_id' => 1,
                    'created_at' => now(),
                ],
            ]);
            DB::table('ac_assets_trx')->delete();
            DB::table('ac_assets_trx')->insert([
                [
                    'unique_id' => '1',
                    'asset_id' => '1',
                    'trx_id' => '1',
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

            DB::table('ac_user_wallets')->delete();
            DB::table('ac_user_wallets')->insert([
                [
                    'user_id' => '3',
                    'balance' => '2500',
                    'status' => '1',
                ],
            ]);
            DB::table('ac_loan_partners')->delete();
            DB::table('ac_loan_partners')->insert([
                [
                    'name' => 'Loan User',
                    'contact' => '213456',
                    'cnic' => '123456789',
                ],
            ]);
            DB::table('ac_loans')->delete();
            DB::table('ac_loans')->insert([
                [
                    'name' => 'Name',
                    'amount' => '2500',
                    'loan_type' => '1',
                    'loan_partner_id' => '1',
                    'status' => '3',
                    'date' => '2012-12-10',
                ],
            ]);
        }

    }
}
