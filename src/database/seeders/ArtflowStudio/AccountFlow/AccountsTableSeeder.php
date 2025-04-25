<?php

namespace Database\Seeders\ArtflowStudio\AccountFlow;

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
        $categories = config('accountflow.categories');

        DB::table('ac_categories')->delete();

        $index = 1;
        foreach ($categories as $type => $categoryGroup) {
            $flowType = ($type == 'income') ? 1 : 2;

            foreach ($categoryGroup as $parent => $children) {
                // Generate icon path for the parent category
                $parentIcon = strtolower(str_replace(' ', '_', $parent)) . '.svg';

                // Insert parent category
                DB::table('ac_categories')->insert([
                    'id' => $index,
                    'flow_type' => $flowType,
                    'name' => $parent,
                    'parent_id' => NULL,
                    'privacy' => 1,
                    'status' => 1,
                    'icon' => $parentIcon,
                ]);
                $parentId = $index;
                $index++;

                // Insert child categories
                foreach ($children as $child) {
                    // Generate icon path for the child category
                    $childIcon = strtolower(str_replace(' ', '_', $child)) . '.svg';

                    DB::table('ac_categories')->insert([
                        'id' => $index,
                        'flow_type' => $flowType,
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

        foreach ($paymentMethods as $method) {
            DB::table('ac_payment_methods')->insert([
                'name' => $method,
            ]);
        }

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


        // DB::table('ac_transactions')->delete();
        // DB::table('ac_transactions')->insert([
        //     [
        //         'unique_id' => '556488',
        //         'account_id' => '1',
        //         'category_id' => '10',
        //         'amount' => '12345',
        //     ],
        // ]);

        // DB::table('ac_assets')->delete();
        // DB::table('ac_assets')->insert([
        //     [
        //         'name' => 'Dummy Asset Name',
        //         'description' => 'This is Asset Description',
        //         'value' => '12345',
        //         'category_id' => '16',
        //         'status' => '1',
        //         'acquisition_date' => '2020-12-10',
        //     ],
        // ]);
        // DB::table('ac_assets_trx')->delete();
        // DB::table('ac_assets_trx')->insert([
        //     [
        //         'unique_id' => '1',
        //         'asset_id' => '1',
        //         'trx_id' => '1'
        //     ],
        // ]);

        // DB::table('ac_transfers')->delete();
        // DB::table('ac_transfers')->insert([
        //     [
        //         'unique_id' => '546132',
        //         'from_account' => '2',
        //         'to_account' => '3',
        //         'description' => 'this is description',
        //         'date' => '2023-12-10',
        //         'created_by' => '1',
        //     ],
        // ]);
       


        // DB::table('ac_user_wallets')->delete();
        // DB::table('ac_user_wallets')->insert([
        //     [
        //         'user_id' => '1',
        //         'balance' => '2500',
        //         'status' => '1'
        //     ],
        // ]);
        // DB::table('ac_loan_users')->delete();
        // DB::table('ac_loan_users')->insert([
        //     [
        //         'name' => 'Loan User',
        //         'contact' => '213456',
        //         'cnic' => '123456789'
        //     ],
        // ]);
        // DB::table('ac_loans')->delete();
        // DB::table('ac_loans')->insert([
        //     [
        //         'name' => 'Name',
        //         'amount' => '2500',
        //         'loan_type' => '1',
        //         'loan_user_id' => '1',
        //         'status' => '3',
        //         'date' => '2012-12-10'
        //     ],
        // ]);
    }
}
