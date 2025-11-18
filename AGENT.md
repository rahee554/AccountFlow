# 🤖 AccountFlow Package - Comprehensive Agent Guide

**Version**: 3.0.0  
**Status**: ✅ Production Ready & Fully Tested  
**Last Updated**: November 18, 2025

This document comprehensively explains the AccountFlow package architecture, API usage, and how to extend it effectively.

---

## 📋 Table of Contents

1. [Package Overview](#-package-overview)
2. [Directory Structure](#-directory-structure)
3. [Using the Facade API](#-using-the-facade-api)
4. [Working with Services](#-working-with-services)
5. [Livewire Components](#-livewire-components)
6. [Database Models](#-database-models)
7. [Blade Directives & Middleware](#-blade-directives--middleware)
8. [Artisan Commands](#-artisan-commands)
9. [Making Changes](#-making-changes)
10. [Common Tasks](#-common-tasks)
11. [Testing & Debugging](#-testing--debugging)
12. [Troubleshooting](#-troubleshooting)

---

## 🎯 Package Overview

### What It Does
AccountFlow is a **complete, production-ready accounting system** that handles:
- Multi-account financial tracking
- Transaction management (Income, Expense, Transfer)
- Budget planning and tracking
- Asset and loan management
- Financial reporting and analysis
- Audit logging of all changes

### Technology Stack
- **Laravel 12** with Livewire 3
- **Flux UI** for beautiful components
- **Eloquent ORM** for database interaction
- **Tailwind CSS v4** for styling

### Key Characteristics
- ✅ Reusable across multiple Laravel projects
- ✅ Feature toggles for selective functionality
- ✅ Complete permission system
- ✅ Fully tested and production-ready
- ✅ Extensible architecture

---

## 📁 Directory Structure

```
vendor/artflow-studio/accountflow/
│
├── src/
│   ├── app/
│   │   ├── Console/
│   │   │   ├── InstallCommand.php              ← Initialize package
│   │   │   ├── LinkCommand.php                 ← Link package files
│   │   │   ├── SyncCommand.php                 ← Sync changes
│   │   │   ├── MigrateCommand.php              ← Run migrations
│   │   │   ├── MigrateFreshCommand.php         ← Fresh install
│   │   │   ├── SeedCommand.php                 ← Seed demo data
│   │   │   ├── FeatureCommand.php              ← Manage features
│   │   │   └── TestCommand.php                 ← Test package
│   │   │
│   │   ├── Http/
│   │   │   └── Controllers/
│   │   │       ├── AccountsController.php      ← Account management
│   │   │       └── DefaultController.php       ← Default routes
│   │   │
│   │   ├── Livewire/
│   │   │   └── AccountFlow/
│   │   │       ├── AccountsDashboard.php       ← Main dashboard
│   │   │       ├── Accounts/
│   │   │       │   ├── AccountsList.php        ← Account list
│   │   │       │   └── AccountForm.php         ← Create/edit form
│   │   │       ├── Transactions/
│   │   │       │   ├── TransactionsList.php    ← View transactions
│   │   │       │   └── TransactionForm.php     ← Create form
│   │   │       ├── Budgets/
│   │   │       │   ├── BudgetsList.php
│   │   │       │   └── BudgetForm.php
│   │   │       ├── Reports/
│   │   │       │   ├── ProfitLossReport.php
│   │   │       │   ├── TrialBalanceReport.php
│   │   │       │   └── CashbookReport.php
│   │   │       ├── Loans/
│   │   │       ├── Assets/
│   │   │       ├── Equity/
│   │   │       ├── AuditTrail/
│   │   │       ├── PaymentMethod/
│   │   │       ├── Categories/
│   │   │       ├── Wallets/
│   │   │       ├── PlannedPayments/
│   │   │       └── Settings.php                ← Settings panel
│   │   │
│   │   ├── Models/
│   │   │   ├── Account.php                     ← Main account model
│   │   │   ├── Transaction.php                 ← Transaction model
│   │   │   ├── Transfer.php                    ← Account transfer
│   │   │   ├── Budget.php                      ← Budget model
│   │   │   ├── Loan.php, LoanTransaction.php
│   │   │   ├── Asset.php, AssetTransaction.php
│   │   │   ├── Category.php                    ← Category model
│   │   │   ├── PaymentMethod.php               ← Payment methods
│   │   │   ├── UserWallet.php                  ← User wallets
│   │   │   ├── EquityPartner.php
│   │   │   ├── AuditTrail.php                  ← Audit logging
│   │   │   ├── Setting.php                     ← Settings model
│   │   │   ├── TransactionTemplate.php
│   │   │   ├── PlannedPayment.php
│   │   │   └── [Other models...]
│   │   │
│   │   ├── Services/
│   │   │   ├── TransactionService.php          ← Transaction operations
│   │   │   ├── AccountService.php              ← Account operations
│   │   │   ├── SettingsService.php             ← Settings operations
│   │   │   ├── FeatureService.php              ← Feature management
│   │   │   ├── AuditService.php                ← Audit logging
│   │   │   ├── CategoryService.php
│   │   │   ├── PaymentMethodService.php
│   │   │   ├── BudgetService.php
│   │   │   └── ReportService.php
│   │   │
│   │   ├── Facades/
│   │   │   └── Accountflow.php                 ← Main facade
│   │   │
│   │   └── Support/
│   │       └── AccountflowServiceProvider.php  ← Service provider
│   │
│   ├── config/
│   │   └── accountflow.php                     ← Configuration
│   │
│   ├── database/
│   │   ├── migrations/
│   │   │   ├── 0001_create_accounts_table.php
│   │   │   ├── 0002_create_transactions_table.php
│   │   │   └── [Other migrations...]
│   │   └── seeders/
│   │       └── AccountFlowSeeder.php           ← Demo data
│   │
│   ├── resources/
│   │   └── views/
│   │       └── vendor/artflow-studio/accountflow/
│   │           ├── blades/                     ← Blade templates
│   │           └── livewire/                   ← Livewire views
│   │               ├── accounts/
│   │               ├── transactions/
│   │               ├── reports/
│   │               └── [Other views...]
│   │
│   ├── routes/
│   │   └── accountflow.php                     ← All routes defined
│   │
│   └── AccountFlowServiceProvider.php          ← Main provider
│
├── docs/
│   ├── QUICK_REFERENCE.md                      ← API cheat sheet
│   ├── SERVICES_INDEX.md                       ← Complete API
│   └── [Other documentation]
│
├── tests/
│   ├── Feature/
│   ├── Unit/
│   └── [Test files...]
│
├── README.md                                   ← Package overview
├── AGENT.md                                    ← This file
├── composer.json                               ← Dependencies
└── PRODUCTION_FEATURES.md                      ← Latest features
```

---

## 🎯 Using the Facade API

The **Facade** is the primary way agents interact with AccountFlow. It provides a clean, fluent API for all operations.

### 1. Transaction Operations

#### Create Income Transaction
```php
use ArtflowStudio\AccountFlow\Facades\Accountflow;

$transaction = Accountflow::transactions()->createIncome([
    'amount' => 1000,
    'description' => 'Client Payment',
    'category_id' => 2,           // Required
    'account_id' => 1,            // Required
    'payment_method_id' => 1,     // Optional
    'date' => now(),              // Defaults to now()
    'reference' => 'INV-001',     // Optional
]);

// Returns: Transaction model with all attributes
```

#### Create Expense Transaction
```php
$transaction = Accountflow::transactions()->createExpense([
    'amount' => 500,
    'description' => 'Office Supplies',
    'category_id' => 5,
    'account_id' => 1,
    'date' => now(),
]);
```

#### Create Transfer Between Accounts
```php
$transfer = Accountflow::transactions()->transfer([
    'from_account_id' => 1,
    'to_account_id' => 2,
    'amount' => 1000,
    'description' => 'Fund transfer',
    'date' => now(),
]);
```

#### Update Transaction
```php
$transaction = Accountflow::transactions()->update($id, [
    'amount' => 1200,
    'description' => 'Updated description',
]);
```

#### Delete Transaction
```php
Accountflow::transactions()->delete($id);
// Automatically adjusts balances
```

#### Get Transaction Summary
```php
$summary = Accountflow::transactions()->getSummary(
    start: now()->startOfMonth(),
    end: now()->endOfMonth(),
    accountId: 1  // Optional filter
);

// Returns: ['income' => ..., 'expense' => ..., 'net' => ...]
```

### 2. Account Operations

#### Create Account
```php
$account = Accountflow::accounts()->create([
    'name' => 'Business Bank Account',
    'type' => 'bank',              // bank, cash, wallet, etc.
    'code' => 'ACC-001',           // Unique code
    'opening_balance' => 10000,    // Starting balance
]);
```

#### Get All Accounts
```php
$accounts = Accountflow::accounts()->getAll();
// Returns collection of Account models
```

#### Get Account Balance
```php
$balance = Accountflow::accounts()->getBalance($accountId);
// Returns: 5250.50
```

#### Adjust Account Balance
```php
// Add to balance
Accountflow::accounts()->addToBalance($accountId, 1000);

// Subtract from balance
Accountflow::accounts()->subtractFromBalance($accountId, 500);
```

### 3. Feature Management

#### Check if Feature is Enabled
```php
if (Accountflow::features()->isEnabled('audit')) {
    // Audit feature is active
}
```

#### Enable Feature
```php
Accountflow::features()->enable('budgets');
// Now budgets module is available
```

#### Disable Feature
```php
Accountflow::features()->disable('loan_module');
// Loan features are hidden
```

#### Get All Features
```php
$features = Accountflow::features()->getAllFeatures();
// Returns array of all features with enabled/disabled status
```

### 4. Category Operations

#### Get All Categories
```php
$categories = Accountflow::categories()->getAll();
// Hierarchical category structure
```

#### Get Categories by Type
```php
$incomeCategories = Accountflow::categories()->getByType('income');
$expenseCategories = Accountflow::categories()->getByType('expense');
```

### 5. Settings Operations

#### Get Setting
```php
$value = Accountflow::settings()->get('default_account_id', 1);
// Returns: 1 (or default value if not found)
```

#### Set Setting
```php
Accountflow::settings()->set('business_name', 'Acme Corp');
// Now stored in database
```

#### Get Default Categories
```php
$salesCategoryId = Accountflow::settings()->defaultSalesCategoryId();
$expenseCategoryId = Accountflow::settings()->defaultExpenseCategoryId();
```

### 6. Audit Operations

#### Log Custom Event
```php
Accountflow::audit()->log(
    action: 'transaction_approved',
    modelType: 'Transaction',
    modelId: $transaction->id,
    oldValue: null,
    newValue: $transaction->toArray()
);
```

#### Log Transaction Created
```php
Accountflow::audit()->logTransactionCreated(
    $transaction->id,
    $transaction->toArray()
);
```

#### Get Recent Audit Logs
```php
$logs = Accountflow::audit()->getRecent(50);
// Last 50 audit trail entries
```

#### Get Audit Logs by User
```php
$logs = Accountflow::audit()->getByUser($userId);
// All actions by specific user
```

### 7. Report Operations

#### Get Profit & Loss Report
```php
$report = Accountflow::reports()->profitAndLoss(
    start: now()->startOfYear(),
    end: now()->endOfYear()
);

// Returns: ['income' => ..., 'expenses' => ..., 'net_profit' => ...]
```

#### Get Trial Balance
```php
$report = Accountflow::reports()->trialBalance(
    date: now()
);

// Returns: Balanced debit/credit columns
```

#### Get Cashbook Report
```php
$report = Accountflow::reports()->cashbook(
    accountId: 1,
    start: now()->startOfMonth(),
    end: now()->endOfMonth()
);

// Returns: Daily cash flow
```

---

## 🔧 Working with Services

### What Are Services?

Services are business logic classes that handle operations. Access them via the Facade.

### Available Services

| Service | Purpose | Access |
|---------|---------|--------|
| TransactionService | Create/read/update/delete transactions | `Accountflow::transactions()` |
| AccountService | Manage accounts | `Accountflow::accounts()` |
| CategoryService | Manage categories | `Accountflow::categories()` |
| FeatureService | Enable/disable features | `Accountflow::features()` |
| SettingsService | Store/retrieve settings | `Accountflow::settings()` |
| AuditService | Log and retrieve audit trails | `Accountflow::audit()` |
| ReportService | Generate financial reports | `Accountflow::reports()` |
| BudgetService | Manage budgets | `Accountflow::budgets()` |
| PaymentMethodService | Manage payment methods | `Accountflow::paymentMethods()` |

### Complete Example: Create Transaction with Audit

```php
use ArtflowStudio\AccountFlow\Facades\Accountflow;

public function processPayment($paymentData)
{
    // Enable audit if needed
    if (!Accountflow::features()->isEnabled('audit_trail')) {
        Accountflow::features()->enable('audit_trail');
    }

    // Create transaction
    $transaction = Accountflow::transactions()->createIncome([
        'amount' => $paymentData['amount'],
        'description' => $paymentData['description'],
        'category_id' => Accountflow::settings()->defaultSalesCategoryId(),
        'account_id' => 1,
        'date' => now(),
    ]);

    // Log the transaction
    Accountflow::audit()->logTransactionCreated(
        $transaction->id,
        $transaction->toArray()
    );

    // Update account balance
    Accountflow::accounts()->addToBalance(
        $transaction->account_id,
        $transaction->amount
    );

    // Store setting for next time
    Accountflow::settings()->set('last_transaction_date', now());

    return $transaction;
}
```

---

## 🎨 Livewire Components

### Available Components

All components are in `app/Livewire/AccountFlow/` namespace.

#### 1. AccountsDashboard
The main dashboard showing account overview, recent transactions, balances.

```blade
<!-- In your view -->
<livewire:account-flow.accounts-dashboard />
```

#### 2. AccountsList
Display all accounts in a table/list with filtering.

```blade
<livewire:account-flow.accounts.accounts-list />
```

#### 3. TransactionsList
Display transactions with filters, search, and pagination.

```blade
<livewire:account-flow.transactions.transactions-list :accountId="1" />
```

#### 4. TransactionForm
Create or edit a transaction (income, expense, or transfer).

```blade
<!-- Create new transaction -->
<livewire:account-flow.transactions.transaction-form />

<!-- Edit existing -->
<livewire:account-flow.transactions.transaction-form :transaction="$transaction" />
```

#### 5. Reports Components
- `ProfitLossReport.php` - P&L statement
- `TrialBalanceReport.php` - Trial balance
- `CashbookReport.php` - Cash flow

```blade
<livewire:account-flow.reports.profit-loss-report />
<livewire:account-flow.reports.trial-balance-report />
<livewire:account-flow.reports.cashbook-report :accountId="1" />
```

#### 6. CategoriesList
Manage income/expense categories.

```blade
<livewire:account-flow.categories.categories-list />
```

#### 7. Settings
Configure AccountFlow settings.

```blade
<livewire:account-flow.settings />
```

### Creating Custom Components

#### Step 1: Create Component Class
```php
namespace App\Livewire\AccountFlow;

use Livewire\Component;
use ArtflowStudio\AccountFlow\Facades\Accountflow;

class MyCustomComponent extends Component
{
    public function mount()
    {
        $this->accounts = Accountflow::accounts()->getAll();
    }

    public function render()
    {
        return view('livewire.account-flow.my-custom-component');
    }
}
```

#### Step 2: Create View
```blade
<!-- resources/views/livewire/account-flow/my-custom-component.blade.php -->
<div class="p-6">
    <h2 class="text-2xl font-bold mb-4">My Custom Component</h2>
    
    @foreach ($accounts as $account)
        <div class="mb-4 p-4 border rounded">
            <h3>{{ $account->name }}</h3>
            <p>Balance: ${{ $account->balance }}</p>
        </div>
    @endforeach
</div>
```

#### Step 3: Register Route
```php
// In routes/accountflow.php or web.php
Route::get('/custom-page', MyCustomComponent::class);
```

---

## 📊 Database Models

### Key Models and Usage

#### Account Model
```php
// Get account with relationships
$account = Account::with('transactions', 'budget')->find($id);

// Get balance
$balance = $account->getBalance();

// Query accounts
$bankAccounts = Account::where('type', 'bank')->get();
```

#### Transaction Model
```php
// Get recent transactions
$transactions = Transaction::latest()->limit(10)->get();

// Filter by type
$income = Transaction::where('type', 'income')->sum('amount');

// With relationships
$transactions = Transaction::with('account', 'category', 'paymentMethod')->get();
```

#### Category Model
```php
// Get by type
$expenseCategories = Category::where('type', 'expense')->get();

// Hierarchical query
$category = Category::with('children')->find($id);
```

---

## 🎨 Blade Directives & Middleware

### Feature Directives

#### Show If Feature Enabled
```blade
@featureEnabled('audit')
    <a href="/audit-trail">View Audit Trail</a>
@endFeatureEnabled
```

#### Show If Feature Disabled
```blade
@featureDisabled('budgets')
    <div class="alert">Budgets feature is not available</div>
@endFeatureDisabled
```

### Middleware Protection

#### Protect Routes
```php
Route::get('/audit', AuditController::class)
    ->middleware('accountflow.feature:audit');

Route::middleware('accountflow.feature:budgets')->group(function () {
    Route::get('/budgets', [BudgetController::class, 'index']);
    Route::post('/budgets', [BudgetController::class, 'store']);
});
```

---

## 🛠️ Artisan Commands

### Installation Commands

```bash
# Initialize package
php artisan accountflow:install

# Link package files
php artisan accountflow:link [--force]

# Publish configuration
php artisan vendor:publish --tag=accountflow-config
```

### Database Commands

```bash
# Run migrations
php artisan accountflow:migrate

# Fresh migration with seeding (development only!)
php artisan accountflow:migrate:fresh --seed

# Seed demo data
php artisan accountflow:seed
```

### Feature Management

```bash
# Enable feature
php artisan accountflow:feature audit enable

# Disable feature
php artisan accountflow:feature budgets disable

# List features
php artisan accountflow:feature list
```

### File Synchronization

```bash
# Check changes (no modifications)
php artisan accountflow:sync --check

# Interactive sync
php artisan accountflow:sync

# Force sync
php artisan accountflow:sync --force
```

### Testing Commands

```bash
# Test complete package
php artisan accountflow:test-complete

# Test facade
php artisan accountflow:test-facade

# Test features
php artisan accountflow:test-features

# Check status
php artisan accountflow:status
```

---

## 📝 Making Changes

### Where to Edit

**For package features**: Edit in `vendor/artflow-studio/accountflow/src/`  
**For project customizations**: Edit in `app/Livewire/AccountFlow/`, `app/Models/AccountFlow/`, etc.

### Change Workflow

```
1. Make change in appropriate location
   ↓
2. Run: php artisan accountflow:sync --force
   ↓
3. Test in browser
   ↓
4. Run tests: php artisan test
   ↓
5. Format code: vendor/bin/pint --dirty
```

### Important Notes

⚠️ **Never modify migrations** - This package is used in production  
✅ **Safe to modify**: Components, models (add methods), views, routes, config

---

## 📋 Common Tasks

### Create New Transaction
```php
use ArtflowStudio\AccountFlow\Facades\Accountflow;

$transaction = Accountflow::transactions()->createIncome([
    'amount' => 1500,
    'description' => 'Client Payment',
    'category_id' => 1,
    'account_id' => 1,
    'date' => now(),
]);
```

### Get Account Balance
```php
$balance = Accountflow::accounts()->getBalance($accountId);
```

### Generate P&L Report
```php
$report = Accountflow::reports()->profitAndLoss(
    now()->startOfYear(),
    now()->endOfYear()
);
```

### Enable Feature
```php
Accountflow::features()->enable('loan_module');
```

### Create Custom Component
```bash
# Create Livewire component
php artisan make:livewire AccountFlow/MyReport

# Then use the facade inside
```

### Transfer Money Between Accounts
```php
$transfer = Accountflow::transactions()->transfer([
    'from_account_id' => 1,
    'to_account_id' => 2,
    'amount' => 5000,
    'description' => 'Fund transfer',
]);
```

---

## 🧪 Testing & Debugging

### Run Tests
```bash
# All tests
php artisan test

# Specific test
php artisan test --filter testCreateTransaction

# With coverage
php artisan test --coverage
```

### Debug with Tinker
```bash
php artisan tinker

# Inside tinker
use ArtflowStudio\AccountFlow\Facades\Accountflow;
$accounts = Accountflow::accounts()->getAll();
dd($accounts);
```

### Check Status
```bash
php artisan accountflow:status
```

---

## 🐛 Troubleshooting

### Facade Not Resolving
```php
// Make sure to import
use ArtflowStudio\AccountFlow\Facades\Accountflow;

// Check status
php artisan accountflow:status
```

### Balance Not Updating
```bash
# Manually recalculate
php artisan tinker
>>> $account = Account::find(1);
>>> $balance = $account->transactions()->sum('amount');
>>> $account->update(['balance' => $balance]);
```

### Component Not Loading
```bash
# Sync files
php artisan accountflow:sync --force

# Clear caches
php artisan cache:clear
php artisan view:clear
```

### Features Not Visible
```bash
# Republish views
php artisan vendor:publish --tag=accountflow-views --force

# Clear view cache
php artisan view:clear

# Enable feature
php artisan accountflow:feature audit enable
```

---

## 🚀 Best Practices

### ✅ DO
- Use the Facade for all operations
- Check features before using them
- Log important operations to audit trail
- Use Eloquent for querying
- Validate input before processing
- Run tests after changes
- Format code with Pint

### ❌ DON'T
- Modify migrations directly
- Use raw SQL queries
- Skip validation
- Create duplicate accounts
- Disable audit in production
- Hardcode category IDs
- Skip syncing after changes

---

## 📚 Quick Reference

### Facade Methods
```php
Accountflow::transactions()->createIncome($data)
Accountflow::transactions()->createExpense($data)
Accountflow::transactions()->transfer($data)
Accountflow::accounts()->create($data)
Accountflow::accounts()->getBalance($id)
Accountflow::features()->isEnabled('feature')
Accountflow::features()->enable('feature')
Accountflow::settings()->get('key', 'default')
Accountflow::audit()->log($action, $model, $id, $old, $new)
Accountflow::reports()->profitAndLoss($start, $end)
```

### Blade Directives
```blade
@featureEnabled('audit')...@endFeatureEnabled
@featureDisabled('budgets')...@endFeatureDisabled
```

### Middleware
```php
->middleware('accountflow.feature:audit')
->middleware('accountflow.feature:budgets')
```

---

**Version**: 3.0.0 | **Status**: ✅ Production Ready | **Last Updated**: November 18, 2025

