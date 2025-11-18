# AccountFlow Services Index

Complete service layer documentation for the AccountFlow accounting package.

## Overview

AccountFlow provides 8 core services covering all accounting operations:

| Service | Purpose | Namespace |
|---------|---------|-----------|
| **TransactionService** | Transaction CRUD, type normalization, account auto-resolution | `ArtflowStudio\AccountFlow\App\Services\TransactionService` |
| **AccountService** | Account management, balance tracking, statistics | `ArtflowStudio\AccountFlow\App\Services\AccountService` |
| **CategoryService** | Income/Expense category management, hierarchy | `ArtflowStudio\AccountFlow\App\Services\CategoryService` |
| **PaymentMethodService** | Payment method management, account linking | `ArtflowStudio\AccountFlow\App\Services\PaymentMethodService` |
| **BudgetService** | Budget creation, tracking, variance analysis | `ArtflowStudio\AccountFlow\App\Services\BudgetService` |
| **ReportService** | Financial reporting (P&L, Cash Flow, Reports) | `ArtflowStudio\AccountFlow\App\Services\ReportService` |
| **SettingsService** | Configuration management, defaults | `ArtflowStudio\AccountFlow\App\Services\SettingsService` |
| **AuditService** | Audit trail logging, compliance tracking | `ArtflowStudio\AccountFlow\App\Services\AuditService` |

---

## Quick Access Methods

### Using the Facade (Recommended)

```php
use ArtflowStudio\AccountFlow\Facades\AC;

// Create transactions
Accountflow::transactions()->create([...]);

// Manage accounts
Accountflow::accounts()->getBalance(1);

// View reports
Accountflow::reports()->profitAndLoss('2024-01-01', '2024-12-31');
```

### Direct Service Access

```php
use ArtflowStudio\AccountFlow\App\Services\TransactionService;

TransactionService::create([...]);
```

### Container Access

```php
app('accountflow')->transactions()->create([...]);
app(TransactionService::class)->create([...]);
```

---

## Service Documentation

### 1. TransactionService

**Location**: `src/app/Services/TransactionService.php`

Handles all transaction operations with intelligent defaults:

```php
// Create transaction
$transaction = Accountflow::transactions()->create([
    'amount' => 1000,
    'type' => 1, // 1=income, 2=expense
    'payment_method' => 1,
    // Defaults auto-filled:
    // - unique_id: Generated automatically
    // - account_id: Resolved from payment method
    // - category_id: Based on type if not provided
    // - date: Current date if not provided
    // - user_id: Current authenticated user
]);

// Create income specifically
$income = Accountflow::transactions()->createIncome([
    'amount' => 500,
    'category_id' => 2,
]);

// Create expense specifically
$expense = Accountflow::transactions()->createExpense([
    'amount' => 200,
]);

// Reverse a transaction
Accountflow::transactions()->reverse($transaction, 'Incorrect entry');

// Get statistics
$stats = Accountflow::transactions()->getSummary('2024-01-01', '2024-12-31');
```

**Key Features**:
- ✅ Auto-generates unique transaction IDs
- ✅ Type normalization (1/2, 'income'/'expense')
- ✅ Intelligent account resolution from payment method
- ✅ Category defaults based on transaction type
- ✅ Database transactions for data consistency
- ✅ Comprehensive validation

---

### 2. AccountService

**Location**: `src/app/Services/AccountService.php`

Manages accounts and balance tracking:

```php
// Create account
$account = Accountflow::accounts()->create([
    'name' => 'Checking Account',
    'description' => 'Main business account',
    'opening_balance' => 5000,
]);

// Get balance
$balance = Accountflow::accounts()->getBalance($account->id);

// Recalculate balance from transactions
$newBalance = Accountflow::accounts()->recalculateBalance($account->id);

// Get transactions for period
$transactions = Accountflow::accounts()->getTransactions(
    $account->id,
    '2024-01-01',
    '2024-12-31'
);

// Get statistics
$stats = Accountflow::accounts()->getStatistics($account->id, '2024-01-01');

// Deactivate/activate
Accountflow::accounts()->deactivate($account);
Accountflow::accounts()->activate($account);
```

**Key Features**:
- ✅ Account creation with opening balance
- ✅ Real-time balance tracking
- ✅ Balance recalculation from transactions
- ✅ Account statistics and analytics
- ✅ Status management (active/inactive)

---

### 3. CategoryService

**Location**: `src/app/Services/CategoryService.php`

Manages income/expense categories with hierarchy:

```php
// Create category
$category = Accountflow::categories()->create([
    'name' => 'Product Sales',
    'type' => 1, // 1=income, 2=expense
    'parent_id' => null, // Top-level
]);

// Create sub-category
$subcat = Accountflow::categories()->create([
    'name' => 'Online Sales',
    'type' => 1,
    'parent_id' => $category->id,
]);

// Get categories by type
$incomeCategories = Accountflow::categories()->getIncomeCategories();
$expenseCategories = Accountflow::categories()->getExpenseCategories();

// Get hierarchy
$hierarchy = Accountflow::categories()->getHierarchy(1); // type 1 = income

// Lock/unlock (privacy control)
Accountflow::categories()->lock($category);
Accountflow::categories()->unlock($category);

// Deactivate/activate
Accountflow::categories()->deactivate($category);
Accountflow::categories()->activate($category);
```

**Key Features**:
- ✅ Parent/child category hierarchy
- ✅ Type-specific queries (income/expense)
- ✅ Privacy control (locked/unlocked)
- ✅ Status management
- ✅ Validation of category relationships

---

### 4. PaymentMethodService

**Location**: `src/app/Services/PaymentMethodService.php`

Manages payment methods and account linking:

```php
// Create payment method
$method = Accountflow::paymentMethods()->create([
    'name' => 'Stripe',
    'account_id' => 1,
    'logo_icon' => 'stripe.svg',
]);

// Get active methods
$active = Accountflow::paymentMethods()->getActive();

// Get methods by account
$byAccount = Accountflow::paymentMethods()->getByAccount(1);

// Link/unlink methods
Accountflow::paymentMethods()->linkToAccount($method, 2);
Accountflow::paymentMethods()->unlinkFromAccount($method);

// Activate/deactivate
Accountflow::paymentMethods()->activate($method);
Accountflow::paymentMethods()->deactivate($method);

// Validate method
$isValid = Accountflow::paymentMethods()->validate($method->id);
```

**Key Features**:
- ✅ Payment method creation with account linking
- ✅ Multiple payment methods per account
- ✅ Status management (active/inactive)
- ✅ Account association validation
- ✅ Transaction count checks before deletion

---

### 5. BudgetService

**Location**: `src/app/Services/BudgetService.php`

Budget planning and tracking:

```php
// Create budget
$budget = Accountflow::budgets()->create([
    'account_id' => 1,
    'category_id' => 5,
    'amount' => 5000,
    'period' => 'monthly', // daily, weekly, monthly, yearly
    'alert_threshold' => 80, // Alert at 80% spending
]);

// Analyze spending vs budget
$analysis = Accountflow::budgets()->analyze($budget->id);
// Returns: budgeted, spent, remaining, percentage_used, variance, is_over_budget, is_alert

// Get active budgets
$active = Accountflow::budgets()->getActive($account->id);

// Get spending transactions
$transactions = Accountflow::budgets()->getTransactions($budget->id);

// Get budget alerts
$alerts = Accountflow::budgets()->getAlertsForAccount($account->id);

// Deactivate budget
Accountflow::budgets()->deactivate($budget);
```

**Key Features**:
- ✅ Period-based budgets (daily/weekly/monthly/yearly)
- ✅ Variance analysis (actual vs budgeted)
- ✅ Alert thresholds
- ✅ Budget status management
- ✅ Transaction tracking by budget

---

### 6. ReportService

**Location**: `src/app/Services/ReportService.php`

Comprehensive financial reporting:

```php
// Income and Expense report
$ieReport = Accountflow::reports()->incomeExpenseReport(
    '2024-01-01',
    '2024-12-31'
);
// Returns: summary, income_by_category, expense_by_category

// Profit and Loss statement
$pl = Accountflow::reports()->profitAndLoss('2024-01-01', '2024-12-31');
// Returns: revenue, expenses, profit, profit_margin, breakdowns

// Cash flow analysis
$cashflow = Accountflow::reports()->cashFlowReport('2024-01-01', '2024-12-31');
// Returns: by_month, total_inflows, total_outflows

// Balance sheet
$balance = Accountflow::reports()->balanceReport();
// Returns: accounts list with balances, total

// By payment method
$byMethod = Accountflow::reports()->byPaymentMethod('2024-01-01', '2024-12-31');

// Daily summary
$daily = Accountflow::reports()->dailySummary('2024-01-01', '2024-12-31');

// Category performance
$categories = Accountflow::reports()->categoryPerformance('2024-01-01', '2024-12-31');
```

**Key Features**:
- ✅ Multiple report types (P&L, Cash Flow, Balance)
- ✅ Date range filtering
- ✅ Category breakdown
- ✅ Payment method analysis
- ✅ Daily and period summaries

---

### 7. SettingsService

**Location**: `src/app/Services/SettingsService.php`

Configuration management:

```php
// Get a setting
$defaultType = Accountflow::settings()->get('default_transaction_type', 2);

// Set a setting
Accountflow::settings()->set('default_transaction_type', 1);

// Get all settings
$all = Accountflow::settings()->getAll();

// Get specific defaults
$defaultMethodId = Accountflow::settings()->defaultPaymentMethodId();
$defaultAccountId = Accountflow::settings()->defaultAccountId();
$defaultCategoryId = Accountflow::settings()->defaultSalesCategoryId();

// Feature toggles
Accountflow::settings()->enableFeature('multi_accounts_module');
Accountflow::settings()->disableFeature('assets_module');
$isEnabled = Accountflow::settings()->isFeatureEnabled('loan_module');
```

**Key Features**:
- ✅ Key-value configuration storage
- ✅ Type-specific value handling (string, numeric, boolean)
- ✅ Feature toggle management
- ✅ Default value system
- ✅ Fallback defaults

---

### 8. AuditService

**Location**: `src/app/Services/AuditService.php`

Audit trail and compliance logging:

```php
// Log an action
AuditService::log('transaction_created', [
    'transaction_id' => 123,
    'amount' => 1000,
], 'Transaction for Invoice #001');

// Get recent logs
$logs = Accountflow::audit()->getRecent(50);

// Get logs by user
$userLogs = Accountflow::audit()->getByUser($userId);

// Get logs by action
$created = Accountflow::audit()->getByAction('transaction_created');

// Get logs by date range
$range = Accountflow::audit()->getByDateRange('2024-01-01', '2024-12-31');

// Get summary
$summary = Accountflow::audit()->getSummary();
// Returns: total_entries, today, this_month, by_action

// Clean old logs
Accountflow::audit()->deleteOlderThan(90); // Delete logs older than 90 days

// Export logs
$export = Accountflow::audit()->export('2024-01-01', '2024-12-31');
```

**Key Features**:
- ✅ Complete action logging with user tracking
- ✅ IP address and user agent capture
- ✅ JSON detail storage
- ✅ Advanced filtering (user, action, date range)
- ✅ Data export capability
- ✅ Automatic old log cleanup

---

## Common Workflows

### Creating an Invoice with Transaction

```php
use ArtflowStudio\AccountFlow\Facades\AC;

// Create invoice (app logic)
$invoice = Invoice::create([...]);

// Create related income transaction
$transaction = Accountflow::transactions()->createIncome([
    'amount' => $invoice->total,
    'category_id' => Accountflow::settings()->defaultSalesCategoryId(),
    'description' => "Invoice #{$invoice->reference}",
    'user_id' => auth()->id(),
]);

// Log the action
Accountflow::audit()->logTransactionCreated($transaction->id, $transaction->toArray());
```

### Budget Monitoring

```php
use ArtflowStudio\AccountFlow\Facades\AC;

// Create monthly budget
$budget = Accountflow::budgets()->create([
    'account_id' => $account->id,
    'category_id' => $categoryId,
    'amount' => 5000,
    'period' => 'monthly',
    'alert_threshold' => 80,
]);

// Check for alerts
$alerts = Accountflow::budgets()->getAlertsForAccount($account->id);

if (count($alerts) > 0) {
    // Send notification
    foreach ($alerts as $alert) {
        notify_admin("Budget alert: {$alert['category_name']} is at {$alert['percentage_used']}%");
    }
}
```

### Generating Reports

```php
use ArtflowStudio\AccountFlow\Facades\AC;

$startDate = '2024-01-01';
$endDate = '2024-12-31';

// Get comprehensive report
$pl = Accountflow::reports()->profitAndLoss($startDate, $endDate);
$cashflow = Accountflow::reports()->cashFlowReport($startDate, $endDate);
$byCategory = Accountflow::reports()->categoryPerformance($startDate, $endDate);

// Export data
$data = [
    'profit_loss' => $pl,
    'cash_flow' => $cashflow,
    'categories' => $byCategory,
];

return response()->json($data);
```

---

## Error Handling

All services throw exceptions on validation errors:

```php
try {
    $transaction = Accountflow::transactions()->create([
        'amount' => 1000,
        'account_id' => 9999, // Non-existent
    ]);
} catch (\Exception $e) {
    // "Account #9999 not found"
    Log::error($e->getMessage());
}
```

---

## Best Practices

1. **Always use services instead of direct model creation**
   ```php
   // ❌ Bad
   Transaction::create($data);
   
   // ✅ Good
   Accountflow::transactions()->create($data);
   ```

2. **Use specific methods when available**
   ```php
   // ❌ Less clear
   Accountflow::transactions()->create(['type' => 1, ...]);
   
   // ✅ Better
   Accountflow::transactions()->createIncome([...]);
   ```

3. **Log important actions**
   ```php
   $transaction = Accountflow::transactions()->create([...]);
   Accountflow::audit()->logTransactionCreated($transaction->id, $transaction->toArray());
   ```

4. **Handle exceptions properly**
   ```php
   try {
       Accountflow::accounts()->delete($account);
   } catch (\Exception $e) {
       return response()->json(['error' => $e->getMessage()], 422);
   }
   ```

5. **Use facades for cleaner code**
   ```php
   // Instead of: app(TransactionService::class)->create(...)
   // Use: Accountflow::transactions()->create(...)
   ```

---

## Architecture Benefits

| Benefit | Description |
|---------|-------------|
| **Reusability** | Services can be used across multiple components/controllers |
| **Testability** | Services can be easily mocked and unit tested |
| **Maintainability** | Business logic is centralized and not scattered |
| **Consistency** | All parts of the app use the same logic |
| **Validation** | Built-in validation on all operations |
| **Error Handling** | Consistent exception handling |
| **Transactions** | Database-level consistency via `DB::transaction()` |
| **Auditing** | Easy to log all important actions |

---

## Related Documentation

- **ARCHITECTURE.md** - TransactionService detailed architecture
- **TRANSACTION_SERVICE_USAGE.md** - TransactionService usage examples
- **README.md** - Package overview and installation
- **composer.json** - Service provider registration

---

## Support

For issues or questions:
- Check ARCHITECTURE.md for detailed information
- Review examples in this document
- Check related service documentation
- Review source code comments and PHPDoc blocks


