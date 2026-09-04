# 0 — Quick start for app developers

If you build the screens your users see, this is the only page you need.

AccountFlow keeps the accounting correct in the background. Your users record
"money came in" and "money went out". They never see a ledger, a transfer or a
category id.

## The five calls that cover most apps

```php
use ArtflowStudio\AccountFlow\Facades\Accountflow as AccountFlow;

// Money in
AccountFlow::money()->received(1500, 'Sale to Ali');

// Money out
AccountFlow::money()->spent(250, 'Fuel', category: 'Transport');

// Money moved between the business's own accounts
AccountFlow::money()->moved(500, from: 'Cash Account', to: 'Bank Account');

// What is in each account
AccountFlow::money()->balance('Cash Account');   // 11500.00
AccountFlow::money()->balance();                 // ['Bank Account' => …, 'Cash Account' => …]

// In, out and the difference for a period
AccountFlow::money()->summary('2026-01-01', '2026-12-31');
// ['received' => 250000.0, 'spent' => 180000.0, 'difference' => 70000.0, 'entries' => 412]
```

## What the package does for you

You pass names; it handles the rest.

| You do not have to | Because |
|--------------------|---------|
| Look up account ids | Pass the account name, or omit it for the default |
| Pre-create categories | A new category name is created and filed correctly |
| Pick a payment method | Falls back to the configured default |
| Update balances | Every posting updates the balance under a row lock |
| Keep transfers out of profit & loss | Transfer legs are tagged and excluded automatically |
| Write an audit trail | Every write raises an event the audit listener records |

## Undo, don't delete

```php
AccountFlow::money()->undo($transaction, 'Customer cancelled');
```

This posts a reversing entry rather than removing the row, so the balance
returns to where it was and the history still explains itself. Showing users an
"Undo" button that maps to this is better than a delete button.

## Errors your UI can show directly

Everything throws `AccountFlowException` (or a subclass) with a message written
for a person, not a developer:

```php
use ArtflowStudio\AccountFlow\Exceptions\AccountFlowException;

try {
    AccountFlow::money()->moved(50000, from: 'Cash Account', to: 'Bank Account');
} catch (AccountFlowException $e) {
    // "Account [Cash Account] has 10000.00 available, which is less than 50000."
    session()->flash('error', $e->getMessage());
}
```

## Ready-made screens

Every screen ships as a Livewire component, already authorized:

```blade
<x-accountflow::table table="transactions" />
@accountflow(['table' => 'accounts'])
```

Or send users to the routes: `/accounts`, `/accounts/transactions`,
`/accounts/dashboard`, `/accounts/report`.

## When you need more

The plain API is a shortcut over the full services, which are all still there:

```php
AccountFlow::transactions();     // exact control, batches, reversal
AccountFlow::accounts();         // balances, recalculation, statistics
AccountFlow::transfers();        // transfers with both ledger legs
AccountFlow::loans();            // borrow, lend, repay, outstanding
AccountFlow::equity();           // partner capital in and out
AccountFlow::assets();           // purchases and disposals
AccountFlow::budgets();          // budget vs actual
AccountFlow::reports();          // P&L, cash flow, ledger, balances
AccountFlow::plannedPayments();  // scheduled payments
AccountFlow::wallets();          // staff floats and advances
AccountFlow::features();         // module toggles
AccountFlow::settings();         // stored settings
```

Read [09 — The accounting model](09-accounting-model.md) to understand what
AccountFlow is and is not, before you promise anything to an accountant.
