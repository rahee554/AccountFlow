<?php

use ArtflowStudio\AccountFlow\Enums\ScheduleType;
use ArtflowStudio\AccountFlow\Models\Account;
use ArtflowStudio\AccountFlow\Models\Category;
use ArtflowStudio\AccountFlow\Models\PaymentMethod;
use ArtflowStudio\AccountFlow\Models\PlannedPayment;
use ArtflowStudio\AccountFlow\Models\Setting;
use ArtflowStudio\AccountFlow\Models\Transaction;
use ArtflowStudio\AccountFlow\Services\PlannedPaymentService;

beforeEach(function () {
    $this->account = Account::factory()->withOpeningBalance(10000)->create();
    $this->method = PaymentMethod::factory()->create(['account_id' => $this->account->id]);
    $this->category = Category::factory()->expense()->create();

    Setting::put('default_account_id', $this->account->id);
    Setting::put('default_payment_method_id', $this->method->id);
    Setting::put('default_expense_category_id', $this->category->id);

    $this->payments = app(PlannedPaymentService::class);
});

function plan(array $overrides = []): PlannedPayment
{
    return PlannedPayment::create(array_merge([
        'name' => 'Office rent',
        'category_id' => test()->category->id,
        'amount' => 1200,
        'start_date' => now()->toDateString(),
        'schedule_type' => ScheduleType::Monthly->value,
        'auto_post' => true,
        'next_run_date' => now()->toDateString(),
    ], $overrides));
}

it('can be created at all', function () {
    // $fillable listed due_date / period / auto_post_date / recurring, none of
    // which are columns, so every create() failed with "Unknown column".
    $payment = plan();

    expect($payment->exists)->toBeTrue()
        ->and($payment->start_date)->not->toBeNull()
        ->and($payment->auto_post)->toBeTrue();
});

it('posts a transaction when due', function () {
    // Nothing in 0.2.x ever ran the schedule — a planned payment sat in the
    // table forever and never became a transaction.
    $payment = plan();

    $result = $this->payments->postDue();

    expect($result['posted'])->toBe(1)
        ->and(Transaction::count())->toBe(1)
        ->and((float) Transaction::first()->amount)->toBe(1200.0)
        ->and((float) $this->account->fresh()->balance)->toBe(8800.0);
});

it('advances the schedule after posting', function () {
    $payment = plan(['start_date' => now()->toDateString(), 'next_run_date' => now()->toDateString()]);

    $this->payments->post($payment);
    $payment->refresh();

    expect($payment->last_run_date->toDateString())->toBe(now()->toDateString())
        ->and($payment->next_run_date->toDateString())->toBe(now()->addMonthNoOverflow()->toDateString());
});

it('does not post twice for the same due date', function () {
    $payment = plan();

    $this->payments->post($payment);
    $second = $this->payments->post($payment->fresh());

    expect($second)->toBeNull()
        ->and(Transaction::count())->toBe(1);
});

it('stops posting a one-off payment after it runs', function () {
    $payment = plan(['schedule_type' => ScheduleType::Once->value]);

    $this->payments->post($payment);
    $payment->refresh();

    expect($payment->auto_post)->toBeFalse()
        ->and($payment->next_run_date)->toBeNull()
        ->and($this->payments->due()->count())->toBe(0);
});

it('ignores payments that are not due yet', function () {
    plan(['next_run_date' => now()->addMonth()->toDateString(), 'start_date' => now()->addMonth()->toDateString()]);

    expect($this->payments->due()->count())->toBe(0)
        ->and($this->payments->postDue()['posted'])->toBe(0);
});

it('ignores payments past their end date', function () {
    plan([
        'start_date' => now()->subYear()->toDateString(),
        'end_date' => now()->subMonth()->toDateString(),
        'next_run_date' => now()->subMonth()->toDateString(),
    ]);

    expect($this->payments->due()->count())->toBe(0);
});

it('ignores payments with auto_post off', function () {
    plan(['auto_post' => false]);

    expect($this->payments->due()->count())->toBe(0);
});

it('is driven by an artisan command', function () {
    plan();

    $this->artisan('accountflow:post-planned-payments')->assertSuccessful();

    expect(Transaction::count())->toBe(1);
});

it('reports without writing on a dry run', function () {
    plan();

    $this->artisan('accountflow:post-planned-payments --dry-run')->assertSuccessful();

    expect(Transaction::count())->toBe(0);
});
