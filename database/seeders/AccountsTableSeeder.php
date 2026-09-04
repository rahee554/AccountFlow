<?php

namespace ArtflowStudio\AccountFlow\Database\Seeders;

use ArtflowStudio\AccountFlow\Enums\CategoryType;
use ArtflowStudio\AccountFlow\Enums\SettingType;
use ArtflowStudio\AccountFlow\Models\Account;
use ArtflowStudio\AccountFlow\Models\Category;
use ArtflowStudio\AccountFlow\Models\PaymentMethod;
use ArtflowStudio\AccountFlow\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Seeds the default chart of accounts, categories, payment methods and settings.
 *
 * This seeder is idempotent and never deletes. The 0.2.x version began with
 * `DB::table('ac_categories')->delete()` and inserted rows at hardcoded ids —
 * so running it on an existing install destroyed the categories that live
 * transactions referenced by foreign key, and (because AUTO_INCREMENT was never
 * reset) left payment methods pointing at accounts that no longer existed. Its
 * `dummy_data_seed` branch additionally deleted `ac_transactions`,
 * `ac_transfers`, `ac_loans` and `ac_user_wallets` before inserting fixtures;
 * that branch is gone — test data belongs in factories.
 *
 * Existing rows are matched by natural key and left alone. Settings the user
 * has already chosen are preserved unless `$overwriteSettings` is set.
 */
class AccountsTableSeeder extends Seeder
{
    /**
     * Replace settings that already exist. False by default so an operator's
     * choices survive re-seeding.
     */
    public bool $overwriteSettings = false;

    public function run(): void
    {
        $this->seedCategories();
        $this->seedAccounts();
        $this->seedPaymentMethods();
        $this->seedSettings();

        Setting::flushCache();
    }

    private function seedCategories(): void
    {
        /** @var array<string,array<string,list<string>>> $groups */
        $groups = config('accountflow.categories', []);

        foreach ($groups as $type => $parents) {
            $categoryType = $type === 'income' ? CategoryType::Income : CategoryType::Expense;

            foreach ($parents as $parentName => $children) {
                $parent = Category::firstOrCreate(
                    [
                        'name' => $parentName,
                        'type' => $categoryType->value,
                        'parent_id' => null,
                    ],
                    [
                        'icon' => $this->icon($parentName),
                        'privacy' => 1,
                        'status' => 1,
                    ],
                );

                foreach ($children as $childName) {
                    Category::firstOrCreate(
                        [
                            'name' => $childName,
                            'type' => $categoryType->value,
                            'parent_id' => $parent->id,
                        ],
                        [
                            'icon' => $this->icon($childName),
                            'privacy' => 1,
                            'status' => 1,
                        ],
                    );
                }
            }
        }
    }

    private function seedAccounts(): void
    {
        foreach (config('accountflow.accounts', []) as $name) {
            Account::firstOrCreate(
                ['name' => $name],
                [
                    'opening_balance' => 0,
                    'balance' => 0,
                    'active' => true,
                ],
            );
        }
    }

    /**
     * Payment methods are paired with accounts positionally, but against the
     * accounts that actually exist rather than assuming ids 1, 2, 3.
     */
    private function seedPaymentMethods(): void
    {
        $accounts = Account::orderBy('id')->pluck('id')->all();

        foreach (array_values(config('accountflow.payment_methods', [])) as $index => $name) {
            PaymentMethod::firstOrCreate(
                ['name' => $name],
                [
                    'account_id' => $accounts[$index] ?? $accounts[0] ?? null,
                    'status' => 1,
                ],
            );
        }
    }

    private function seedSettings(): void
    {
        $defaults = Setting::defaults() + [
            'create_custom_category' => 'enabled',
            'create_multiple_transactions' => 'enabled',
        ];

        $existing = Setting::query()->pluck('key')->all();

        foreach ($defaults as $key => $value) {
            if (in_array($key, $existing, true) && ! $this->overwriteSettings) {
                continue;
            }

            $type = in_array($value, ['enabled', 'disabled'], true)
                ? SettingType::Feature
                : SettingType::Value;

            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => (string) $value, 'type' => $type->value],
            );
        }
    }

    private function icon(string $name): string
    {
        return strtolower(str_replace(' ', '_', $name)).'.svg';
    }
}
