# 🤖 AccountFlow Package - Agent Documentation

This document explains the AccountFlow package structure, what it does, and where to make changes.

---

## 📋 What is AccountFlow?

AccountFlow is a comprehensive accounting and financial management system built into the ArtflowERP as a reusable Laravel package. It provides:

- **Double-Entry Accounting**: Accounts, transactions, transfers
- **Budget Management**: Create and track budgets
- **Asset Tracking**: Manage company assets and movements
- **Loan Management**: Track loans and loan users
- **Financial Reports**: Dashboard, profit/loss, trial balance, cashbook
- **Payment Methods**: Manage various payment methods
- **Audit Trail**: Complete transaction history
- **User Wallets**: Individual user wallet balances

---

## 📁 Package Directory Structure

```
vendor/artflow-studio/accountflow/
├── src/
│   ├── app/
│   │   ├── Console/                    # Artisan commands
│   │   │   ├── InstallCommand.php
│   │   │   ├── AccountFlowLinkCommand.php
│   │   │   ├── AccountFlowSyncCommand.php        ← File sync utility
│   │   │   ├── AccountFlowMigrateCommand.php
│   │   │   ├── AccountFlowMigrateFreshCommand.php
│   │   │   └── AccountFlowSeedCommand.php
│   │   │
│   │   ├── Http/
│   │   │   └── Controllers/AccountFlow/
│   │   │       ├── AccountsController.php         ← Web controllers
│   │   │       └── DefaultController.php
│   │   │
│   │   ├── Livewire/                  # UI Components
│   │   │   └── AccountFlow/
│   │   │       ├── AccountsDashboard.php          ← Dashboard
│   │   │       ├── Accounts/                      ← Account management
│   │   │       ├── Transactions/                  ← Transaction management
│   │   │       ├── Budgets/                       ← Budget management
│   │   │       ├── Loans/                         ← Loan management
│   │   │       ├── Assets/                        ← Asset tracking
│   │   │       ├── Equity/                        ← Equity tracking
│   │   │       ├── Reports/                       ← Financial reports
│   │   │       ├── PaymentMethod/                 ← Payment methods
│   │   │       ├── Categories/                    ← Categories
│   │   │       ├── Wallets/                       ← User wallets
│   │   │       ├── AuditTrail/                    ← Audit logs
│   │   │       ├── PlannedPayments/               ← Payment planning
│   │   │       └── Settings.php                   ← Settings component
│   │   │
│   │   └── Models/                    # Eloquent Models
│   │       ├── Account.php                        ← Main account model
│   │       ├── Transaction.php                    ← Transaction model
│   │       ├── Transfer.php                       ← Transfer model
│   │       ├── Budget.php
│   │       ├── Loan.php
│   │       ├── LoanTransaction.php
│   │       ├── LoanUser.php
│   │       ├── Asset.php
│   │       ├── AssetTransaction.php
│   │       ├── Category.php
│   │       ├── PaymentMethod.php
│   │       ├── UserWallet.php
│   │       ├── EquityPartner.php
│   │       ├── EquityTransaction.php
│   │       ├── AuditTrail.php
│   │       ├── Setting.php
│   │       ├── TransactionTemplate.php
│   │       ├── PlannedPayment.php
│   │       └── Purchase related models
│   │
│   ├── config/
│   │   └── accountflow.php                        ← Configuration file
│   │
│   ├── database/
│   │   ├── migrations/                            ← DO NOT MODIFY (Production)
│   │   └── seeders/                               ← Demo data seeder
│   │
│   ├── resources/
│   │   └── views/
│   │       └── vendor/artflow-studio/accountflow/
│   │           ├── blades/                        ← Blade templates
│   │           └── livewire/                      ← Livewire view files
│   │
│   ├── public/
│   │   └── vendor/artflow-studio/accountflow/
│   │       └── assets/                            ← CSS, JS, images
│   │
│   ├── routes/
│   │   └── accountflow.php                        ← All package routes
│   │
│   └── AccountFlowServiceProvider.php             ← Main service provider
│
├── AGENT.md                                       ← This file
├── PRODUCTION_FEATURES.md                        ← What was added
└── [Other documentation files]
```

---

## 🎯 Where to Make Changes

### 1. **Add New Features / Modify Business Logic**

#### In the Package (for new features)
Edit in: `vendor/artflow-studio/accountflow/src/`

**Example**: Add a new expense category
- Edit: `src/config/accountflow.php` → Add to categories array
- Then sync: `php artisan accountflow:sync`

#### In the Project (for customizations)
Edit in: `app/`, `config/`, etc.

**Example**: Customize the accounts dashboard
- Edit: `app/Livewire/AccountFlow/AccountsDashboard.php`
- Then sync back: `php artisan accountflow:sync`

---

### 2. **Add New Livewire Component**

**Scenario**: You want to add a new report view

**Steps**:
1. Create in package: `vendor/artflow-studio/accountflow/src/app/Livewire/AccountFlow/Reports/MyNewReport.php`
2. Create view: `vendor/artflow-studio/accountflow/src/resources/views/vendor/.../livewire/reports/my-new-report.blade.php`
3. Run sync: `php artisan accountflow:sync`
4. Add route: `src/routes/accountflow.php`
5. Run sync again: `php artisan accountflow:sync`

---

### 3. **Modify Models**

**Scenario**: Add a new relationship or method to the Account model

**Steps**:
1. Edit: `vendor/artflow-studio/accountflow/src/app/Models/Account.php`
2. Or edit: `app/Models/AccountFlow/Account.php` (in your project)
3. Run sync: `php artisan accountflow:sync`

---

### 4. **Update Configuration**

**File**: `config/accountflow.php` in your project root

**Options to customize**:
```php
'layout' => 'layouts.admin.app-fluid',           // Admin layout
'business_name' => fn () => '...',               // Business name
'middlewares' => ['tenant.web', 'auth'],         // Route middleware
'categories' => [                                // Income/expense categories
    'income' => [...],
    'expense' => [...],
],
```

---

### 5. **Add Views/Blade Templates**

**Location**: `resources/views/vendor/artflow-studio/accountflow/`

**Sync from package**:
```bash
php artisan accountflow:sync
```

---

## 🔄 File Syncing Process

### Understanding Copy vs Symlink

On **Windows**, files are **copied** (not symlinked) because Windows doesn't support true symlinks without admin privileges.

**This means**:
- ✅ Changes in project copy to package
- ✅ Changes in package copy to project
- ❌ Real-time sync not automatic

### How to Sync Files

```bash
# Check what files have changed (without making changes)
php artisan accountflow:sync --check

# Sync all changed files (interactive - asks for confirmation)
php artisan accountflow:sync

# Force sync without prompting
php artisan accountflow:sync --force
```

### Sync Output Example

```
📁 Syncing Livewire Components...
  ✓ Synced: AccountsDashboard.php
  ✓ Synced: Accounts\AccountsList.php
  ✓ Synced: Transactions\Transactions.php

📁 Syncing Models...
  ✓ Synced: Account.php
  ✓ Synced: Transaction.php

✅ Sync complete!
  ✓ Synced: 5 files
```

---

## ⚠️ Production Considerations

### ❌ DO NOT MODIFY MIGRATIONS

**Reason**: This package is used in production on other apps. Migrations cannot be changed once deployed.

**What to do instead**:
- Add new features in models
- Create custom traits or observers
- Extend existing models

### ✅ SAFE TO MODIFY

- Livewire components
- Controllers
- Models (add methods, relationships)
- Views and templates
- Config file
- Routes

### 🔒 Database Schema

Current tables:
- accounts
- transactions
- transfers
- budgets
- loans, loan_transactions, loan_users
- assets, asset_transactions
- categories
- payment_methods
- user_wallets
- equity_partners, equity_transactions
- audit_trails
- settings
- purchase, purchase_transactions
- transaction_templates
- planned_payments

**Cannot add new tables** - Must extend existing models.

---

## 🎨 Customization Examples

### Example 1: Add a Custom Account Type

**File**: `config/accountflow.php`
```php
'account_types' => [
    'bank' => 'Bank Account',
    'cash' => 'Cash',
    'wallet' => 'Digital Wallet',
    'custom' => 'Custom Type',  // Add this
],
```

### Example 2: Extend Account Model

**File**: `app/Models/AccountFlow/Account.php`
```php
class Account extends Model
{
    // Add custom method
    public function getDailyBalance($date)
    {
        return $this->transactions()
            ->whereDate('date', '<=', $date)
            ->sum('amount');
    }
    
    // Add custom relationship
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
```

### Example 3: Create Custom Report

**File**: `app/Livewire/AccountFlow/Reports/CustomReport.php`
```php
namespace App\Livewire\AccountFlow\Reports;

use Livewire\Component;
use App\Models\AccountFlow\Transaction;

class CustomReport extends Component
{
    public function render()
    {
        $transactions = Transaction::all();
        return view('accountflow::reports.custom', [
            'transactions' => $transactions,
        ]);
    }
}
```

---

## 🛠️ Commands Reference

```bash
# Link package files to project
php artisan accountflow:link [--force]

# Sync files between package and project
php artisan accountflow:sync [--check] [--force]

# Publish configuration
php artisan vendor:publish --tag=accountflow-config

# Migrate database (add new tables only if safe)
php artisan accountflow:migrate

# Fresh migration with seed (development only!)
php artisan accountflow:migrate:fresh --seed

# Seed demo data
php artisan accountflow:seed

# Test model loading
php artisan test:accountflow-models

# List all routes
php artisan route:list | grep accountflow
```

---

## 📊 Database Relationships

### Main Flows

```
Account
  ├── transactions() → Transaction
  ├── transfers_from() → Transfer (from_account_id)
  ├── transfers_to() → Transfer (to_account_id)
  ├── budget() → Budget
  └── audit_trails() → AuditTrail

Transaction
  ├── account() → Account
  ├── category() → Category
  └── payment_method() → PaymentMethod

Transfer
  ├── from_account() → Account
  └── to_account() → Account

Budget
  └── account() → Account
```

---

## 🔐 Important Notes

### Production Package
- **Cannot modify migrations** - affects production apps
- **Can add features** - new columns handled via new migrations
- **Should extend** - add traits, observers, custom methods
- **Backward compatible** - all changes must work with existing data

### Development
- Edit files in both locations
- Run `php artisan accountflow:sync` to keep both in sync
- Test thoroughly before production deployment

### File Permissions
- Models: PSR-4 namespace must match file path
- Livewire: PSR-4 namespace must match file path
- Controllers: Follow Laravel conventions

---

## 📞 Quick Reference

| Need | Action | File |
|------|--------|------|
| Add category | Edit config | `config/accountflow.php` |
| New component | Create & sync | `app/Livewire/AccountFlow/...` |
| Extend model | Add method | `app/Models/AccountFlow/...` |
| Custom route | Add & sync | `app/routes/accountflow.php` |
| Fix bug | Edit & sync | Appropriate file |
| Add calculation | Add method | Model file |

---

## 🎓 Next Steps

1. Familiarize yourself with the directory structure
2. Run `php artisan accountflow:sync` to sync all files
3. Make your first change (e.g., edit a component)
4. Run sync again to confirm it works
5. Test the changes in the browser

---

**This package is actively developed and maintained for production use.**

