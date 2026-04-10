---
name: accountflow-development
description: "Use for ANY task involving the AccountFlow package (artflow-studio/accountflow). Activate when the user mentions: AccountFlow transactions, accounts, equity, equity partners, loans, assets, budgets, planned payments, cashbook, P&L, profit and loss, balance sheet, trial balance, financial summary, the Accountflow:: facade, AC:: facade, @accountflow directive, @featureEnabled, @featureDisabled, accountflow config keys, ac_ database tables, AccountFlowManager, FeatureService, ReportService, TransactionService, AccountsList, TransfersList, CategoriesList, or any Livewire components in app/Livewire/AccountFlow/. Also activate for: embedding AccountFlow tables in other pages, enabling/disabling AccountFlow features, currency display in AccountFlow, debugging AccountFlow Livewire standalone mode, AccountFlow route registration, or adding new reports/components to AccountFlow. Do NOT use for tenancy, general Livewire unrelated to AccountFlow, or non-accounting features."
license: MIT
metadata:
  author: artflow-studio
---

# AccountFlow Development Skill

## Package Identity

- **Composer package:** `artflow-studio/accountflow`
- **PHP namespace root:** `ArtflowStudio\AccountFlow`
- **Source:** `vendor/artflow-studio/accountflow/src/`
- **Stack:** Laravel 12+, Livewire 4, PHP 8.4

---

## Architecture

### Class Loading — SPL Autoloader (no symlinks required)

`AccountFlowServiceProvider::register()` registers an SPL autoloader that maps three namespaces directly from the package `src/` directory:

| App Namespace | Package Source |
|---|---|
| `App\Livewire\AccountFlow\*` | `vendor/artflow-studio/accountflow/src/app/Livewire/AccountFlow/` |
| `App\Models\AccountFlow\*` | `vendor/artflow-studio/accountflow/src/app/Models/` |
| `App\Http\Controllers\AccountFlow\*` | `vendor/artflow-studio/accountflow/src/app/Http/Controllers/AccountFlow/` |

This means classes are discovered at runtime from the package source — **no symlinks or junctions are needed** in production or after a fresh `composer install`.

### Development Linking (optional)

For active development where you want your IDE to index files under `app/`, you can create junctions:

```bash
php artisan accountflow:link     # Creates junctions from app/ → package src/
php artisan accountflow:delink   # Removes junctions (uses rmdir on Windows, unlink on Unix)
```

With or without junctions, **always edit the package source** — `vendor/artflow-studio/accountflow/src/`.

### View Resolution

Views are registered under the `accountflow::` namespace via `loadViewsFrom()`. The ServiceProvider also publishes them:

```
accountflow::layout.app          → src/resources/views/.../layout/app.blade.php
accountflow::livewire.transactions → src/resources/views/.../livewire/transactions.blade.php
accountflow::components.table    → src/resources/views/.../components/table.blade.php
```

To override a view in the host app:
```bash
php artisan vendor:publish --tag=accountflow-views
# Views published to: resources/views/vendor/accountflow/
```

### Package Structure

```
src/
├── AccountFlowServiceProvider.php   # Boots: views, routes, middleware, commands, Blade directives, SPL autoloader
├── Facades/
│   ├── Accountflow.php              # Primary facade → AccountFlowManager
│   └── AC.php                       # Short alias → same binding
├── Services/
│   └── AccountFlowManager.php       # Gateway: 9 service accessors
├── config/
│   └── accountflow.php              # Package default config
├── routes/
│   └── accountflow.php              # All named routes
├── app/
│   ├── Services/                    # TransactionService, AccountService, ... (9 services)
│   ├── Livewire/AccountFlow/        # ~42 Livewire components
│   ├── Models/                      # 20 Eloquent models (ac_* tables)
│   ├── Http/Controllers/AccountFlow/ # Thin controllers
│   └── Console/Commands/            # All artisan commands
└── resources/views/vendor/artflow-studio/accountflow/
    ├── layout/                      # app.blade.php, print.blade.php
    ├── livewire/                    # One view per Livewire component
    └── components/                  # table.blade.php (embed component)
```

---

## Facades — Both Aliases Are Available

The package ships with **two facades** that resolve to the same `AccountFlowManager`:

```php
// Primary alias — verbose, self-documenting
use ArtflowStudio\AccountFlow\Facades\Accountflow;
Accountflow::transactions()->createIncome([...]);

// Short alias — convenient for dense code
use ArtflowStudio\AccountFlow\Facades\AC;
AC::transactions()->createIncome([...]);
```

Both `Accountflow::` and `AC::` are registered in the container under the key `accountflow` → `AccountFlowManager`.

---

## AccountFlowManager — Service Accessors

```php
Accountflow::transactions()    // TransactionService
Accountflow::accounts()        // AccountService
Accountflow::categories()      // CategoryService
Accountflow::paymentMethods()  // PaymentMethodService
Accountflow::budgets()         // BudgetService
Accountflow::reports()         // ReportService
Accountflow::settings()        // SettingsService
Accountflow::audit()           // AuditService
Accountflow::features()        // FeatureService
```

---

## TransactionService — Complete API

All methods are **static** on `TransactionService`. Access via facade: `Accountflow::transactions()->methodName()`.

### Transaction Types
- `1` = Income (adds to account balance)
- `2` = Expense (subtracts from account balance)
- Also accepts string `'income'` / `'expense'` — auto-normalised

### Creating Transactions

```php
use ArtflowStudio\AccountFlow\Facades\Accountflow;

// ── Full create with all options ──────────────────────────────────────────
$transaction = Accountflow::transactions()->create([
    'amount'         => 1500.00,    // required — must be > 0
    'type'           => 1,          // required — 1=income, 2=expense (or 'income'/'expense')
    'payment_method' => 3,          // optional — auto-resolves account_id from PaymentMethod
    'account_id'     => 2,          // optional — defaults via payment_method or Setting default
    'category_id'    => 5,          // optional — defaults to type-based default category
    'date'           => '2026-04-09', // optional — Carbon-parseable, defaults to now()
    'description'    => 'April rent', // optional
    'reference'      => 'INV-001',  // optional
    'user_id'        => 1,          // optional — defaults to auth()->id()
]);

// ── Income shorthand ────────────────────────────────────────────────────
$income = Accountflow::transactions()->createIncome([
    'amount'      => 5000,
    'description' => 'Product sale',
    'category_id' => 2,
]);

// ── Expense shorthand ───────────────────────────────────────────────────
$expense = Accountflow::transactions()->createExpense([
    'amount'      => 250,
    'description' => 'Office supplies',
    'category_id' => 8,
    'account_id'  => 1,
]);

// ── Batch create (DB transaction, all-or-nothing) ───────────────────────
$transactions = Accountflow::transactions()->createBatch([
    ['amount' => 1000, 'type' => 1, 'description' => 'Sale A'],
    ['amount' =>  200, 'type' => 2, 'description' => 'Expense B'],
]);

// ── Update a transaction ─────────────────────────────────────────────────
// Balance is automatically reversed then reapplied — no manual math needed
$updated = Accountflow::transactions()->update($transaction, [
    'amount'      => 1800,
    'description' => 'Updated April rent',
]);

// ── Short alias (same result) ────────────────────────────────────────────
use ArtflowStudio\AccountFlow\Facades\AC;

$tx = AC::transactions()->createExpense(['amount' => 100, 'description' => 'Coffee']);
```

### Side Effects (automatic — no manual steps needed)
- On **create**: `AccountService::addToBalance()` or `subtractFromBalance()` is called automatically.
- On **update**: Previous balance change is reversed, new one is applied.
- `unique_id` is auto-generated.
- `category_id` falls back to the type-based default from `SettingsService`.
- `account_id` is auto-resolved from `payment_method` if not provided.

---

## AccountService — Complete API

```php
// Create account
$account = Accountflow::accounts()->create([
    'name'            => 'Main Business Account',
    'description'     => 'Primary bank account',
    'opening_balance' => 10000.00,
    'active'          => true,
]);

// Update account
$account = Accountflow::accounts()->update($account, ['name' => 'Petty Cash']);

// Get current balance (from balance column, not recalculated)
$balance = Accountflow::accounts()->getBalance($account->id);

// Recalculate balance from scratch (opening_balance + all transactions)
$balance = Accountflow::accounts()->recalculateBalance($account->id);

// Add to balance directly (used internally by TransactionService)
Accountflow::accounts()->addToBalance($account->id, 500.00);

// Subtract from balance directly
Accountflow::accounts()->subtractFromBalance($account->id, 250.00);

// Get transactions for an account
$txns = Accountflow::accounts()->getTransactions(
    accountId: $account->id,
    startDate: '2026-01-01',
    endDate:   '2026-12-31',
    limit:     50
);
```

---

## CategoryService — Complete API

```php
// Create income category
$category = Accountflow::categories()->create([
    'name'      => 'Product Sales',
    'type'      => 1,           // 1=income, 2=expense
    'parent_id' => null,        // null = top-level category
    'privacy'   => 1,           // 1=locked, 2=unlocked
    'icon'      => null,
    'status'    => 1,           // 1=active, 2=inactive
]);

// Get categories by type
$incomeCategories  = Accountflow::categories()->getByType(1);
$expenseCategories = Accountflow::categories()->getByType(2);

// Update category
$category = Accountflow::categories()->update($category, ['name' => 'Services']);
```

---

## PaymentMethodService — Complete API

```php
// Create payment method
$method = Accountflow::paymentMethods()->create([
    'name'       => 'Stripe',
    'account_id' => 1,        // links this method to an account
    'logo_icon'  => 'stripe',
    'info'       => 'Online card payments',
    'status'     => 1,        // 1=active, 2=inactive
]);

// Get all active methods
$methods = Accountflow::paymentMethods()->getActive();

// Update
$method = Accountflow::paymentMethods()->update($method, ['name' => 'Stripe Gateway']);
```

---

## BudgetService — Complete API

```php
// Create budget
$budget = Accountflow::budgets()->create([
    'account_id'      => 1,
    'category_id'     => 5,
    'amount'          => 5000.00,
    'period'          => 'monthly',  // daily, weekly, monthly, yearly
    'start_date'      => '2026-01-01',
    'end_date'        => '2026-12-31',
    'alert_threshold' => 80,         // alert at 80% spent
    'notes'           => 'Marketing budget',
    'status'          => 1,          // 1=active, 2=inactive
]);

// Update budget
$budget = Accountflow::budgets()->update($budget, ['amount' => 6000, 'alert_threshold' => 70]);
```

---

## ReportService — Complete API

```php
// Income + expense report with category breakdown
$report = Accountflow::reports()->incomeExpenseReport(
    startDate: '2026-01-01',
    endDate:   '2026-12-31',
    accountId: null            // null = all accounts
);
// Returns: summary (total_income, total_expense, net_income), income_by_category, expense_by_category

// Profit & loss
$pl = Accountflow::reports()->profitAndLoss('2026-01-01', '2026-12-31');
// Returns: revenue, expenses, profit, profit_margin, revenue_breakdown, expense_breakdown

// Cash flow (monthly inflows/outflows)
$cashflow = Accountflow::reports()->cashFlowReport('2026-01-01', '2026-12-31');
// Returns: by_month (month, inflows, outflows, net_cash_flow), total_inflows, total_outflows

// Balance report (per account)
$balances = Accountflow::reports()->balanceReport();
// Returns: accounts (id, name, balance, opening_balance), total_balance

// Transactions grouped by payment method
$byMethod = Accountflow::reports()->byPaymentMethod('2026-01-01', '2026-12-31');
```

---

## SettingsService — Complete API

All settings are stored in the `ac_settings` database table (key/value/type rows).

```php
// Read a setting
$value = Accountflow::settings()->get('currency', 'USD');
$value = Accountflow::settings()->get('default_transaction_type', 2);

// Write a setting
Accountflow::settings()->set('currency', 'PKR');
Accountflow::settings()->set('default_transaction_type', 1, 2); // type 2 = numeric

// Convenience getters
$defaultPaymentMethodId  = Accountflow::settings()->defaultPaymentMethodId();
$defaultSalesCategoryId  = Accountflow::settings()->defaultSalesCategoryId();
$defaultExpenseCategoryId = Accountflow::settings()->defaultExpenseCategoryId();

// Get all settings as array
$all = Accountflow::settings()->getAll();
```

---

## FeatureService — Complete API

```php
// Check if a feature is currently enabled/disabled
Accountflow::features()->isEnabled('audit');      // bool
Accountflow::features()->isDisabled('budgets');   // bool

// Enable / disable programmatically
Accountflow::features()->enable('audit');
Accountflow::features()->disable('budgets');
Accountflow::features()->toggle('categories', 'enabled');

// Get all features and their status
$features = Accountflow::features()->getAllFeatures();
// Returns: [ 'audit_trail' => ['name'=>..., 'enabled'=>true, 'key'=>...], ... ]
```

### Feature Keys (for `isEnabled`, `enable`, `disable`, middleware, and Blade directives)

| Short key | DB key | Description |
|---|---|---|
| `audit` | `audit_trail` | Audit trail logging |
| `budgets` | `budgets_module` | Budgets management |
| `planned_payments` | `planned_payments_module` | Recurring planned payments |
| `assets` | `assets_module` | Assets management |
| `loans` | `loan_module` | Loans management |
| `wallets` | `user_wallet_module` | User wallets |
| `equity` | `equity_module` | Equity partners |
| `cashbook` | `cashbook_module` | Cashbook report |
| `multi_accounts` | `multi_accounts_module` | Multi-account support |
| `templates` | `transaction_templates` | Transaction templates |
| `payment_methods` | `payment_methods_module` | Payment methods |
| `categories` | `categories_module` | Custom categories |
| `transfers` | `transfers_module` | Account transfers |
| `profit_loss` | `profit_loss_report` | P&L report |
| `trial_balance` | `trial_balance_report` | Trial balance report |

---

## AuditService — Complete API

The audit service auto-checks the `audit_trail` feature flag before writing — nothing is written if audit is disabled.

```php
// Log a generic action
Accountflow::audit()->log(
    action:    'transaction_created',
    modelType: 'Transaction',
    modelId:   $transaction->id,
    before:    null,
    after:     $transaction->toArray()
);

// Get recent audit logs
$logs = Accountflow::audit()->getRecent(limit: 50);
```

---

## Blade Directives

```blade
{{-- Show block only when feature is enabled --}}
@featureEnabled('audit')
    <a href="{{ route('accountflow::audittrail') }}">Audit Trail</a>
@endFeatureEnabled

{{-- Show block only when feature is disabled --}}
@featureDisabled('budgets')
    <p>Budgets module is currently disabled.</p>
@endFeatureDisabled

{{-- Same as @featureEnabled, alternative syntax --}}
@accountflowFeature('loans')
    <a href="{{ route('accountflow::loans') }}">Loans</a>
@endaccountflowFeature
```

---

## Middleware

```php
// Protect a route by feature flag
Route::get('/budgets', BudgetsController::class)
    ->middleware('accountflow.feature:budgets');

// Group protection
Route::middleware(['auth', 'accountflow.feature:equity'])->group(function () {
    Route::get('/equity/partners', EquityPartnersList::class);
});

// Admin-only (accountflow.admin)
Route::get('/settings', SettingsController::class)
    ->middleware('accountflow.admin');
```

---

## Named Routes

All routes live under the `accountflow::` prefix. Apply the configured middleware from `config/accountflow.php`.

```php
// Dashboard & settings
route('accountflow::dashboard')
route('accountflow::settings')       // requires role:business

// Accounts
route('accountflow::accounts')
route('accountflow::accounts.create')

// Transactions
route('accountflow::transactions')
route('accountflow::transaction.create')
route('accountflow::transactions.edit', ['id' => $id])
route('accountflow::transactions.create')    // multiple

// Transaction templates (feature: templates)
route('accountflow::transactions.templates')
route('accountflow::transactions.templates.create')

// Transfers (feature: transfers)
route('accountflow::transfers.list')
route('accountflow::transfers.create')
route('accountflow::transfers.edit', ['id' => $id])

// Categories (feature: categories)
route('accountflow::categories')
route('accountflow::categories.create')
route('accountflow::categories.edit', ['id' => $id])

// Payment methods (feature: payment_methods)
route('accountflow::payment-methods')
route('accountflow::payment-methods.create')

// Planned payments (feature: planned_payments)
route('accountflow::planned-payments')
route('accountflow::planned-payments.create')
route('accountflow::planned-payments.edit', ['id' => $id])

// Budgets (feature: budgets)
route('accountflow::budgets')
route('accountflow::budgets.create')

// Assets (feature: assets)
route('accountflow::assets')
route('accountflow::assets.create')
route('accountflow::assets.edit', ['id' => $id])
route('accountflow::assets.transactions')
route('accountflow::assets.transactions.create')
route('accountflow::assets.transactions.edit', ['id' => $id])

// Loans (feature: loans)
route('accountflow::loans')
route('accountflow::loans.create')
route('accountflow::loans.edit', ['id' => $id])
route('accountflow::loans.partners')
route('accountflow::loans.partners.create')
route('accountflow::loans.partners.edit', ['id' => $id])

// Equity (feature: equity)
route('accountflow::equity.partners')
route('accountflow::equity.partners.create')
route('accountflow::equity.partners.edit', ['id' => $id])
route('accountflow::equity.transactions')

// Wallets (feature: wallets)
route('accountflow::users.wallets')
route('accountflow::users.wallets.create')

// Audit trail (feature: audit)
route('accountflow::audittrail')

// Reports
route('accountflow::report')
route('accountflow::report.profitLoss')        // feature: profit_loss
route('accountflow::report.trial-balance')     // feature: trial_balance
route('accountflow::report.cashbook')          // feature: cashbook
route('accountflow::report.balance-sheet')
```

---

## Embed API — Standalone Tables in Any Blade/Livewire Page

Render an AccountFlow list table with no header nav, no layout wrapper — suitable for embedding inside any existing page.

### Blade Directive
```blade
@accountflow(['table' => 'transactions'])
@accountflow(['table' => 'accounts'])
@accountflow(['table' => 'equity-partners'])
@accountflow(['table' => 'loans'])
@accountflow(['table' => 'assets'])
@accountflow(['table' => 'budgets'])
@accountflow(['table' => 'transfers'])
@accountflow(['table' => 'categories'])
@accountflow(['table' => 'payment-methods'])
@accountflow(['table' => 'planned-payments'])
@accountflow(['table' => 'wallets'])
@accountflow(['table' => 'audit-trail'])
```

### Anonymous Blade Component
```blade
<x-accountflow::table table="transactions" />
<x-accountflow::table table="accounts" />
```

### Livewire Standalone Mode (PHP)

Each list component has `public bool $standalone = false`. When `true`, the component's blade suppresses the dashboard header (`@if(!$standalone)`).

```blade
@livewire('account-flow.transactions.transactions', ['standalone' => true])
@livewire('account-flow.accounts.accounts-list', ['standalone' => true])
```

---

## Configuration (`config/accountflow.php`)

```php
return [
    // Route prefix — default: 'accounts'
    'route_prefix' => 'accounts',

    // Middleware applied to ALL AccountFlow routes
    'middlewares' => ['web', 'auth'],

    // Default currency code
    'currency' => 'USD',

    // Supported currencies for the settings dropdown
    'currencies' => [
        'PKR' => 'PKR — Pakistani Rupee',
        'USD' => 'USD — US Dollar',
        'EUR' => 'EUR — Euro',
        'GBP' => 'GBP — British Pound',
        'AED' => 'AED — UAE Dirham',
        'SAR' => 'SAR — Saudi Riyal',
        'INR' => 'INR — Indian Rupee',
        'BDT' => 'BDT — Bangladeshi Taka',
    ],

    // Display symbols — 'Rs. ' for PKR, '$' for USD, etc.
    'currency_symbols' => [
        'PKR' => 'Rs. ',
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'AED' => 'AED ',
        'SAR' => 'SAR ',
        'INR' => '₹',
        'BDT' => '৳',
    ],
];
```

### Resolve Currency Symbol Anywhere

```php
$symbol = config(
    'accountflow.currency_symbols.' . config('accountflow.currency'),
    config('accountflow.currency') . ' '
);
// '$' for USD, 'Rs. ' for PKR
```

---

## Database Tables (all prefixed with `ac_`)

| Table | Purpose |
|---|---|
| `ac_accounts` | Bank/cash accounts with running balance |
| `ac_transactions` | All income/expense transactions |
| `ac_transfers` | Account-to-account transfers |
| `ac_categories` | Hierarchical income/expense categories |
| `ac_payment_methods` | Payment methods (linked to accounts) |
| `ac_budgets` | Budget definitions with spending limits |
| `ac_planned_payments` | Recurring/scheduled payments |
| `ac_assets` | Business assets |
| `ac_asset_transactions` | Asset purchase/depreciation records |
| `ac_loans` | Loan records |
| `ac_loan_transactions` | Loan payment/interest transactions |
| `ac_loan_users` | Loan partners per loan |
| `ac_equity_partners` | Equity partner records |
| `ac_equity_transactions` | Equity investment/distribution transactions |
| `ac_purchases` | Purchase records |
| `ac_purchase_transactions` | Line-item transactions per purchase |
| `ac_user_wallets` | Per-user wallet balances |
| `ac_audit_trail` | Change history log |
| `ac_settings` | Feature flags + configuration |
| `ac_transaction_templates` | Saved transaction templates |

---

## Models (all in `App\Models\AccountFlow\` namespace)

```
Account            HasMany: transactions, transfers, paymentMethods, budgets
Transaction        BelongsTo: account, category, paymentMethod
Transfer           BelongsTo: fromAccount, toAccount
Category           BelongsTo: parent; HasMany: children, transactions
PaymentMethod      BelongsTo: account; HasMany: transactions
Budget             BelongsTo: account, category
PlannedPayment     BelongsTo: account, category
Asset              HasMany: assetTransactions
AssetTransaction   BelongsTo: asset
Loan               HasMany: loanTransactions, loanUsers
LoanTransaction    BelongsTo: loan
LoanUser           BelongsTo: loan
EquityPartner      HasMany: equityTransactions
EquityTransaction  BelongsTo: equityPartner
Purchase           HasMany: purchaseTransactions
PurchaseTransaction BelongsTo: purchase
UserWallet         BelongsTo: user
AuditTrail         BelongsTo: user
Setting            (key/value/type store)
TransactionTemplate —
```

---

## Artisan Commands

```bash
# Installation
php artisan accountflow:install         # Publish config, views, models, run migrations
php artisan accountflow:seed            # Seed default categories, settings, payment methods
php artisan accountflow:db              # Database-related setup

# Development linking
php artisan accountflow:link            # Create junctions/symlinks from app/ to package src/
php artisan accountflow:delink          # Remove all junctions/symlinks (safe on Windows + Unix)
php artisan accountflow:delink --dry-run # Preview what would be removed without removing
php artisan accountflow:sync            # Sync package files

# Feature management
php artisan accountflow:feature {name} {enable|disable}
# Examples:
php artisan accountflow:feature audit enable
php artisan accountflow:feature budgets disable
php artisan accountflow:feature categories enable

# Status & diagnostics
php artisan accountflow:status          # Check all services, features, DB tables

# Skill install (copies SKILL.md to .github/skills/)
php artisan accountflow:skill-install

# Testing / diagnostics (development only)
php artisan accountflow:test-complete   # Run all package tests
php artisan accountflow:test-facade     # Test facade bindings
php artisan accountflow:test-features   # Test feature service
php artisan accountflow:analyze-livewire # Analyze all Livewire components
```

---

## Livewire Components (app/Livewire/AccountFlow/)

All components are in `app/Livewire/AccountFlow/` (symlinked from package source):

```
AccountsDashboard          — Main dashboard with KPI cards + charts
AccountsReport             — Accounts report view
Settings                   — Feature toggle + currency settings (admin only)
Accounts/
  AccountsList             — Full account list [standalone: ✓]
  CreateAccount            — Create / edit account form
Assets/
  AssetsList               — Assets table [standalone: ✓]
  CreateAsset              — Create / edit asset
  AssetTransactions        — Asset transaction history
  CreateAssetTransaction   — Log asset transaction
AuditTrail/
  AuditTrailList           — Audit log table [standalone: ✓]
Budgets/
  BudgetsList              — Budget list with usage bars [standalone: ✓]
  CreateBudget             — Create / edit budget
Categories/
  CategoriesList           — Category tree [standalone: ✓]
  CreateCategory           — Create / edit category
Equity/
  EquityPartnersList       — Partners table [standalone: ✓]
  EquityTransactionsList   — Equity transactions [standalone: ✓]
  CreateEquityPartner      — Add / edit equity partner
Loans/
  LoansList                — Loans table [standalone: ✓]
  LoansPartnersList        — Loan partners [standalone: ✓]
  CreateLoan               — Create / edit loan
  CreateLoanPartner        — Add / edit loan partner
PaymentMethod/
  PaymentMethods           — Payment methods list [standalone: ✓]
  CreatePaymentMethod      — Add / edit payment method
PlannedPayments/
  PlannedPaymentsList      — Planned payments [standalone: ✓]
  CreatePlannedPayment     — Create / edit planned payment
Reports/
  Cashbook                 — Cashbook ledger view
  BalanceSheet             — Balance sheet
  ProfitLoss               — Profit & loss statement
  TrialBalance             — Trial balance
Transactions/
  Transactions             — Main transaction list [standalone: ✓]
  CreateTransaction        — Create / edit single transaction
  CreateTransactionMultiple — Bulk transaction entry
  CreateTransactionTemplate — Save transaction template
  TransactionTemplate      — Template list
Transfers/
  TransfersList            — Transfers list [standalone: ✓]
  CreateTransfer           — Create / edit transfer
Wallets/
  UserWalletsList          — User wallet list [standalone: ✓]
  CreateUserWalletTransfers — Create wallet transfer
```

---

## Complete Usage Examples

### 1. Record a Sale (Income)

```php
use ArtflowStudio\AccountFlow\Facades\Accountflow;

$transaction = Accountflow::transactions()->createIncome([
    'amount'      => 2500.00,
    'description' => 'Invoice #1042 — Website Design',
    'category_id' => Accountflow::settings()->defaultSalesCategoryId(),
    'account_id'  => 1,
    'date'        => now()->toDateString(),
]);
```

### 2. Record an Expense

```php
use ArtflowStudio\AccountFlow\Facades\AC;

$expense = AC::transactions()->createExpense([
    'amount'         => 350.00,
    'description'    => 'AWS hosting — April',
    'payment_method' => 2,   // auto-resolves account_id
]);
```

### 3. Conditional Feature Check

```php
if (Accountflow::features()->isEnabled('audit')) {
    Accountflow::audit()->log(
        action:    'invoice_paid',
        modelType: 'Transaction',
        modelId:   $transaction->id,
        after:     $transaction->toArray()
    );
}
```

### 4. Get Account Balance

```php
$balance = Accountflow::accounts()->getBalance(accountId: 1);
echo "Balance: $balance";
```

### 5. Generate a P&L Report

```php
$pl = Accountflow::reports()->profitAndLoss(
    startDate: now()->startOfMonth()->toDateString(),
    endDate:   now()->endOfMonth()->toDateString()
);

echo "Revenue: {$pl['revenue']} | Expenses: {$pl['expenses']} | Profit: {$pl['profit']}";
```

### 6. Embed Transactions Table in Any Page

```blade
{{-- In any Blade view — no layout, no header --}}
@accountflow(['table' => 'transactions'])
```

### 7. Feature-Gated Navigation Link

```blade
@featureEnabled('loans')
    <a href="{{ route('accountflow::loans') }}" class="nav-link">Loans</a>
@endFeatureEnabled
```

### 8. Create a Budget

```php
$budget = Accountflow::budgets()->create([
    'account_id'      => 1,
    'category_id'     => 8,
    'amount'          => 3000.00,
    'period'          => 'monthly',
    'alert_threshold' => 75,
]);
```

---

## Common Mistakes to Avoid

1. **Wrong namespace for models** — use `App\Models\AccountFlow\Transaction`, not `App\Models\Transaction`.
2. **Editing published files instead of package source** — always edit in `vendor/artflow-studio/accountflow/src/`. The SPL autoloader loads straight from that path; published files under `app/` are only relevant if junctions are active.
3. **Manual balance updates** — never update `account->balance` directly; `TransactionService` handles it.
4. **Using `DB::` in queries** — prefer `Model::query()` for all AccountFlow models.
5. **Ignoring the `$standalone` flag** — when embedding a component, always pass `standalone: true` to suppress the layout header.
6. **Feature key vs. DB key mismatch** — use the short alias (`audit`, `budgets`) not the DB key (`audit_trail`); the service normalises it.
7. **Calling `accountflow:delink` with `--force` without checking** — use `--dry-run` first to preview what will be removed.

---

## Install Skill Command

To keep this skill up to date in your project after updating the package:

```bash
php artisan accountflow:skill-install
```

This copies `SKILL.md` from the package root to `.github/skills/accountflow-development/SKILL.md`, making it available to VS Code GitHub Copilot as a context skill.
