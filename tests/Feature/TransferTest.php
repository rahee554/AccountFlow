<?php

use ArtflowStudio\AccountFlow\Exceptions\AccountFlowException;
use ArtflowStudio\AccountFlow\Models\Account;
use ArtflowStudio\AccountFlow\Models\Category;
use ArtflowStudio\AccountFlow\Models\PaymentMethod;
use ArtflowStudio\AccountFlow\Models\Transaction;
use ArtflowStudio\AccountFlow\Models\Transfer;
use ArtflowStudio\AccountFlow\Services\AccountService;
use ArtflowStudio\AccountFlow\Services\ReportService;
use ArtflowStudio\AccountFlow\Services\TransferService;

beforeEach(function () {
    $this->from = Account::factory()->withOpeningBalance(1000)->create();
    $this->to = Account::factory()->withOpeningBalance(0)->create();
    $this->transfers = app(TransferService::class);
});

it('actually moves money between accounts', function () {
    // 0.2.x wrote a row to ac_transfers and nothing else. Balances come from
    // ac_transactions, so the money never moved.
    $this->transfers->create([
        'amount' => 250,
        'from_account' => $this->from->id,
        'to_account' => $this->to->id,
    ]);

    expect((float) $this->from->fresh()->balance)->toBe(750.0)
        ->and((float) $this->to->fresh()->balance)->toBe(250.0);
});

it('posts a linked ledger entry on each side', function () {
    $transfer = $this->transfers->create([
        'amount' => 250,
        'from_account' => $this->from->id,
        'to_account' => $this->to->id,
    ]);

    expect($transfer->isPosted())->toBeTrue()
        ->and(Transaction::where('transfer_id', $transfer->id)->count())->toBe(2)
        ->and($transfer->fromTransaction->isExpense())->toBeTrue()
        ->and($transfer->toTransaction->isIncome())->toBeTrue();
});

it('survives a balance rebuild from the ledger', function () {
    // The transfer screen used to call recalculateAll() straight after writing
    // the row, which rebuilt balances from a ledger that had no record of it.
    $this->transfers->create([
        'amount' => 250,
        'from_account' => $this->from->id,
        'to_account' => $this->to->id,
    ]);

    app(AccountService::class)->recalculateAll();

    expect((float) $this->from->fresh()->balance)->toBe(750.0)
        ->and((float) $this->to->fresh()->balance)->toBe(250.0);
});

it('keeps transfers out of profit and loss', function () {
    $method = PaymentMethod::factory()->create(['account_id' => $this->from->id]);
    $income = Category::factory()->income()->create();

    Transaction::factory()->income()->create([
        'account_id' => $this->from->id,
        'category_id' => $income->id,
        'payment_method' => $method->id,
        'amount' => 500,
    ]);

    $this->transfers->create([
        'amount' => 250,
        'from_account' => $this->from->id,
        'to_account' => $this->to->id,
    ]);

    $report = app(ReportService::class)->profitAndLoss();

    // Moving your own cash is neither revenue nor a cost.
    expect($report['revenue'])->toBe(500.0)
        ->and($report['expenses'])->toBe(0.0)
        ->and($report['profit'])->toBe(500.0);
});

it('refuses to overdraw the source account', function () {
    $this->transfers->create([
        'amount' => 5000,
        'from_account' => $this->from->id,
        'to_account' => $this->to->id,
    ]);
})->throws(AccountFlowException::class);

it('allows overdrawing when configured to', function () {
    config()->set('accountflow.allow_negative_balance', true);

    $this->transfers->create([
        'amount' => 5000,
        'from_account' => $this->from->id,
        'to_account' => $this->to->id,
    ]);

    expect((float) $this->from->fresh()->balance)->toBe(-4000.0);
});

it('refuses a transfer to the same account', function () {
    $this->transfers->create([
        'amount' => 100,
        'from_account' => $this->from->id,
        'to_account' => $this->from->id,
    ]);
})->throws(AccountFlowException::class);

it('unwinds both balances on delete', function () {
    $transfer = $this->transfers->create([
        'amount' => 250,
        'from_account' => $this->from->id,
        'to_account' => $this->to->id,
    ]);

    $this->transfers->delete($transfer);

    expect((float) $this->from->fresh()->balance)->toBe(1000.0)
        ->and((float) $this->to->fresh()->balance)->toBe(0.0)
        ->and(Transaction::where('transfer_id', $transfer->id)->count())->toBe(0);
});

it('reposts both legs when the amount changes', function () {
    $transfer = $this->transfers->create([
        'amount' => 250,
        'from_account' => $this->from->id,
        'to_account' => $this->to->id,
    ]);

    $this->transfers->update($transfer, ['amount' => 400]);

    expect((float) $this->from->fresh()->balance)->toBe(600.0)
        ->and((float) $this->to->fresh()->balance)->toBe(400.0);
});

it('backfills transfers that were written without ledger entries', function () {
    // Exactly what a 0.2.x row looks like: no from_trx_id, no to_trx_id.
    $legacy = Transfer::create([
        'unique_id' => 'LEGACY1',
        'amount' => 300,
        'from_account' => $this->from->id,
        'to_account' => $this->to->id,
        'date' => now()->toDateString(),
        'created_by' => null,
    ]);

    expect($legacy->isPosted())->toBeFalse()
        ->and((float) $this->from->fresh()->balance)->toBe(1000.0);

    $repaired = $this->transfers->backfillLedgerEntries();

    expect($repaired)->toBe(1)
        ->and($legacy->fresh()->isPosted())->toBeTrue()
        ->and((float) $this->from->fresh()->balance)->toBe(700.0)
        ->and((float) $this->to->fresh()->balance)->toBe(300.0);
});
