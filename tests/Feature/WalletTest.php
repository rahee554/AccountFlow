<?php

use ArtflowStudio\AccountFlow\Exceptions\AccountFlowException;
use ArtflowStudio\AccountFlow\Models\Account;
use ArtflowStudio\AccountFlow\Models\Category;
use ArtflowStudio\AccountFlow\Models\PaymentMethod;
use ArtflowStudio\AccountFlow\Models\Setting;
use ArtflowStudio\AccountFlow\Models\UserTransfer;
use ArtflowStudio\AccountFlow\Services\WalletService;
use Illuminate\Support\Facades\DB;

/*
 * The wallet module was entirely inert: ac_user_transfers had no model, the
 * create screen had no save method, and nothing ever touched
 * ac_user_wallets.balance.
 */

beforeEach(function () {
    $this->account = Account::factory()->withOpeningBalance(100000)->create(['name' => 'Cash Account']);
    $this->method = PaymentMethod::factory()->create(['account_id' => $this->account->id]);
    $income = Category::factory()->income()->create();
    $expense = Category::factory()->expense()->create();

    Setting::put('default_account_id', $this->account->id);
    Setting::put('default_payment_method_id', $this->method->id);
    Setting::put('default_sales_category_id', $income->id);
    Setting::put('default_expense_category_id', $expense->id);

    $this->alice = DB::table('users')->insertGetId([
        'name' => 'Alice', 'email' => 'alice@example.test', 'password' => 'x',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $this->bob = DB::table('users')->insertGetId([
        'name' => 'Bob', 'email' => 'bob@example.test', 'password' => 'x',
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $this->wallets = app(WalletService::class);
});

it('creates a wallet the first time a person is used', function () {
    expect($this->wallets->balance($this->alice))->toBe(0.0);
});

it('moves cash out of the business when a wallet is topped up', function () {
    $this->wallets->topUp($this->alice, 5000, account: 'Cash Account');

    expect($this->wallets->balance($this->alice))->toBe(5000.0)
        // The money genuinely left the business account.
        ->and((float) $this->account->fresh()->balance)->toBe(95000.0);
});

it('brings cash back when a wallet is settled', function () {
    $this->wallets->topUp($this->alice, 5000, account: 'Cash Account');
    $this->wallets->settle($this->alice, 2000, account: 'Cash Account');

    expect($this->wallets->balance($this->alice))->toBe(3000.0)
        ->and((float) $this->account->fresh()->balance)->toBe(97000.0);
});

it('moves money between two people without touching the business balance', function () {
    $this->wallets->topUp($this->alice, 5000, account: 'Cash Account');
    $businessBefore = (float) $this->account->fresh()->balance;

    $this->wallets->transfer($this->alice, $this->bob, 1500);

    expect($this->wallets->balance($this->alice))->toBe(3500.0)
        ->and($this->wallets->balance($this->bob))->toBe(1500.0)
        // The business still holds the same total, so nothing posted.
        ->and((float) $this->account->fresh()->balance)->toBe($businessBefore);
});

it('records the transfer so it can be listed', function () {
    // ac_user_transfers had no model at all, so nothing could read or write it.
    $this->wallets->topUp($this->alice, 5000, account: 'Cash Account');
    $this->wallets->transfer($this->alice, $this->bob, 1500);

    $transfer = UserTransfer::first();

    expect(UserTransfer::count())->toBe(1)
        ->and((float) $transfer->amount)->toBe(1500.0)
        ->and((int) $transfer->from)->toBe($this->alice)
        ->and((int) $transfer->to)->toBe($this->bob);
});

it('refuses to send more than a wallet holds', function () {
    $this->wallets->topUp($this->alice, 100, account: 'Cash Account');

    $this->wallets->transfer($this->alice, $this->bob, 5000);
})->throws(AccountFlowException::class);

it('refuses to settle more than a wallet holds', function () {
    $this->wallets->settle($this->alice, 500, account: 'Cash Account');
})->throws(AccountFlowException::class);

it('refuses a transfer to the same person', function () {
    $this->wallets->transfer($this->alice, $this->alice, 100);
})->throws(AccountFlowException::class);

it('refuses to use a frozen wallet', function () {
    $this->wallets->freeze($this->alice);

    $this->wallets->topUp($this->alice, 100, account: 'Cash Account');
})->throws(AccountFlowException::class);

it('can be unfrozen again', function () {
    $this->wallets->freeze($this->alice);
    $this->wallets->unfreeze($this->alice);

    $this->wallets->topUp($this->alice, 100, account: 'Cash Account');

    expect($this->wallets->balance($this->alice))->toBe(100.0);
});

it('reports what the business has handed out and not got back', function () {
    $this->wallets->topUp($this->alice, 5000, account: 'Cash Account');
    $this->wallets->topUp($this->bob, 3000, account: 'Cash Account');

    expect($this->wallets->totalOutstanding())->toBe(8000.0);
});

it('summarises one person in plain words', function () {
    $this->wallets->topUp($this->alice, 5000, account: 'Cash Account');
    $this->wallets->transfer($this->alice, $this->bob, 1000);

    expect($this->wallets->summary($this->alice))->toBe([
        'balance' => 4000.0,
        'received' => 0.0,
        'sent' => 1000.0,
        'status' => 'active',
    ]);
});

it('refuses a non-positive amount', function () {
    $this->wallets->topUp($this->alice, 0, account: 'Cash Account');
})->throws(AccountFlowException::class);
