<?php

use ArtflowStudio\AccountFlow\Enums\TransactionType;
use ArtflowStudio\AccountFlow\Events\TransactionCreated;
use ArtflowStudio\AccountFlow\Events\TransactionReversed;
use ArtflowStudio\AccountFlow\Exceptions\InvalidTransactionException;
use ArtflowStudio\AccountFlow\Exceptions\RecordNotFoundException;
use ArtflowStudio\AccountFlow\Models\Account;
use ArtflowStudio\AccountFlow\Models\Category;
use ArtflowStudio\AccountFlow\Models\PaymentMethod;
use ArtflowStudio\AccountFlow\Services\TransactionService;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->account = Account::factory()->withOpeningBalance(1000)->create();
    $this->method = PaymentMethod::factory()->create(['account_id' => $this->account->id]);
    $this->income = Category::factory()->income()->create();
    $this->expense = Category::factory()->expense()->create();
    $this->service = app(TransactionService::class);
});

function attrs(): array
{
    return [
        'account_id' => test()->account->id,
        'category_id' => test()->expense->id,
        'payment_method' => test()->method->id,
    ];
}

it('adds income to the account balance', function () {
    $this->service->income(500, 'sale', attrs());

    expect((float) $this->account->fresh()->balance)->toBe(1500.0);
});

it('subtracts an expense from the account balance', function () {
    $this->service->expense(300, 'supplies', attrs());

    expect((float) $this->account->fresh()->balance)->toBe(700.0);
});

it('keeps the opening balance an account was created with', function () {
    // Account::$fillable omitted opening_balance, so this silently became 0.
    $account = app(ArtflowStudio\AccountFlow\Services\AccountService::class)
        ->create(['name' => 'Petty Cash', 'opening_balance' => 250]);

    expect((float) $account->opening_balance)->toBe(250.0)
        ->and((float) $account->balance)->toBe(250.0);
});

it('rejects a non-positive amount', function () {
    $this->service->income(0, 'nothing', attrs());
})->throws(InvalidTransactionException::class);

it('rejects an unknown type', function () {
    $this->service->create(['type' => 'sideways', 'amount' => 10] + attrs());
})->throws(InvalidTransactionException::class);

it('rejects a missing account', function () {
    $this->service->create(['type' => 1, 'amount' => 10, 'account_id' => 99999]);
})->throws(RecordNotFoundException::class);

it('re-bases the balance when an amount changes', function () {
    $transaction = $this->service->income(500, 'sale', attrs());

    $this->service->update($transaction, ['amount' => 800]);

    expect((float) $this->account->fresh()->balance)->toBe(1800.0);
});

it('re-bases the balance when a type flips', function () {
    $transaction = $this->service->income(500, 'sale', attrs());

    $this->service->update($transaction, ['type' => TransactionType::Expense]);

    expect((float) $this->account->fresh()->balance)->toBe(500.0);
});

it('moves the balance between accounts when the account changes', function () {
    $other = Account::factory()->withOpeningBalance(0)->create();
    $transaction = $this->service->income(500, 'sale', attrs());

    $this->service->update($transaction, ['account_id' => $other->id]);

    expect((float) $this->account->fresh()->balance)->toBe(1000.0)
        ->and((float) $other->fresh()->balance)->toBe(500.0);
});

it('undoes the balance effect on delete', function () {
    $transaction = $this->service->income(500, 'sale', attrs());

    $this->service->delete($transaction);

    expect((float) $this->account->fresh()->balance)->toBe(1000.0);
});

it('books a reversal as a same-type contra entry, not an opposite-type one', function () {
    $transaction = $this->service->income(500, 'sale', attrs());

    $reversal = $this->service->reverse($transaction, 'refund');

    expect((int) $reversal->type)->toBe(TransactionType::Income->value)
        ->and((float) $reversal->amount)->toBe(-500.0)
        ->and((int) $reversal->reversal_of_id)->toBe((int) $transaction->id)
        ->and($transaction->fresh()->reversed_at)->not->toBeNull()
        ->and((float) $this->account->fresh()->balance)->toBe(1000.0);
});

it('nets a reversed sale to zero in the summary', function () {
    $transaction = $this->service->income(500, 'sale', attrs());
    $this->service->reverse($transaction);

    $summary = $this->service->getSummary(null, null, $this->account->id);

    expect($summary['total_income'])->toBe(0.0)
        ->and($summary['net'])->toBe(0.0);
});

it('refuses to reverse the same transaction twice', function () {
    $transaction = $this->service->income(500, 'sale', attrs());
    $this->service->reverse($transaction);

    $this->service->reverse($transaction->fresh());
})->throws(InvalidTransactionException::class);

it('rolls the whole batch back when one entry fails', function () {
    $before = (float) $this->account->fresh()->balance;

    try {
        $this->service->createBatch([
            ['type' => 1, 'amount' => 100] + attrs(),
            ['type' => 1, 'amount' => -5] + attrs(),
        ]);
    } catch (InvalidTransactionException) {
        // expected
    }

    expect((float) $this->account->fresh()->balance)->toBe($before)
        ->and(ArtflowStudio\AccountFlow\Models\Transaction::count())->toBe(0);
});

it('fires domain events', function () {
    Event::fake([TransactionCreated::class, TransactionReversed::class]);

    $transaction = $this->service->income(500, 'sale', attrs());
    Event::assertDispatched(TransactionCreated::class);

    $this->service->reverse($transaction);
    Event::assertDispatched(TransactionReversed::class);
});

it('recalculates a drifted balance from the ledger', function () {
    $this->service->income(500, 'sale', attrs());
    $this->account->forceFill(['balance' => 999999])->save();

    $balance = app(ArtflowStudio\AccountFlow\Services\AccountService::class)
        ->recalculateBalance($this->account->id);

    expect($balance)->toBe(1500.0);
});
