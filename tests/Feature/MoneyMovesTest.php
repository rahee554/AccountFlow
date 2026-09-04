<?php

use ArtflowStudio\AccountFlow\Enums\LoanType;
use ArtflowStudio\AccountFlow\Exceptions\AccountFlowException;
use ArtflowStudio\AccountFlow\Models\Account;
use ArtflowStudio\AccountFlow\Models\Asset;
use ArtflowStudio\AccountFlow\Models\Category;
use ArtflowStudio\AccountFlow\Models\EquityPartner;
use ArtflowStudio\AccountFlow\Models\LoanUser;
use ArtflowStudio\AccountFlow\Models\PaymentMethod;
use ArtflowStudio\AccountFlow\Models\Setting;
use ArtflowStudio\AccountFlow\Services\AssetService;
use ArtflowStudio\AccountFlow\Services\EquityService;
use ArtflowStudio\AccountFlow\Services\LoanService;

/*
 * Every feature in this file recorded something and left the money where it
 * was. For an end user that is the worst possible failure: the screen says
 * "saved" and no balance changes.
 */

beforeEach(function () {
    $this->account = Account::factory()->withOpeningBalance(100000)->create();
    $this->method = PaymentMethod::factory()->create(['account_id' => $this->account->id]);
    $this->expense = Category::factory()->expense()->create();
    $this->income = Category::factory()->income()->create();

    Setting::put('default_account_id', $this->account->id);
    Setting::put('default_payment_method_id', $this->method->id);
    Setting::put('default_expense_category_id', $this->expense->id);
    Setting::put('default_sales_category_id', $this->income->id);
});

it('takes money out of the account when an asset is bought', function () {
    // The screen called `new Transaction(...)->save()` directly, bypassing the
    // only write path that moves a balance.
    $asset = Asset::create([
        'name' => 'Laptop',
        'category_id' => $this->expense->id,
        'value' => 80000,
        'status' => 1,
        'acquisition_date' => now()->toDateString(),
    ]);

    app(AssetService::class)->purchase($asset->id, 80000, ['account_id' => $this->account->id]);

    expect((float) $this->account->fresh()->balance)->toBe(20000.0)
        ->and(app(AssetService::class)->netCost($asset->id))->toBe(80000.0);
});

it('puts money back when an asset is sold', function () {
    $asset = Asset::create([
        'name' => 'Van', 'category_id' => $this->income->id, 'value' => 50000,
        'status' => 1, 'acquisition_date' => now()->toDateString(),
    ]);

    app(AssetService::class)->sell($asset->id, 45000, ['account_id' => $this->account->id]);

    expect((float) $this->account->fresh()->balance)->toBe(145000.0)
        ->and((int) $asset->fresh()->status)->toBe(3);
});

it('brings cash in when a loan is received', function () {
    // CreateLoan wrote a row to ac_loans and stopped — receiving 500,000 left
    // every balance untouched.
    $partner = LoanUser::create(['name' => 'Bank', 'contact' => '123', 'cnic' => '456']);

    $loan = app(LoanService::class)->borrow(500000, $partner->id, [
        'name' => 'Bank loan',
        'account_id' => $this->account->id,
        'category_id' => $this->income->id,
    ]);

    expect((float) $this->account->fresh()->balance)->toBe(600000.0)
        ->and($loan->type())->toBe(LoanType::Borrowed)
        ->and(app(LoanService::class)->outstanding($loan))->toBe(500000.0);
});

it('sends cash out when money is lent', function () {
    $partner = LoanUser::create(['name' => 'Ali', 'contact' => '123', 'cnic' => '456']);

    app(LoanService::class)->lend(30000, $partner->id, [
        'account_id' => $this->account->id,
        'category_id' => $this->expense->id,
    ]);

    expect((float) $this->account->fresh()->balance)->toBe(70000.0);
});

it('tracks a loan down to settled as it is repaid', function () {
    $partner = LoanUser::create(['name' => 'Bank', 'contact' => '1', 'cnic' => '2']);
    $loans = app(LoanService::class);

    $loan = $loans->borrow(1000, $partner->id, [
        'account_id' => $this->account->id,
        'category_id' => $this->income->id,
    ]);

    $loans->repay($loan, 400, ['account_id' => $this->account->id, 'category_id' => $this->expense->id]);

    expect($loans->outstanding($loan->fresh()))->toBe(600.0)
        ->and((int) $loan->fresh()->status)->toBe(2)  // partially returned
        ->and((float) $this->account->fresh()->balance)->toBe(100600.0);

    $loans->repay($loan->fresh(), 600, ['account_id' => $this->account->id, 'category_id' => $this->expense->id]);

    expect($loans->outstanding($loan->fresh()))->toBe(0.0)
        ->and((int) $loan->fresh()->status)->toBe(1)  // returned
        ->and($loans->summary($loan->fresh())['is_settled'])->toBeTrue();
});

it('refuses to repay more than is outstanding', function () {
    $partner = LoanUser::create(['name' => 'Bank', 'contact' => '1', 'cnic' => '2']);
    $loans = app(LoanService::class);

    $loan = $loans->borrow(1000, $partner->id, [
        'account_id' => $this->account->id, 'category_id' => $this->income->id,
    ]);

    $loans->repay($loan, 5000, ['account_id' => $this->account->id, 'category_id' => $this->expense->id]);
})->throws(AccountFlowException::class);

it('brings cash in when a partner contributes capital', function () {
    // There was no save method on the equity screen at all — a partner could
    // invest 200,000 and nothing happened anywhere.
    $partner = EquityPartner::create(['name' => 'Sara', 'is_active' => true, 'current_equity' => 0]);

    app(EquityService::class)->contribute($partner->id, 200000, [
        'account_id' => $this->account->id,
        'category_id' => $this->income->id,
    ]);

    expect((float) $this->account->fresh()->balance)->toBe(300000.0)
        ->and((float) $partner->fresh()->current_equity)->toBe(200000.0);
});

it('sends cash out when a partner withdraws', function () {
    $partner = EquityPartner::create(['name' => 'Sara', 'is_active' => true, 'current_equity' => 0]);
    $equity = app(EquityService::class);

    $equity->contribute($partner->id, 100000, ['account_id' => $this->account->id, 'category_id' => $this->income->id]);
    $equity->withdraw($partner->id, 40000, ['account_id' => $this->account->id, 'category_id' => $this->expense->id]);

    expect((float) $this->account->fresh()->balance)->toBe(160000.0)
        ->and((float) $partner->fresh()->current_equity)->toBe(60000.0);
});

it('allocates a profit share without moving cash', function () {
    $partner = EquityPartner::create(['name' => 'Sara', 'is_active' => true, 'current_equity' => 0]);

    app(EquityService::class)->shareProfit($partner->id, 30000);

    // Equity moves; the bank balance does not.
    expect((float) $partner->fresh()->current_equity)->toBe(30000.0)
        ->and((float) $this->account->fresh()->balance)->toBe(100000.0);
});

it('nets profit and loss shares against each other', function () {
    $partner = EquityPartner::create(['name' => 'Sara', 'is_active' => true, 'current_equity' => 0]);
    $equity = app(EquityService::class);

    $equity->shareProfit($partner->id, 50000);
    $equity->shareLoss($partner->id, 20000);

    expect($equity->recalculateEquity($partner->id))->toBe(30000.0);
});

it('unwinds the cash when an equity movement is deleted', function () {
    $partner = EquityPartner::create(['name' => 'Sara', 'is_active' => true, 'current_equity' => 0]);
    $equity = app(EquityService::class);

    $movement = $equity->contribute($partner->id, 75000, [
        'account_id' => $this->account->id, 'category_id' => $this->income->id,
    ]);

    $equity->delete($movement);

    expect((float) $this->account->fresh()->balance)->toBe(100000.0)
        ->and((float) $partner->fresh()->current_equity)->toBe(0.0);
});
