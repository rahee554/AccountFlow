# 🎉 AccountFlow Package - Complete Documentation

**Version:** 3.0.0  
**Date:** November 18, 2025  
**Status:** ✅ Production Ready & Fully Tested

---

## 📋 Table of Contents

1. [Features](#-features)
2. [Installation](#-installation)
3. [Quick Start](#-quick-start)
4. [Feature Management](#-feature-management)
5. [Blade Directives](#-blade-directives)
6. [Middleware](#-middleware)
7. [Commands](#-commands)
8. [Services](#-services)
9. [Testing](#-testing)

---

## ✨ Features

### Core Accounting
- ✅ Multi-Account Management
- ✅ Transactions (Income/Expense/Transfer)
- ✅ Categories (Hierarchical)
- ✅ Payment Methods
- ✅ Real-time Balance Tracking

### Advanced Features
- 📊 Financial Reports (P&L, Cash Flow, Balance Sheet)
- 💰 Budgets & Variance Analysis
- 🏦 Assets Management
- 💸 Loans Management
- 👥 Equity Partners
- 📅 Planned Payments
- 💼 Transaction Templates
- 🔍 Audit Trail (FIXED!)
- 👛 User Wallets

### Feature Control (NEW!)
- 🔧 Enable/Disable Features
- 🛡️ Middleware Protection
- 🎨 Blade Directives
- ⚙️ Granular Control

---

## 🚀 Installation

```bash
# Install
composer require artflow-studio/accountflow

# Migrate
php artisan migrate

# Seed
php artisan accountflow:seed

# Check status
php artisan accountflow:status
```

---

## 🎯 Quick Start

```php
use ArtflowStudio\AccountFlow\Facades\Accountflow;

// Create transaction
$transaction = Accountflow::transactions()->createIncome([
    'amount' => 1000,
    'category_id' => 2,
    'account_id' => 1,
]);

// Get balance
$balance = Accountflow::accounts()->getBalance($accountId);

// Log audit (if enabled)
if (Accountflow::features()->isEnabled('audit')) {
    Accountflow::audit()->logTransactionCreated($transaction->id, $transaction->toArray());
}
```

---

## 🔧 Feature Management

### Enable/Disable Features

```bash
# Via command
php artisan accountflow:feature audit enable
php artisan accountflow:feature budgets disable
```

```php
// Via code
Accountflow::features()->enable('audit');
Accountflow::features()->disable('budgets');
Accountflow::features()->isEnabled('audit'); // true/false
```

### Available Features

- `audit_trail` - Audit logging
- `budgets` - Budget management
- `planned_payments` - Recurring payments
- `reports` - Financial reports
- `assets` - Assets management
- `loans` - Loans management
- `wallets` - User wallets
- `equity` - Equity partners
- `templates` - Transaction templates

---

## 🎨 Blade Directives

```blade
{{-- Show/hide based on feature --}}
@featureEnabled('audit')
    <a href="/audit-trail">View Audit Trail</a>
@endFeatureEnabled

@featureDisabled('budgets')
    <div>Budgets are disabled</div>
@endFeatureDisabled

{{-- In navigation --}}
<nav>
    @featureEnabled('audit')
        <li><a href="/audit">Audit</a></li>
    @endFeatureEnabled
    
    @featureEnabled('budgets')
        <li><a href="/budgets">Budgets</a></li>
    @endFeatureEnabled
</nav>
```

---

## 🛡️ Middleware

```php
// Protect routes
Route::get('/audit-trail', Controller::class)
    ->middleware('accountflow.feature:audit');

// Group protection
Route::middleware(['auth', 'accountflow.feature:budgets'])->group(function () {
    Route::get('/budgets', [BudgetController::class, 'index']);
});
```

---

## 🎮 Commands

### Testing
```bash
php artisan accountflow:test-complete    # Run all tests
php artisan accountflow:test-facade      # Test facade
php artisan accountflow:test-features    # Test features
```

### Management
```bash
php artisan accountflow:status           # System status
php artisan accountflow:seed             # Seed data
php artisan accountflow:feature {name} {enable|disable}
php artisan accountflow:analyze-livewire # Analyze components
```

---

## 📦 Services (9 Total)

### 1. TransactionService
```php
Accountflow::transactions()->createIncome($data);
Accountflow::transactions()->createExpense($data);
Accountflow::transactions()->update($id, $data);
Accountflow::transactions()->delete($id);
Accountflow::transactions()->getSummary($start, $end);
```

### 2. AccountService
```php
Accountflow::accounts()->create($data);
Accountflow::accounts()->getAll();
Accountflow::accounts()->getBalance($id);
Accountflow::accounts()->addToBalance($id, $amount);
Accountflow::accounts()->subtractFromBalance($id, $amount);
```

### 3. SettingsService
```php
Accountflow::settings()->defaultSalesCategoryId();
Accountflow::settings()->defaultExpenseCategoryId();
Accountflow::settings()->get('key', 'default');
Accountflow::settings()->set('key', 'value');
```

### 4. FeatureService (NEW!)
```php
Accountflow::features()->isEnabled('audit');
Accountflow::features()->enable('audit');
Accountflow::features()->disable('budgets');
Accountflow::features()->getAllFeatures();
```

### 5. AuditService (FIXED!)
```php
Accountflow::audit()->log('created', 'Transaction', $id, null, $data);
Accountflow::audit()->logTransactionCreated($id, $data);
Accountflow::audit()->getRecent(50);
Accountflow::audit()->getByUser($userId);
```

### 6-9. Other Services
- CategoryService, PaymentMethodService, BudgetService, ReportService

See `docs/SERVICES_INDEX.md` for complete API documentation.

---

## 🧪 Testing

```bash
php artisan accountflow:test-complete
```

**Results:**
```
✅ 9/9 tests PASSED
- Status Check
- Facade Resolution
- All Services
- Feature Management
- Real Usage
```

---

## 🔧 What's New in v3.0.0

### ✅ Fixed
- **Audit Trail SQL Error** - Fixed `model_type` field issue
- **Container Resolution** - Proper namespace structure
- **Service Binding** - All services registered correctly

### ✨ New
- **Feature Management** - Complete feature control system
- **Blade Directives** - `@featureEnabled`, `@featureDisabled`
- **Middleware** - Route protection
- **14 Commands** - Complete test suite
- **FeatureService** - 9th service added

---

## 📚 Documentation

- `README.md` - This file
- `docs/QUICK_REFERENCE.md` - API cheat sheet
- `docs/SERVICES_INDEX.md` - Complete API
- `ISSUE_RESOLVED.md` - Recent fixes

---

## 🚨 Common Issues

### Audit Trail Error (FIXED!)
```
Error: Field 'model_type' doesn't have a default value
Solution: Updated in v3.0.0
```

### Feature Not Working
```bash
php artisan accountflow:status
php artisan accountflow:feature audit enable
```

---

## 💡 Complete Example

```php
use ArtflowStudio\AccountFlow\Facades\Accountflow;

// Enable audit if needed
if (!Accountflow::features()->isEnabled('audit')) {
    Accountflow::features()->enable('audit');
}

// Create transaction
$transaction = Accountflow::transactions()->createIncome([
    'amount' => 2500,
    'description' => 'Client Payment',
    'category_id' => Accountflow::settings()->defaultSalesCategoryId(),
    'account_id' => 1,
    'date' => now(),
]);

// Log audit
Accountflow::audit()->logTransactionCreated($transaction->id, $transaction->toArray());

// Update balance
Accountflow::accounts()->addToBalance($transaction->account_id, $transaction->amount);

// Get report
$report = Accountflow::reports()->profitAndLoss(now()->startOfMonth(), now()->endOfMonth());
```

---

**Version:** 3.0.0  
**Status:** ✅ Production Ready  
**Last Updated:** November 18, 2025  
**License:** MIT

