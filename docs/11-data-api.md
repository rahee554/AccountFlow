# 11 — Data API (accounts, categories, payment methods)

Everything here is for pulling data out to build your **own** UI — a custom
dashboard widget, a report, a page that isn't one of the bundled screens. If
you just want transactions, see [02 — Transactions](02-transactions.md); this
page covers the other core entities.

Every method below already exists in the package — nothing here needs new
code, just the right call.

## The facade

`Accountflow::` resolves one service per entity. Each is a singleton, so
`app(AccountService::class)` and `Accountflow::accounts()` are the same
instance.

```php
use ArtflowStudio\AccountFlow\Facades\Accountflow;

Accountflow::accounts();        // AccountService
Accountflow::categories();      // CategoryService
Accountflow::paymentMethods();  // PaymentMethodService
Accountflow::transactions();    // TransactionService — see 02-transactions.md
Accountflow::transfers();       // TransferService
Accountflow::budgets();         // BudgetService
Accountflow::reports();         // ReportService
```

## Accounts

```php
use ArtflowStudio\AccountFlow\Models\Account;

Account::active()->orderBy('name')->get();      // query scope, chain freely
Accountflow::accounts()->getActive();            // same result, pre-built
Accountflow::accounts()->getAll();               // every account, active or not

Accountflow::accounts()->getBalance($accountId);             // float
Accountflow::accounts()->getTransactions($accountId, $from, $to); // Collection<Transaction>
Accountflow::accounts()->getStatistics($accountId, $from, $to);
// => ['opening_balance' => .., 'income' => .., 'expense' => .., 'net' => .., 'balance' => ..]

Accountflow::accounts()->totalBalance(); // sum of balance across active accounts
```

Writing:

```php
Accountflow::accounts()->create(['name' => 'Petty Cash', 'opening_balance' => 5000]);
Accountflow::accounts()->update($account, ['active' => false]);
Accountflow::accounts()->activate($account);
Accountflow::accounts()->deactivate($account);
Accountflow::accounts()->delete($account); // throws if it still has transactions
```

`balance` is a maintained column, not computed on read — every write above
routes through a row-locked updater. If you ever suspect drift, `Account`
also exposes `calculatedBalance()`, which re-derives the balance from the
ledger in SQL for comparison, and `Accountflow::accounts()->recalculateBalance($id)`
to persist the correction.

## Categories

```php
use ArtflowStudio\AccountFlow\Models\Category;
use ArtflowStudio\AccountFlow\Enums\CategoryType;

Category::active()->ofType(CategoryType::Expense)->topLevel()->get();

Accountflow::categories()->getIncomeCategories();   // shortcut for ofType(Income)
Accountflow::categories()->getExpenseCategories();
Accountflow::categories()->getHierarchy(CategoryType::Expense->value);
// => [['id' => .., 'name' => .., 'children' => [['id' => .., 'name' => ..], ...]], ...]
```

`$category->isIncome()`, `isExpense()`, `iconUrl()` are available on any
loaded instance.

Writing — categories with `privacy === 1` are the seeded defaults and are
locked: `update()`/`delete()` on those will fail on purpose.

```php
Accountflow::categories()->create(['name' => 'Fuel', 'type' => CategoryType::Expense->value]);
Accountflow::categories()->activate($category);
Accountflow::categories()->lock($category);   // privacy = 1, prevents user edits
Accountflow::categories()->delete($category); // throws if locked, has transactions, or has children
```

## Payment methods

```php
use ArtflowStudio\AccountFlow\Models\PaymentMethod;

PaymentMethod::active()->get();
Accountflow::paymentMethods()->getActive();                 // same, pre-built
Accountflow::paymentMethods()->getByAccount($accountId);    // scoped to one account

$method->isActive(); // bool, on any loaded instance
```

Writing:

```php
Accountflow::paymentMethods()->create(['name' => 'Stripe', 'account_id' => $accountId]);
Accountflow::paymentMethods()->linkToAccount($method, $accountId);
Accountflow::paymentMethods()->unlinkFromAccount($method);
Accountflow::paymentMethods()->validate($methodId); // true only if it exists and is active
```

## Transfers

No dedicated query scopes — a `Transfer` is two ledger legs (`fromAccount`
→ `toAccount`) plus bookkeeping:

```php
use ArtflowStudio\AccountFlow\Models\Transfer;

Transfer::with('fromAccount', 'toAccount')->latest()->get();

$transfer->isPosted();  // both legs exist
$transfer->legs();      // the two posted Transaction rows, as a Collection
```

## Budgets

```php
use ArtflowStudio\AccountFlow\Models\Budget;

Budget::forYear(2026)->with('account', 'category')->get();

$budget->isMonthly(); // period === 'monthly'
```

A budget's window comes from `period` + `year` + `month` only — there is no
`start_date`/`end_date`/`status` column, whatever an older version of this
package (or old docs) implied.

## Why go through the service instead of the model directly?

Reads: no difference — `Account::active()->get()` and
`Accountflow::accounts()->getActive()` hit the same query. Use whichever
reads better inline.

Writes: always go through the service (`Accountflow::accounts()->update(...)`,
never `$account->update([...])` directly) when the field you're changing has
side effects — `opening_balance` on an account triggers a balance
recalculation, category `status`/`privacy` changes are validated, payment
method `account_id` is checked against real accounts. The services are where
that logic lives; writing straight to the model bypasses it silently.
