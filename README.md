# AccountFlow — Laravel Accounting Package

A full-featured accounting module for Laravel. Drop it in as a package and get accounts, transactions, budgets, reports, assets, loans, equity, and more — all behind a feature-flag system you control.

---

## Table of Contents

1. [Features](#features)
2. [Installation](#installation)
3. [Quick Start](#quick-start)
4. [Facades](#facades)
5. [Services](#services)
6. [Feature Management](#feature-management)
7. [Blade Directives](#blade-directives)
8. [Middleware](#middleware)
9. [Named Routes](#named-routes)
10. [Embed API](#embed-api)
11. [Configuration](#configuration)
12. [Database Tables](#database-tables)
13. [Artisan Commands](#artisan-commands)
14. [Changelog](#changelog)

---

## Features

**Core Accounting**
- Multi-account management with real-time balance tracking
- Transactions (income / expense) with auto-balance updates
- Account-to-account transfers
- Hierarchical categories (income & expense)
- Payment methods linked to accounts

**Advanced Modules** (each independently enable/disable-able)
- Financial Reports — P&L, Trial Balance, Cashbook, Balance Sheet
- Budgets with variance analysis and threshold alerts
- Assets management with transaction history
- Loans management with partners
- Equity partners and distributions
- Planned / recurring payments
- Transaction templates
- User wallets
- Audit trail

---

## Installation

### Local Development (path symlink)

```json
{
    "repositories": [
        { "type": "path", "url": "../accountflow", "options": { "symlink": true } }
    ],
    "require": {
        "artflow-studio/accountflow": "*"
    }
}
```

```bash
composer update artflow-studio/accountflow
```

### Production (Packagist / VCS)

```bash
composer require artflow-studio/accountflow
```

### After Installing (both options)

```bash
php artisan migrate
php artisan accountflow:seed
php artisan vendor:publish --tag=accountflow-config   # optional
php artisan accountflow:status                         # verify everything is working
```

---

## Quick Start

```php
use ArtflowStudio\AccountFlow\Facades\Accountflow;

// Record a sale
$transaction = Accountflow::transactions()->createIncome([
    'amount'      => 2500.00,
    'description' => 'Invoice #1042',
    'category_id' => Accountflow::settings()->defaultSalesCategoryId(),
    'account_id'  => 1,
]);

// Record an expense
$expense = Accountflow::transactions()->createExpense([
    'amount'         => 350.00,
    'description'    => 'AWS hosting',
    'payment_method' => 2,     // account_id auto-resolved from this payment method
]);

// Get account balance
$balance = Accountflow::accounts()->getBalance(accountId: 1);

// Get P&L for the current month
$pl = Accountflow::reports()->profitAndLoss(
    startDate: now()->startOfMonth()->toDateString(),
    endDate:   now()->endOfMonth()->toDateString()
);
```

---

## Facades

The package registers **two facade aliases** that both resolve to `AccountFlowManager`:

```php
// Primary — verbose and self-documenting
use ArtflowStudio\AccountFlow\Facades\Accountflow;
Accountflow::transactions()->createIncome([...]);

// Short alias — convenient for dense code
use ArtflowStudio\AccountFlow\Facades\AC;
AC::transactions()->createIncome([...]);
```

Both are bound to the container key `accountflow`.

---

## Services

### TransactionService

All `TransactionService` methods are static. Call them through the facade:

```php
Accountflow::transactions()->create(array $data): Transaction
Accountflow::transactions()->createIncome(array $data): Transaction
Accountflow::transactions()->createExpense(array $data): Transaction
Accountflow::transactions()->createBatch(array $transactions): Collection
Accountflow::transactions()->update(Transaction $tx, array $data): Transaction
```

**`create()` — field reference**

| Field | Required | Notes |
|---|---|---|
| `amount` | Yes | Float, must be > 0 |
| `type` | Yes | `1` = income, `2` = expense (or `'income'` / `'expense'`) |
| `payment_method` | — | If set, `account_id` is auto-resolved from it |
| `account_id` | — | Defaults via payment method or the Settings default |
| `category_id` | — | Defaults to type-based default category from Settings |
| `date` | — | Any Carbon-parseable string. Defaults to `now()` |
| `description` | — | Free text |
| `reference` | — | External reference (invoice number etc.) |
| `user_id` | — | Defaults to `auth()->id()` |

> **Balance is managed automatically.** `TransactionService` calls `AccountService::addToBalance()` / `subtractFromBalance()` after every create and reverses it on update. Never update `account->balance` manually.

**Examples**

```php
// Full options
$tx = Accountflow::transactions()->create([
    'amount'         => 1500.00,
    'type'           => 1,
    'payment_method' => 3,
    'category_id'    => 5,
    'date'           => '2026-04-09',
    'description'    => 'April consulting',
    'reference'      => 'INV-2026-042',
]);

// Convenience methods (type is set automatically)
$income  = Accountflow::transactions()->createIncome(['amount' => 5000, 'description' => 'Sale']);
$expense = Accountflow::transactions()->createExpense(['amount' => 200, 'description' => 'Supplies']);

// Batch (single DB transaction — all or nothing)
$batch = Accountflow::transactions()->createBatch([
    ['amount' => 1000, 'type' => 1, 'description' => 'Sale A'],
    ['amount' =>  200, 'type' => 2, 'description' => 'Rent'],
]);

// Update — balance reversal + reapplication is automatic
$updated = Accountflow::transactions()->update($tx, ['amount' => 1800]);

// Short alias
use ArtflowStudio\AccountFlow\Facades\AC;
$tx = AC::transactions()->createExpense(['amount' => 75, 'description' => 'Coffee']);
```

---

### AccountService

```php
Accountflow::accounts()->create([
    'name'            => 'Main Account',
    'opening_balance' => 10000.00,
    'active'          => true,
]): Account

Accountflow::accounts()->update($account, ['name' => 'Petty Cash']): Account

// Read current stored balance
Accountflow::accounts()->getBalance(int $accountId): float

// Recompute balance from all transactions
Accountflow::accounts()->recalculateBalance(int $accountId): float

// Query account transactions
Accountflow::accounts()->getTransactions(
    accountId: 1,
    startDate: '2026-01-01',
    endDate:   '2026-12-31',
    limit:     50
): Collection
```

---

### ReportService

```php
// Income + expense totals with category breakdown
Accountflow::reports()->incomeExpenseReport(
    startDate: '2026-01-01',
    endDate:   '2026-12-31',
    accountId: null             // null = all accounts
): array

// P&L
Accountflow::reports()->profitAndLoss('2026-01-01', '2026-12-31'): array
// Returns: revenue, expenses, profit, profit_margin, revenue_breakdown, expense_breakdown

// Monthly cash flow
Accountflow::reports()->cashFlowReport('2026-01-01', '2026-12-31'): array
// Returns: by_month[{month, inflows, outflows, net_cash_flow}], total_inflows, total_outflows

// Per-account balance snapshot
Accountflow::reports()->balanceReport(): array
// Returns: accounts[{account_id, account_name, balance, opening_balance}], total_balance

// Grouped by payment method
Accountflow::reports()->byPaymentMethod('2026-01-01', '2026-12-31'): array
```

---

### SettingsService

```php
Accountflow::settings()->get(string $key, mixed $default = null): mixed
Accountflow::settings()->set(string $key, mixed $value, int $type = 1): Setting
Accountflow::settings()->getAll(): array

// Convenience
Accountflow::settings()->defaultPaymentMethodId(): int
Accountflow::settings()->defaultSalesCategoryId(): int
Accountflow::settings()->defaultExpenseCategoryId(): int
```

---

### FeatureService

```php
Accountflow::features()->isEnabled(string $feature): bool
Accountflow::features()->isDisabled(string $feature): bool
Accountflow::features()->enable(string $feature): bool
Accountflow::features()->disable(string $feature): bool
Accountflow::features()->getAllFeatures(): array

// Example
if (Accountflow::features()->isEnabled('audit')) {
    Accountflow::audit()->log('invoice_paid', 'Transaction', $tx->id, null, $tx->toArray());
}
```

---

### CategoryService

```php
Accountflow::categories()->create([
    'name'      => 'Product Sales',
    'type'      => 1,           // 1 = income, 2 = expense
    'parent_id' => null,
]): Category

Accountflow::categories()->update($category, ['name' => 'Services']): Category
Accountflow::categories()->getByType(int $type): Collection
```

---

### PaymentMethodService

```php
Accountflow::paymentMethods()->create([
    'name'       => 'Stripe',
    'account_id' => 1,
    'status'     => 1,
]): PaymentMethod

Accountflow::paymentMethods()->update($method, ['name' => 'Stripe Gateway']): PaymentMethod
Accountflow::paymentMethods()->getActive(): Collection
```

---

### BudgetService

```php
Accountflow::budgets()->create([
    'account_id'      => 1,
    'category_id'     => 5,
    'amount'          => 5000.00,
    'period'          => 'monthly',  // daily | weekly | monthly | yearly
    'alert_threshold' => 80,         // alert at 80%
    'start_date'      => '2026-01-01',
    'end_date'        => '2026-12-31',
]): Budget

Accountflow::budgets()->update($budget, ['amount' => 6000]): Budget
```

---

### AuditService

Auto-checks the `audit_trail` feature flag — nothing is written when audit is disabled.

```php
Accountflow::audit()->log(
    action:    'transaction_created',
    modelType: 'Transaction',
    modelId:   $transaction->id,
    before:    null,
    after:     $transaction->toArray()
): ?AuditTrail

Accountflow::audit()->getRecent(int $limit = 50): Collection
```

---

## Feature Management

### Via Artisan

```bash
php artisan accountflow:feature audit enable
php artisan accountflow:feature budgets disable
```

### Via Code

```php
Accountflow::features()->enable('audit');
Accountflow::features()->disable('budgets');
```

### Feature Keys

| Short key | Description |
|---|---|
| `audit` | Audit trail |
| `budgets` | Budgets module |
| `planned_payments` | Planned / recurring payments |
| `assets` | Assets management |
| `loans` | Loans management |
| `wallets` | User wallets |
| `equity` | Equity partners |
| `cashbook` | Cashbook report |
| `multi_accounts` | Multiple account support |
| `templates` | Transaction templates |
| `payment_methods` | Payment methods management |
| `categories` | Custom categories |
| `transfers` | Account transfers |
| `profit_loss` | P&L report |
| `trial_balance` | Trial balance report |

---

## Blade Directives

```blade
@featureEnabled('audit')
    <a href="{{ route('accountflow::audittrail') }}">Audit Trail</a>
@endFeatureEnabled

@featureDisabled('budgets')
    <p>Budgets module is disabled.</p>
@endFeatureDisabled

@accountflowFeature('loans')
    <a href="{{ route('accountflow::loans') }}">Loans</a>
@endaccountflowFeature
```

---

## Middleware

```php
// Single route
Route::get('/budgets', BudgetsList::class)
    ->middleware('accountflow.feature:budgets');

// Group
Route::middleware(['auth', 'accountflow.feature:equity'])->group(function () {
    Route::get('/equity/partners', EquityPartnersList::class);
});

// Admin only
Route::get('/settings', AccountsSettings::class)
    ->middleware('accountflow.admin');
```

---

## Named Routes

All routes are prefixed with `accountflow::`. URL prefix defaults to `accounts` (configurable).

```php
route('accountflow::dashboard')
route('accountflow::settings')
route('accountflow::accounts')
route('accountflow::accounts.create')
route('accountflow::transactions')
route('accountflow::transaction.create')
route('accountflow::transactions.edit', ['id' => $id])
route('accountflow::transfers.list')
route('accountflow::transfers.create')
route('accountflow::categories')
route('accountflow::categories.create')
route('accountflow::payment-methods')
route('accountflow::payment-methods.create')
route('accountflow::planned-payments')
route('accountflow::planned-payments.create')
route('accountflow::planned-payments.edit', ['id' => $id])
route('accountflow::budgets')
route('accountflow::budgets.create')
route('accountflow::assets')
route('accountflow::assets.create')
route('accountflow::assets.transactions')
route('accountflow::loans')
route('accountflow::loans.create')
route('accountflow::loans.partners')
route('accountflow::equity.partners')
route('accountflow::equity.transactions')
route('accountflow::users.wallets')
route('accountflow::audittrail')
route('accountflow::report')
route('accountflow::report.profitLoss')
route('accountflow::report.trial-balance')
route('accountflow::report.cashbook')
route('accountflow::report.balance-sheet')
route('accountflow::transactions.templates')
```

---

## Embed API

Render any AccountFlow list table in standalone mode (no nav, no layout wrapper).

### Blade directive

```blade
@accountflow(['table' => 'transactions'])
@accountflow(['table' => 'accounts'])
@accountflow(['table' => 'budgets'])
@accountflow(['table' => 'assets'])
@accountflow(['table' => 'loans'])
@accountflow(['table' => 'equity-partners'])
@accountflow(['table' => 'transfers'])
@accountflow(['table' => 'categories'])
@accountflow(['table' => 'payment-methods'])
@accountflow(['table' => 'planned-payments'])
@accountflow(['table' => 'wallets'])
@accountflow(['table' => 'audit-trail'])
```

### Livewire inline with standalone mode

```blade
@livewire('account-flow.transactions.transactions', ['standalone' => true])
@livewire('account-flow.accounts.accounts-list', ['standalone' => true])
```

---

## Configuration

```bash
php artisan vendor:publish --tag=accountflow-config
```

```php
// config/accountflow.php
return [
    'route_prefix' => 'accounts',
    'middlewares'  => ['web', 'auth'],
    'currency'     => 'USD',

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

---

## Database Tables

| Table | Purpose |
|---|---|
| `ac_accounts` | Accounts with running balance |
| `ac_transactions` | Income & expense records |
| `ac_transfers` | Account-to-account transfers |
| `ac_categories` | Hierarchical categories |
| `ac_payment_methods` | Payment methods |
| `ac_budgets` | Budget definitions |
| `ac_planned_payments` | Recurring/scheduled payments |
| `ac_assets` | Business assets |
| `ac_asset_transactions` | Asset transactions |
| `ac_loans` | Loan records |
| `ac_loan_transactions` | Loan repayment records |
| `ac_loan_users` | Loan partners |
| `ac_equity_partners` | Equity partners |
| `ac_equity_transactions` | Equity transactions |
| `ac_purchases` | Purchase records |
| `ac_purchase_transactions` | Purchase line items |
| `ac_user_wallets` | Per-user wallets |
| `ac_audit_trail` | Change history |
| `ac_settings` | Feature flags & config |
| `ac_transaction_templates` | Saved templates |

---

## Artisan Commands

```bash
# Setup
php artisan accountflow:install
php artisan accountflow:link
php artisan accountflow:seed

# Feature flags
php artisan accountflow:feature {name} {enable|disable}

# Status
php artisan accountflow:status

# AI Agent skill — copies SKILL.md to .github/skills/accountflow-development/
php artisan accountflow:skill-install

# Diagnostics
php artisan accountflow:test-complete
php artisan accountflow:test-facade
php artisan accountflow:analyze-livewire
```

---

## What's Included

- Dynamic per-tenant currency via Settings page with `currency_symbols` config
- KPI dashboard with period-over-period comparisons
- All list components support `$standalone = true` for embed mode (see [Embed API](#embed-api))
- 9 services behind a unified `AccountFlowManager` façade
- 15 independently toggleable feature flags
- `@featureEnabled` / `@featureDisabled` Blade directives
- `accountflow.feature` and `accountflow.admin` middleware
- SPL autoloader — no symlinks or junctions required in production
- `accountflow:skill-install` command for AI agent context

---

**License:** MIT — artflow-studio
