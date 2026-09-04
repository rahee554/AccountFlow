<?php

use ArtflowStudio\AccountFlow\Models\Account;
use ArtflowStudio\AccountFlow\Models\Category;
use ArtflowStudio\AccountFlow\Models\PaymentMethod;
use ArtflowStudio\AccountFlow\Models\Transaction;
use ArtflowStudio\AccountFlow\Services\ReportService;

beforeEach(function () {
    $this->account = Account::factory()->withOpeningBalance(0)->create();
    $this->method = PaymentMethod::factory()->create(['account_id' => $this->account->id]);
    $this->incomeCategory = Category::factory()->income()->create();
    $this->expenseCategory = Category::factory()->expense()->create();
    $this->reports = app(ReportService::class);
});

it('totals income and expense correctly', function () {
    Transaction::factory()->income()->create([
        'account_id' => $this->account->id,
        'category_id' => $this->incomeCategory->id,
        'payment_method' => $this->method->id,
        'amount' => 1000,
    ]);
    Transaction::factory()->expense()->create([
        'account_id' => $this->account->id,
        'category_id' => $this->expenseCategory->id,
        'payment_method' => $this->method->id,
        'amount' => 400,
    ]);

    $report = $this->reports->incomeExpenseReport();

    expect($report['summary']['total_income'])->toBe(1000.0)
        ->and($report['summary']['total_expense'])->toBe(400.0)
        ->and($report['summary']['net_income'])->toBe(600.0);
});

it('reports a transaction with no payment method instead of crashing', function () {
    // `payment_method` is nullable, and 0.2.x dereferenced
    // $group->first()->paymentMethod->id — one such row fataled the report.
    Transaction::factory()->withoutPaymentMethod()->create([
        'account_id' => $this->account->id,
        'category_id' => $this->expenseCategory->id,
        'amount' => 75,
    ]);

    $report = $this->reports->byPaymentMethod();

    expect($report['by_payment_method'])->toHaveCount(1)
        ->and($report['by_payment_method'][0]['payment_method_name'])->toBe('Unassigned');
});

it('computes a profit margin without dividing by zero', function () {
    $report = $this->reports->profitAndLoss();

    expect($report['profit_margin'])->toBe(0.0);
});

it('groups cash flow by month with driver-portable SQL', function () {
    Transaction::factory()->income()->create([
        'account_id' => $this->account->id,
        'category_id' => $this->incomeCategory->id,
        'payment_method' => $this->method->id,
        'amount' => 500,
        'date' => now()->toDateString(),
    ]);

    $report = $this->reports->cashFlowReport();

    expect($report['by_month'])->toHaveCount(1)
        ->and($report['by_month'][0]['inflows'])->toBe(500.0)
        ->and($report['by_month'][0]['month'])->toBe(now()->format('Y-m'));
});

it('builds a running-balance ledger', function () {
    foreach ([100, 200, 50] as $i => $amount) {
        Transaction::factory()->income()->create([
            'account_id' => $this->account->id,
            'category_id' => $this->incomeCategory->id,
            'payment_method' => $this->method->id,
            'amount' => $amount,
            'date' => now()->subDays(3 - $i)->toDateString(),
        ]);
    }

    $ledger = $this->reports->ledger($this->account->id);

    expect($ledger['entries'])->toHaveCount(3)
        ->and($ledger['closing_balance'])->toBe(350.0)
        ->and(array_column($ledger['entries'], 'balance'))->toBe([100.0, 300.0, 350.0]);
});
