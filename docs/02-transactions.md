# 2 — Transactions

## Creating

```php
use ArtflowStudio\AccountFlow\Facades\Accountflow;

Accountflow::transactions()->income(1500, 'Invoice #221 payment');
Accountflow::transactions()->expense(250, 'Office supplies');
```

Everything except `amount` and `type` is optional. The account resolves from the
payment method, and the category and payment method fall back to your configured
defaults.

```php
use ArtflowStudio\AccountFlow\Enums\TransactionType;

Accountflow::transactions()->create([
    'type'           => TransactionType::Income,  // enum, 1, or 'income'
    'amount'         => 1500,
    'description'    => 'Invoice #221 payment',
    'account_id'     => $accountId,
    'category_id'    => $categoryId,
    'payment_method' => $methodId,
    'date'           => now(),
    'user_id'        => $userId,   // stored in `added_by`
]);
```

`create()` is the **only** method that inserts. `income()`, `expense()`,
`createIncome()`, `createExpense()` and `createBatch()` all route through it, so
validation, balance updates and events cannot be bypassed.

## Types

`TransactionType` is backed by the same integers already in the column, so
nothing needed converting:

| Case | Value | Sign |
|------|-------|------|
| `TransactionType::Income` | 1 | `+1` |
| `TransactionType::Expense` | 2 | `-1` |

`TransactionType::tryParse()` accepts the enum, `1`/`2`, or the legacy `'income'`
/ `'expense'` strings that had leaked into the column.

The model casts `type` to **int**, not to the enum — the bundled views compare it
loosely (`$row->type == 1`) and collections filter on it, both of which an enum
cast would silently break. Use the accessor when you want the enum:

```php
$transaction->transactionType();   // TransactionType|null
$transaction->isIncome();
$transaction->signedAmount();      // negative for expenses
```

## Balances

Every balance write goes through `Support\BalanceUpdater`, which selects the row
`FOR UPDATE` inside a transaction. Two concurrent requests serialise instead of
racing.

```php
Accountflow::accounts()->getBalance($accountId);
Accountflow::accounts()->recalculateBalance($accountId);  // repair from the ledger
Accountflow::accounts()->recalculateAll();
```

## Updating

```php
Accountflow::transactions()->update($transaction, [
    'amount' => 800,
    'type'   => TransactionType::Expense,
]);
```

If the amount, type or account changes, the original effect is reversed off the
old account and the new effect applied to the new one, so balances stay correct
across a move.

## Deleting

```php
Accountflow::transactions()->delete($transaction);
```

Deleting undoes the entry's effect on the account balance.

## Reversing

A reversal is a **contra entry**: same type, negative amount, linked back to the
original.

```php
$reversal = Accountflow::transactions()->reverse($transaction, 'Customer refund');

$transaction->fresh()->isReversed();   // true
$reversal->isReversal();               // true
$reversal->reverses;                   // the original
```

This is why it matters: booking the reversal as the *opposite* type — as 0.2.x
did — made a reversed sale appear as an expense, inflating both revenue and
costs. As a contra entry, profit & loss nets to zero.

Reversing twice, or reversing a reversal, raises
`InvalidTransactionException`.

## Batches

```php
Accountflow::transactions()->createBatch([
    ['type' => 1, 'amount' => 500, 'description' => 'Sale A'],
    ['type' => 2, 'amount' => 120, 'description' => 'Courier'],
]);
```

Atomic: if any entry is invalid, none are written and no balance moves.

## Querying

```php
use ArtflowStudio\AccountFlow\Models\Transaction;

Transaction::income()->between($from, $to)->sum('amount');
Transaction::expense()->forAccount($id)->count();
Transaction::forAccount($id)->notReversed()->latest('date')->paginate();
Transaction::ofType(TransactionType::Income)->since($date)->get();
```

Summaries aggregate in SQL:

```php
Accountflow::transactions()->getSummary($from, $to, $accountId);
// ['total_income' => ..., 'total_expense' => ..., 'net' => ..., 'count' => ...]
```

## Errors

Everything throws a subclass of `AccountFlowException`, so one `catch` covers
the package:

| Exception | Raised when |
|-----------|-------------|
| `InvalidTransactionException` | Amount not positive, unknown type, double reversal |
| `RecordNotFoundException` | Account, category or payment method missing/inactive |
| `AuthorizationException` | Ability denied |

## Events

| Event | Carries |
|-------|---------|
| `TransactionCreated` | `$transaction` |
| `TransactionUpdated` | `$transaction`, `$before` |
| `TransactionDeleted` | `$transaction` |
| `TransactionReversed` | `$original`, `$reversal`, `$reason` |
| `AccountBalanceChanged` | `$account`, `$from`, `$to`, `delta()` |

The audit trail is a listener on these, which is why it cannot silently stop
recording — see [07 — Extending](07-extending.md).
