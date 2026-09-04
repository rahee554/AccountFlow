<?php

use ArtflowStudio\AccountFlow\Livewire\Loans\CreateLoan;
use ArtflowStudio\AccountFlow\Livewire\Transactions\CreateTransactionMultiple;
use ArtflowStudio\AccountFlow\Models\Account;
use ArtflowStudio\AccountFlow\Models\AuditTrail;
use ArtflowStudio\AccountFlow\Models\Category;
use ArtflowStudio\AccountFlow\Models\Loan;
use ArtflowStudio\AccountFlow\Models\LoanUser;
use ArtflowStudio\AccountFlow\Models\PaymentMethod;
use ArtflowStudio\AccountFlow\Models\Setting;
use ArtflowStudio\AccountFlow\Models\Transaction;
use Livewire\Livewire;

/*
 * The services were fixed first, but a service nobody calls fixes nothing.
 * These drive the actual Livewire screens.
 */

beforeEach(function () {
    $this->account = Account::factory()->withOpeningBalance(100000)->create(['name' => 'Cash Account']);
    $this->method = PaymentMethod::factory()->create(['account_id' => $this->account->id]);
    $this->income = Category::factory()->income()->create();
    $this->expense = Category::factory()->expense()->create();

    Setting::put('default_account_id', $this->account->id);
    Setting::put('default_payment_method_id', $this->method->id);
    Setting::put('default_sales_category_id', $this->income->id);
    Setting::put('default_expense_category_id', $this->expense->id);
});

it('moves money when a loan is recorded on the loan screen', function () {
    // The screen called Loan::create() and stopped, so the cash never appeared.
    $partner = LoanUser::create(['name' => 'Bank', 'contact' => '1', 'cnic' => '2']);

    Livewire::test(CreateLoan::class)
        ->set('name', 'Bank loan')
        ->set('amount', '500000')
        ->set('loan_type', 2)          // 2 = Borrowed: money comes in
        ->set('loan_partner_id', (string) $partner->id)
        ->set('account_id', (string) $this->account->id)
        ->set('date', now()->toDateString())
        ->call('save');

    expect(Loan::count())->toBe(1)
        ->and((float) $this->account->fresh()->balance)->toBe(600000.0)
        ->and(Transaction::count())->toBe(1);
});

it('takes money out when a loan is given on the loan screen', function () {
    $partner = LoanUser::create(['name' => 'Ali', 'contact' => '1', 'cnic' => '2']);

    Livewire::test(CreateLoan::class)
        ->set('name', 'Advance to Ali')
        ->set('amount', '30000')
        ->set('loan_type', 1)          // 1 = Lent: money goes out
        ->set('loan_partner_id', (string) $partner->id)
        ->set('account_id', (string) $this->account->id)
        ->set('date', now()->toDateString())
        ->call('save');

    expect((float) $this->account->fresh()->balance)->toBe(70000.0);
});

it('refuses to change a posted loan amount from the screen', function () {
    $partner = LoanUser::create(['name' => 'Bank', 'contact' => '1', 'cnic' => '2']);

    $loan = app(ArtflowStudio\AccountFlow\Services\LoanService::class)
        ->borrow(1000, $partner->id, ['account_id' => $this->account->id]);

    $balanceAfterLoan = (float) $this->account->fresh()->balance;

    Livewire::test(CreateLoan::class, ['id' => $loan->id])
        ->set('amount', '999999')
        ->call('save');

    // Editing the figure must not silently desync the ledger from the loan.
    expect((float) $loan->fresh()->amount)->toBe(1000.0)
        ->and((float) $this->account->fresh()->balance)->toBe($balanceAfterLoan);
});

it('audits multi-entry transactions, which used to bypass the service', function () {
    Livewire::test(CreateTransactionMultiple::class)
        ->set('type', 2)
        ->set('account_id', $this->account->id)
        ->set('payment_method', $this->method->id)
        ->set('transactions', [
            ['amount' => 100, 'category_id' => $this->expense->id, 'date' => now()->toDateString(), 'description' => 'A'],
            ['amount' => 250, 'category_id' => $this->expense->id, 'date' => now()->toDateString(), 'description' => 'B'],
        ])
        ->call('storeTransactions');

    expect(Transaction::count())->toBe(2)
        ->and((float) $this->account->fresh()->balance)->toBe(99650.0)
        // The old path raised no events, so nothing reached the audit trail.
        ->and(AuditTrail::where('model_type', 'Transaction')->count())->toBe(2);
});

it('writes none of a multi-entry batch when one row is invalid', function () {
    Livewire::test(CreateTransactionMultiple::class)
        ->set('type', 2)
        ->set('account_id', $this->account->id)
        ->set('payment_method', $this->method->id)
        ->set('transactions', [
            ['amount' => 100, 'category_id' => $this->expense->id, 'date' => now()->toDateString(), 'description' => 'A'],
            ['amount' => 250, 'category_id' => 999999, 'date' => now()->toDateString(), 'description' => 'bad category'],
        ])
        ->call('storeTransactions');

    // Atomic: the good row must not survive on its own.
    expect(Transaction::count())->toBe(0)
        ->and((float) $this->account->fresh()->balance)->toBe(100000.0);
});
