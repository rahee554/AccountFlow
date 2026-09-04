<?php

use ArtflowStudio\AccountFlow\Exceptions\AccountFlowException;
use ArtflowStudio\AccountFlow\Models\Account;
use ArtflowStudio\AccountFlow\Models\Category;
use ArtflowStudio\AccountFlow\Models\PaymentMethod;
use ArtflowStudio\AccountFlow\Models\Setting;
use ArtflowStudio\AccountFlow\Services\MoneyService;

/*
 * The plain-language API. A user records "money in" and "money out"; the
 * package works out accounts, categories and ledger entries behind them.
 */

beforeEach(function () {
    $this->cash = Account::factory()->withOpeningBalance(10000)->create(['name' => 'Cash Account']);
    $this->bank = Account::factory()->withOpeningBalance(50000)->create(['name' => 'Bank Account']);
    $this->method = PaymentMethod::factory()->create(['name' => 'Cash Payment', 'account_id' => $this->cash->id]);

    Category::factory()->income()->create(['name' => 'Income', 'parent_id' => null]);
    Category::factory()->expense()->create(['name' => 'Regular Expense', 'parent_id' => null]);

    Setting::put('default_account_id', $this->cash->id);
    Setting::put('default_payment_method_id', $this->method->id);
    Setting::put('default_sales_category_id', Category::where('type', 1)->value('id'));
    Setting::put('default_expense_category_id', Category::where('type', 2)->value('id'));

    $this->money = app(MoneyService::class);
});

it('records money in with nothing but an amount', function () {
    $this->money->received(1500, 'Sale to Ali');

    expect($this->money->balance('Cash Account'))->toBe(11500.0);
});

it('records money out with nothing but an amount', function () {
    $this->money->spent(250, 'Fuel');

    expect($this->money->balance('Cash Account'))->toBe(9750.0);
});

it('takes account and category by name, not id', function () {
    // A user knows "Bank Account", not id 2.
    $this->money->spent(900, 'Office rent', category: 'Rentals', account: 'Bank Account');

    expect($this->money->balance('Bank Account'))->toBe(49100.0);
});

it('creates a category the first time a new name is used', function () {
    expect(Category::where('name', 'Transport')->exists())->toBeFalse();

    $this->money->spent(300, 'Taxi', category: 'Transport');

    $created = Category::where('name', 'Transport')->first();

    expect($created)->not->toBeNull()
        ->and((int) $created->type)->toBe(2)          // expense side
        ->and($created->parent_id)->not->toBeNull();  // filed under the expense parent
});

it('reuses a category rather than creating duplicates', function () {
    $this->money->spent(100, 'Taxi', category: 'Transport');
    $this->money->spent(200, 'Bus', category: 'Transport');

    expect(Category::where('name', 'Transport')->count())->toBe(1);
});

it('falls back to the default category when auto-create is off', function () {
    config()->set('accountflow.auto_create_categories', false);

    $transaction = $this->money->spent(100, 'Taxi', category: 'Something New');

    expect(Category::where('name', 'Something New')->exists())->toBeFalse()
        ->and((int) $transaction->category_id)->toBe(Setting::defaultExpenseCategoryId());
});

it('moves money between accounts by name', function () {
    $this->money->moved(2000, from: 'Cash Account', to: 'Bank Account');

    expect($this->money->balance('Cash Account'))->toBe(8000.0)
        ->and($this->money->balance('Bank Account'))->toBe(52000.0);
});

it('says which accounts exist when a name is wrong', function () {
    // The message has to be useful to someone who is not a developer.
    try {
        $this->money->moved(100, from: 'Petty Cash', to: 'Bank Account');
        $this->fail('Expected an AccountFlowException.');
    } catch (AccountFlowException $e) {
        expect($e->getMessage())->toContain('There is no account called [Petty Cash]')
            ->and($e->getMessage())->toContain('Cash Account');
    }
});

it('reports every balance at once', function () {
    expect($this->money->balance())->toBe([
        'Bank Account' => 50000.0,
        'Cash Account' => 10000.0,
    ])->and($this->money->total())->toBe(60000.0);
});

it('summarises a period in plain words', function () {
    $this->money->received(5000, 'Sale');
    $this->money->spent(1200, 'Supplies');

    expect($this->money->summary())->toBe([
        'received' => 5000.0,
        'spent' => 1200.0,
        'difference' => 3800.0,
        'entries' => 2,
    ]);
});

it('leaves transfers out of the in/out summary', function () {
    $this->money->received(5000, 'Sale');
    $this->money->moved(1000, from: 'Cash Account', to: 'Bank Account');

    // Moving your own money is not income or spending.
    expect($this->money->summary()['received'])->toBe(5000.0)
        ->and($this->money->summary()['spent'])->toBe(0.0);
});

it('undoes an entry without deleting the history', function () {
    $transaction = $this->money->received(1500, 'Sale to Ali');

    $this->money->undo($transaction, 'Customer cancelled');

    expect($this->money->balance('Cash Account'))->toBe(10000.0)
        ->and($transaction->fresh()->isReversed())->toBeTrue()
        ->and($this->money->summary()['received'])->toBe(0.0);
});

it('refuses an amount of zero', function () {
    $this->money->spent(0, 'Nothing');
})->throws(AccountFlowException::class);
