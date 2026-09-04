<?php

use ArtflowStudio\AccountFlow\Database\Seeders\AccountsTableSeeder;
use ArtflowStudio\AccountFlow\Models\Account;
use ArtflowStudio\AccountFlow\Models\Category;
use ArtflowStudio\AccountFlow\Models\PaymentMethod;
use ArtflowStudio\AccountFlow\Models\Setting;
use ArtflowStudio\AccountFlow\Models\Transaction;

it('is idempotent — running twice changes nothing', function () {
    $seeder = new AccountsTableSeeder;

    $seeder->run();
    $counts = [Account::count(), Category::count(), PaymentMethod::count()];

    $seeder->run();

    expect([Account::count(), Category::count(), PaymentMethod::count()])->toBe($counts);
});

it('never deletes categories that transactions reference', function () {
    $seeder = new AccountsTableSeeder;
    $seeder->run();

    $account = Account::first();
    $category = Category::whereNotNull('parent_id')->first();

    $transaction = Transaction::factory()->create([
        'account_id' => $account->id,
        'category_id' => $category->id,
        'payment_method' => PaymentMethod::first()->id,
    ]);

    // 0.2.x began with DB::table('ac_categories')->delete(), destroying the
    // chart of accounts that live transactions pointed at.
    $seeder->run();

    expect(Category::whereKey($category->id)->exists())->toBeTrue()
        ->and(Transaction::whereKey($transaction->id)->exists())->toBeTrue();
});

it('preserves settings that have already been changed', function () {
    $seeder = new AccountsTableSeeder;
    $seeder->run();

    Setting::put('default_account_id', 42);

    $seeder->run();

    expect(Setting::defaultAccountId())->toBe(42);
});

it('points payment methods at accounts that actually exist', function () {
    (new AccountsTableSeeder)->run();

    foreach (PaymentMethod::all() as $method) {
        expect(Account::whereKey($method->account_id)->exists())->toBeTrue();
    }
});
