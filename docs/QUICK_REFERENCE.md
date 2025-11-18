# AccountFlow - Quick Reference Card

## Import the Facade

```php
use ArtflowStudio\AccountFlow\Facades\Accountflow;
```

> **Note:** `AC` facade is still available but `Accountflow` is the recommended primary interface.

---

## Transactions

```php
// Create
Accountflow::transactions()->create(['amount' => 1000, 'type' => 1, ...])
Accountflow::transactions()->createIncome(['amount' => 1000, ...])
Accountflow::transactions()->createExpense(['amount' => 500, ...])

// Update/Delete
Accountflow::transactions()->update($transaction, ['amount' => 1200])
Accountflow::transactions()->delete($transaction)
Accountflow::transactions()->reverse($transaction, 'Reason')

// Query
Accountflow::transactions()->getSummary($start, $end, $accountId)
Accountflow::transactions()->getActivePaymentMethods()
Accountflow::transactions()->getCategoriesForType(1) // 1=income, 2=expense
```

---

## Accounts

```php
// Create
Accountflow::accounts()->create(['name' => 'Checking', 'opening_balance' => 5000])

// Query
Accountflow::accounts()->getBalance($accountId)
Accountflow::accounts()->recalculateBalance($accountId)
Accountflow::accounts()->getTransactions($accountId, $start, $end, $limit)
Accountflow::accounts()->getStatistics($accountId, $start, $end)
Accountflow::accounts()->getAll($onlyActive)

// Manage
Accountflow::accounts()->update($account, ['name' => 'New Name'])
Accountflow::accounts()->activate($account)
Accountflow::accounts()->deactivate($account)
Accountflow::accounts()->delete($account)
```

---

## Categories

```php
// Create
Accountflow::categories()->create(['name' => 'Sales', 'type' => 1, 'parent_id' => null])

// Query
Accountflow::categories()->getIncomeCategories()
Accountflow::categories()->getExpenseCategories()
Accountflow::categories()->getHierarchy(1) // With structure
Accountflow::categories()->getWithChildren($id)
Accountflow::categories()->getAll()

// Manage
Accountflow::categories()->lock($category)
Accountflow::categories()->unlock($category)
Accountflow::categories()->activate($category)
Accountflow::categories()->deactivate($category)
Accountflow::categories()->delete($category)
```

---

## Payment Methods

```php
// Create
Accountflow::paymentMethods()->create(['name' => 'Stripe', 'account_id' => 1])

// Query
Accountflow::paymentMethods()->getAll()
Accountflow::paymentMethods()->getActive()
Accountflow::paymentMethods()->getByAccount($id)
Accountflow::paymentMethods()->validate($methodId)

// Manage
Accountflow::paymentMethods()->linkToAccount($method, $accountId)
Accountflow::paymentMethods()->unlinkFromAccount($method)
Accountflow::paymentMethods()->activate($method)
Accountflow::paymentMethods()->deactivate($method)
Accountflow::paymentMethods()->delete($method)
```

---

## Budgets

```php
// Create
Accountflow::budgets()->create([
    'account_id' => 1,
    'category_id' => 5,
    'amount' => 5000,
    'period' => 'monthly',
    'alert_threshold' => 80
])

// Query
Accountflow::budgets()->analyze($budgetId)
Accountflow::budgets()->getAlertsForAccount($accountId)
Accountflow::budgets()->getActive($accountId)
Accountflow::budgets()->getTransactions($budgetId, $limit)

// Manage
Accountflow::budgets()->update($budget, ['amount' => 6000])
Accountflow::budgets()->activate($budget)
Accountflow::budgets()->deactivate($budget)
Accountflow::budgets()->delete($budget)
```

---

## Reports

```php
// Financial Statements
Accountflow::reports()->profitAndLoss($start, $end)
Accountflow::reports()->incomeExpenseReport($start, $end)
Accountflow::reports()->cashFlowReport($start, $end)
Accountflow::reports()->balanceReport()

// Analysis
Accountflow::reports()->byPaymentMethod($start, $end)
Accountflow::reports()->categoryPerformance($start, $end)
Accountflow::reports()->dailySummary($start, $end)
```

---

## Settings

```php
// Get/Set
Accountflow::settings()->get('key', $default)
Accountflow::settings()->set('key', 'value')
Accountflow::settings()->getAll()

// Defaults
Accountflow::settings()->defaultPaymentMethodId()
Accountflow::settings()->defaultAccountId()
Accountflow::settings()->defaultSalesCategoryId()
Accountflow::settings()->defaultExpenseCategoryId()

// Features
Accountflow::settings()->enableFeature('feature')
Accountflow::settings()->disableFeature('feature')
Accountflow::settings()->isFeatureEnabled('feature')
```

---

## Audit

```php
// Log
Accountflow::audit()->log('action', ['detail' => 'value'], 'Description')
Accountflow::audit()->logTransactionCreated($id, $data)
Accountflow::audit()->logTransactionUpdated($id, $changes)
Accountflow::audit()->logTransactionDeleted($id, $data)

// Query
Accountflow::audit()->getRecent($limit)
Accountflow::audit()->getByUser($userId, $limit)
Accountflow::audit()->getByAction('action', $limit)
Accountflow::audit()->getByDateRange($start, $end, $limit)

// Manage
Accountflow::audit()->getSummary()
Accountflow::audit()->deleteOlderThan(90) // Delete logs > 90 days old
Accountflow::audit()->export($start, $end)
```

---

## Common Patterns

### Invoice with Transaction

```php
$invoice = Invoice::create([...]);
$transaction = Accountflow::transactions()->createIncome([
    'amount' => $invoice->total,
    'category_id' => Accountflow::settings()->defaultSalesCategoryId(),
    'description' => "Invoice #{$invoice->reference}",
]);
Accountflow::audit()->logTransactionCreated($transaction->id, $transaction->toArray());
```

### Check Budget Alerts

```php
$alerts = Accountflow::budgets()->getAlertsForAccount($accountId);
foreach ($alerts as $alert) {
    notify("Budget alert: {$alert['category_name']} at {$alert['percentage_used']}%");
}
```

### Generate Report

```php
$pl = Accountflow::reports()->profitAndLoss('2024-01-01', '2024-12-31');
return response()->json($pl);
```

### Track Expense

```php
$expense = Accountflow::transactions()->createExpense([
    'amount' => 250,
    'category_id' => 10,
]);
Accountflow::audit()->logTransactionCreated($expense->id, $expense->toArray());
```

---

## Exception Handling

```php
try {
    $transaction = Accountflow::transactions()->create($data);
} catch (\Exception $e) {
    Log::error('Transaction failed: ' . $e->getMessage());
    return response()->json(['error' => $e->getMessage()], 422);
}
```

Common exceptions:
- `"Transaction amount must be greater than 0"`
- `"Account #X not found"`
- `"Cannot delete account with existing transactions"`
- `"Payment method #X not found or inactive"`

---

## Data Types

| Field | Type | Example |
|-------|------|---------|
| amount | float | 1000.50 |
| type | int | 1 (income) or 2 (expense) |
| date | string/Carbon | '2024-01-01' or Carbon::now() |
| status | int | 1 (active) or 2 (inactive) |
| period | string | 'daily', 'weekly', 'monthly', 'yearly' |

---

## Directory Structure

Services: `vendor/artflow-studio/accountflow/src/app/Services/`
Facade: `vendor/artflow-studio/accountflow/src/Facades/AC.php`
Docs: `vendor/artflow-studio/accountflow/docs/`

---

For detailed documentation, see:
- **SERVICES_INDEX.md** - All service methods
- **SERVICES_QUICK_GUIDE.md** - Usage examples
- **PACKAGE_OVERVIEW.md** - Complete overview


