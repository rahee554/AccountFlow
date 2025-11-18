# Using AccountFlow Services - Quick Guide

This guide shows how to use all AccountFlow services in your application.

## Installation

The services are automatically available through the AC facade:

```php
use ArtflowStudio\AccountFlow\Facades\AC;
```

---

## Transaction Management

### Creating Transactions

```php
// Basic transaction
$transaction = Accountflow::transactions()->create([
    'amount' => 1000,
    'type' => 1, // 1 = income, 2 = expense
    'payment_method' => 1,
    // Auto-filled: unique_id, account_id, date, user_id
]);

// Income transaction (shorthand)
$income = Accountflow::transactions()->createIncome([
    'amount' => 500,
    'category_id' => 2,
]);

// Expense transaction (shorthand)
$expense = Accountflow::transactions()->createExpense([
    'amount' => 250,
    'category_id' => 5,
]);

// Batch create
$transactions = Accountflow::transactions()->createBatch([
    ['amount' => 100, 'type' => 1],
    ['amount' => 50, 'type' => 2],
]);
```

### Managing Transactions

```php
// Update transaction
$transaction = Accountflow::transactions()->update($transaction, [
    'amount' => 1200,
    'description' => 'Updated amount',
]);

// Reverse transaction (creates offsetting entry)
$reversing = Accountflow::transactions()->reverse($transaction, 'Duplicate entry');

// Delete transaction
Accountflow::transactions()->delete($transaction);
```

### Querying Transactions

```php
// Get statistics for period
$stats = Accountflow::transactions()->getSummary(
    '2024-01-01',      // startDate
    '2024-12-31',      // endDate
    1                  // accountId (optional)
);
// Returns: total_income, total_expense, net, count, by_category, by_payment_method

// Get active payment methods
$methods = Accountflow::transactions()->getActivePaymentMethods();

// Get categories for type
$incomeCategories = Accountflow::transactions()->getCategoriesForType(1);
$expenseCategories = Accountflow::transactions()->getCategoriesForType(2);
```

---

## Account Management

### Creating Accounts

```php
$account = Accountflow::accounts()->create([
    'name' => 'Checking Account',
    'description' => 'Primary business account',
    'opening_balance' => 5000,
    'active' => true,
]);
```

### Querying Accounts

```php
// Get balance
$balance = Accountflow::accounts()->getBalance($accountId);

// Recalculate from transactions
$newBalance = Accountflow::accounts()->recalculateBalance($accountId);

// Get transactions for period
$transactions = Accountflow::accounts()->getTransactions(
    $accountId,
    '2024-01-01',
    '2024-12-31',
    50 // limit
);

// Get account statistics
$stats = Accountflow::accounts()->getStatistics($accountId);
// Returns: total_income, total_expenses, net, transaction_count, average_transaction, by_category

// Get all accounts
$accounts = Accountflow::accounts()->getAll($onlyActive = true);
```

### Managing Accounts

```php
// Update account
Accountflow::accounts()->update($account, [
    'name' => 'Updated Name',
]);

// Deactivate/activate
Accountflow::accounts()->deactivate($account);
Accountflow::accounts()->activate($account);

// Delete (only if no transactions)
Accountflow::accounts()->delete($account);
```

---

## Category Management

### Creating Categories

```php
// Main category
$category = Accountflow::categories()->create([
    'name' => 'Product Sales',
    'type' => 1, // 1 = income, 2 = expense
    'parent_id' => null, // Top-level
    'privacy' => 1, // 1 = locked, 2 = unlocked
    'status' => 1, // 1 = active, 2 = inactive
]);

// Sub-category
$subCategory = Accountflow::categories()->create([
    'name' => 'Online Sales',
    'type' => 1,
    'parent_id' => $category->id, // Link to parent
]);
```

### Querying Categories

```php
// Get by type
$incomeCategories = Accountflow::categories()->getIncomeCategories();
$expenseCategories = Accountflow::categories()->getExpenseCategories();

// Get all categories
$all = Accountflow::categories()->getAll($onlyActive = true);

// Get hierarchy
$hierarchy = Accountflow::categories()->getHierarchy(1); // type 1
// Returns nested structure with parent and children

// Get single with children
$category = Accountflow::categories()->getWithChildren($categoryId);
```

### Managing Categories

```php
// Update
Accountflow::categories()->update($category, [
    'name' => 'Updated Name',
    'status' => 1,
]);

// Lock/unlock (privacy control)
Accountflow::categories()->lock($category);     // Prevent modification
Accountflow::categories()->unlock($category);   // Allow modification

// Deactivate/activate
Accountflow::categories()->deactivate($category);
Accountflow::categories()->activate($category);

// Delete (only if no transactions and unlocked)
Accountflow::categories()->delete($category);
```

---

## Payment Method Management

### Creating Payment Methods

```php
$method = Accountflow::paymentMethods()->create([
    'name' => 'Stripe',
    'account_id' => 1,
    'logo_icon' => 'stripe.svg',
    'info' => 'Production account',
    'status' => 1,
]);
```

### Querying Payment Methods

```php
// Get all methods
$all = Accountflow::paymentMethods()->getAll();

// Get active methods only
$active = Accountflow::paymentMethods()->getActive();

// Get by account
$byAccount = Accountflow::paymentMethods()->getByAccount($accountId);

// Validate method
$isValid = Accountflow::paymentMethods()->validate($methodId);
```

### Managing Payment Methods

```php
// Update
Accountflow::paymentMethods()->update($method, [
    'name' => 'Stripe - Updated',
]);

// Link to account
Accountflow::paymentMethods()->linkToAccount($method, $accountId);

// Unlink from account
Accountflow::paymentMethods()->unlinkFromAccount($method);

// Activate/deactivate
Accountflow::paymentMethods()->activate($method);
Accountflow::paymentMethods()->deactivate($method);

// Delete (only if no transactions)
Accountflow::paymentMethods()->delete($method);
```

---

## Budget Management

### Creating Budgets

```php
$budget = Accountflow::budgets()->create([
    'account_id' => 1,
    'category_id' => 5,
    'amount' => 5000,
    'period' => 'monthly', // daily, weekly, monthly, yearly
    'start_date' => '2024-01-01',
    'end_date' => '2024-12-31',
    'alert_threshold' => 80, // Alert at 80% spending
    'notes' => 'Q1 marketing budget',
]);
```

### Analyzing Budgets

```php
// Detailed analysis
$analysis = Accountflow::budgets()->analyze($budgetId);
// Returns: budgeted, spent, remaining, percentage_used, variance, is_over_budget, is_alert, etc.

// Get all alerts
$alerts = Accountflow::budgets()->getAlertsForAccount($accountId);

// Get spending transactions
$transactions = Accountflow::budgets()->getTransactions($budgetId, $limit = 50);
```

### Querying Budgets

```php
// Get active budgets
$active = Accountflow::budgets()->getActive($accountId);

// Get all budgets
$all = Accountflow::budgets()->getAll($accountId);
```

### Managing Budgets

```php
// Update
Accountflow::budgets()->update($budget, [
    'amount' => 6000,
    'alert_threshold' => 75,
]);

// Deactivate/activate
Accountflow::budgets()->deactivate($budget);
Accountflow::budgets()->activate($budget);

// Delete
Accountflow::budgets()->delete($budget);
```

---

## Financial Reports

### Income & Expense Analysis

```php
$report = Accountflow::reports()->incomeExpenseReport(
    '2024-01-01',
    '2024-12-31',
    $accountId // optional
);

// Returns:
// - period: date range
// - summary: total_income, total_expense, net_income, transaction_count
// - income_by_category: breakdown
// - expense_by_category: breakdown
```

### Profit & Loss Statement

```php
$pl = Accountflow::reports()->profitAndLoss('2024-01-01', '2024-12-31');

// Returns:
// - period
// - revenue: total income
// - expenses: total expenses
// - profit: net result
// - profit_margin: percentage
// - revenue_breakdown: by category
// - expense_breakdown: by category
```

### Cash Flow Analysis

```php
$cashflow = Accountflow::reports()->cashFlowReport('2024-01-01', '2024-12-31');

// Returns:
// - period
// - by_month: monthly inflows, outflows, net cash flow
// - total_inflows
// - total_outflows
```

### Balance Report

```php
$balance = Accountflow::reports()->balanceReport();

// Returns:
// - accounts: list with balances
// - total_balance
// - report_date
```

### Other Reports

```php
// By payment method
$byMethod = Accountflow::reports()->byPaymentMethod('2024-01-01', '2024-12-31');

// Daily summary
$daily = Accountflow::reports()->dailySummary('2024-01-01', '2024-12-31');

// Category performance
$categories = Accountflow::reports()->categoryPerformance('2024-01-01', '2024-12-31');
```

---

## Settings Management

### Getting Settings

```php
// Get specific setting
$value = Accountflow::settings()->get('key_name', $default = null);

// Get all settings
$all = Accountflow::settings()->getAll();

// Get default values
$methodId = Accountflow::settings()->defaultPaymentMethodId();
$accountId = Accountflow::settings()->defaultAccountId();
$categoryId = Accountflow::settings()->defaultSalesCategoryId();
$type = Accountflow::settings()->defaultTransactionType();
```

### Managing Settings

```php
// Set a value
Accountflow::settings()->set('key_name', 'value', $type = 1);

// Feature toggles
Accountflow::settings()->enableFeature('feature_name');
Accountflow::settings()->disableFeature('feature_name');
$isEnabled = Accountflow::settings()->isFeatureEnabled('feature_name');

// Delete setting
Accountflow::settings()->delete('key_name');
```

---

## Audit Logging

### Logging Actions

```php
// Log generic action
Accountflow::audit()->log('action_name', [
    'detail_key' => 'detail_value',
], 'Optional description');

// Log transaction events
Accountflow::audit()->logTransactionCreated($transactionId, $data);
Accountflow::audit()->logTransactionUpdated($transactionId, $changes);
Accountflow::audit()->logTransactionDeleted($transactionId, $data);

// Log account events
Accountflow::audit()->logAccountCreated($accountId, $data);

// Log budget events
Accountflow::audit()->logBudgetCreated($budgetId, $data);
```

### Querying Audit Logs

```php
// Get recent
$recent = Accountflow::audit()->getRecent($limit = 50);

// Get by user
$userLogs = Accountflow::audit()->getByUser($userId, $limit = 50);

// Get by action
$actionLogs = Accountflow::audit()->getByAction('transaction_created', $limit = 50);

// Get by date range
$rangeLogs = Accountflow::audit()->getByDateRange('2024-01-01', '2024-12-31', $limit = 50);

// Get summary
$summary = Accountflow::audit()->getSummary();
// Returns: total_entries, today, this_month, by_action
```

### Managing Audit Logs

```php
// Delete old logs
Accountflow::audit()->deleteOlderThan(90); // Delete logs older than 90 days

// Export logs
$export = Accountflow::audit()->export('2024-01-01', '2024-12-31');
// Returns array suitable for CSV/JSON export
```

---

## Real-World Examples

### Example 1: Invoice with Transaction

```php
use ArtflowStudio\AccountFlow\Facades\AC;
use App\Models\Invoice;

public function storeInvoice(InvoiceRequest $request)
{
    // Create invoice
    $invoice = Invoice::create($request->validated());

    try {
        // Create income transaction
        $transaction = Accountflow::transactions()->createIncome([
            'amount' => $invoice->total,
            'category_id' => Accountflow::settings()->defaultSalesCategoryId(),
            'description' => "Invoice #{$invoice->reference}",
        ]);

        // Log the action
        Accountflow::audit()->logTransactionCreated($transaction->id, $transaction->toArray());

        return response()->json([
            'invoice' => $invoice,
            'transaction' => $transaction,
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 422);
    }
}
```

### Example 2: Budget Monitoring

```php
public function checkBudgetAlerts()
{
    $accountId = auth()->user()->account_id;

    $alerts = Accountflow::budgets()->getAlertsForAccount($accountId);

    if (count($alerts) > 0) {
        foreach ($alerts as $alert) {
            Notification::send(auth()->user(), new BudgetAlertNotification($alert));
        }
    }

    return response()->json(['alerts' => $alerts]);
}
```

### Example 3: Financial Report

```php
public function generateReport(Request $request)
{
    $start = $request->input('start_date', '2024-01-01');
    $end = $request->input('end_date', date('Y-12-31'));

    $data = [
        'profit_loss' => Accountflow::reports()->profitAndLoss($start, $end),
        'cash_flow' => Accountflow::reports()->cashFlowReport($start, $end),
        'balance' => Accountflow::reports()->balanceReport(),
        'categories' => Accountflow::reports()->categoryPerformance($start, $end),
    ];

    return response()->json($data);
}
```

### Example 4: Expense Tracking

```php
public function recordExpense(ExpenseRequest $request)
{
    try {
        $expense = Accountflow::transactions()->createExpense([
            'amount' => $request->amount,
            'category_id' => $request->category_id,
            'payment_method' => $request->payment_method,
            'description' => $request->description,
            'date' => $request->date,
        ]);

        // Check if budget exceeded
        $budgets = Accountflow::budgets()->getActive($expense->account_id);
        foreach ($budgets as $budget) {
            if ($budget->category_id === $expense->category_id) {
                $analysis = Accountflow::budgets()->analyze($budget->id);
                if ($analysis['is_over_budget']) {
                    Log::warning("Budget exceeded for category {$budget->category_id}");
                }
            }
        }

        Accountflow::audit()->logTransactionCreated($expense->id, $expense->toArray());

        return response()->json(['expense' => $expense]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 422);
    }
}
```

---

## Error Handling

All services throw exceptions on validation errors:

```php
try {
    $transaction = Accountflow::transactions()->create([
        'amount' => 1000,
        'type' => 999, // Invalid type
    ]);
} catch (\Exception $e) {
    // Catch and handle
    Log::error('Transaction error: ' . $e->getMessage());
    return response()->json(['error' => $e->getMessage()], 422);
}
```

Common exceptions:
- "Transaction amount must be greater than 0"
- "Transaction type must be 1 (income) or 2 (expense)"
- "Account #X not found"
- "Payment method #X not found or inactive"
- "Cannot delete account with existing transactions"

---

## Performance Tips

1. **Use eager loading** in reports for large datasets:
   ```php
   $transactions = $account->transactions()->with('category', 'paymentMethod')->get();
   ```

2. **Use date range filtering** to limit results:
   ```php
   Accountflow::reports()->profitAndLoss('2024-01-01', '2024-12-31');
   ```

3. **Cache expensive reports**:
   ```php
   $pl = Cache::remember('pl_2024', 3600, function () {
       return Accountflow::reports()->profitAndLoss('2024-01-01', '2024-12-31');
   });
   ```

4. **Use limit on queries**:
   ```php
   Accountflow::audit()->getRecent(limit: 50);
   ```

---

## Next Steps

- Check **SERVICES_INDEX.md** for complete service reference
- Review **ARCHITECTURE.md** for TransactionService details
- See **README.md** for installation
- Check source code PHPDoc for method signatures


