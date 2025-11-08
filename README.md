# 📊 AccountFlow - Professional Accounting System for Laravel-- Active: 1723998450972@@127.0.0.1@3306

# AccountFlow - Reusable Dynamic Accounts Module for Laravel

<div align="center">

AccountFlow is a reusable dynamic accounts module designed for Laravel, providing customization for views, controllers, models, migrations, and configurations.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

[![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)![AccountFlow Logo](https://via.placeholder.com/468x300?text=AccountFlow+Logo)

[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white)](https://php.net)

[![Livewire](https://img.shields.io/badge/Livewire-3.6-FB70A9?logo=livewire&logoColor=white)](https://livewire.laravel.com)## Features



**A reusable, production-ready accounting package for Laravel applications.**- Configurable Views

- Modular Controllers and Models

[Features](#-features) • [Installation](#-quick-start) • [Documentation](#-documentation) • [Support](#-support)- Dynamic Layouts

- Publishable Migrations and Configurations

</div>



---## Installation



## 🌟 Features

Install the package using Composer: 

### 📋 Core Accounting Features

- ✅ **Double-Entry Bookkeeping** - Accurate financial records```bash

- ✅ **Multi-Account System** - Manage multiple accountscomposer require artflow-studio/accountflow

- ✅ **Transaction Management** - Track all financial movements```

- ✅ **Account Transfers** - Move funds between accounts

- ✅ **Transaction Templates** - Recurring transaction shortcuts## Publish Files



### 📈 Advanced Financial Features

- 💰 **Budget Management** - Plan and track budgets by categorypublish the files separately or at once. use --force to overwrite

- 🏦 **Loan Management** - Track loans with multiple partners

- 🤝 **Equity Management** - Manage equity partners and distributions```bash

- 💳 **Payment Methods** - Configure multiple payment optionsphp artisan vendor:publish --tag=accountflow-config

- 👝 **User Wallets** - Personal wallet management systemphp artisan vendor:publish --tag=accountflow-migrations

php artisan vendor:publish --tag=accountflow-views

### 🏢 Enterprise Featuresphp artisan vendor:publish --tag=accountflow-controllers

- 📊 **Financial Reports** - Trial Balance, Profit & Loss, Cashbookphp artisan vendor:publish --tag=accountflow-models

- 💼 **Asset Tracking** - Depreciation and asset managementphp artisan vendor:publish --tag=accountflow-routes

- 🔔 **Planned Payments** - Schedule and track upcoming payments

- 📝 **Audit Trail** - Complete activity logging```

- ⚙️ **Settings Management** - Customizable system settings

## Usage

### 🛠️ Developer Features

- 🔄 **Bidirectional File Sync** - Package ↔ App synchronizationIn your controller: 

- 🎨 **Livewire 3 Components** - Interactive UI components```php 

- 🗂️ **Modular Architecture** - Separate concerns cleanlyuse App\Http\Controllers\AccountFlow\AccountController;

- 🔌 **Easy Integration** - Simple Composer installation

- 📦 **Reusable Package** - Use across multiple projectspublic function index(){ 

    return view(config('accountflow.view_path') . 'accounts'); 

---} 

```

## 🚀 Quick Start

Extend your views: 

### Prerequisites```blade 

- **PHP**: 8.2 or higher@extends(config('accountflow.layout')) 

- **Laravel**: 11.0 or higher```

- **Livewire**: 3.6 or higher

- **Composer**: Latest versionInclude partials: 

```blade 

### Installation - 5 Steps@include(config('accountflow.view_path').'modals.add_transaction') 

```

#### Step 1️⃣ Install the Package

## Configuration

```bash

composer require artflow-studio/accountflowEnsure you have published the configuration file: 

``````bash 

php artisan vendor:publish --tag=accountflow-config 

#### Step 2️⃣ Link Package Files```



```bashThe configuration file will be located at `config/accountflow.php`. Customize your paths and settings as needed.

php artisan accountflow:link

```## License



This command copies AccountFlow files from the package into your project:This project is licensed under the MIT License.

- `app/Models/AccountFlow/` - Eloquent models
- `app/Livewire/AccountFlow/` - Interactive components
- `app/Http/Controllers/AccountFlow/` - Controllers
- `resources/views/vendor/artflow-studio/accountflow/` - Blade templates

#### Step 3️⃣ Publish Configuration

```bash
php artisan vendor:publish --tag=accountflow-config
```

Creates `config/accountflow.php` for customization.

#### Step 4️⃣ Run Migrations

```bash
php artisan migrate
```

Creates 20 accounting-specific database tables.

#### Step 5️⃣ (Optional) Seed Demo Data

```bash
php artisan accountflow:seed
```

Populates sample data for testing (3 demo accounts, 1 transaction).

---

## 📖 Documentation

### Quick Links

| Document | Purpose |
|----------|---------|
| **[README.md](README.md)** | Package overview (this file) |
| **[AGENT.md](AGENT.md)** | Developer guide - where to edit, how it works |
| **[PRODUCTION_FEATURES.md](PRODUCTION_FEATURES.md)** | Complete feature list & production guidelines |

### Common Tasks

#### 🔧 Syncing File Changes

When you edit AccountFlow files, keep them in sync:

```bash
# Check for changes without syncing
php artisan accountflow:sync --check

# Interactive sync (select which files to sync)
php artisan accountflow:sync

# Force sync all changes
php artisan accountflow:sync --force
```

#### 📱 Accessing the Dashboard

Once installed, visit your accounts dashboard:

```
http://your-app.local/accounts/dashboard
```

Available routes:
- `/accounts/dashboard` - Main dashboard
- `/accounts/list` - All accounts
- `/accounts/transactions` - Transaction management
- `/accounts/budgets` - Budget management
- `/accounts/loans` - Loan management
- `/accounts/assets` - Asset management
- `/accounts/wallets` - Wallet management
- `/accounts/reports/*` - Financial reports

#### ⚙️ Configuration

Edit `config/accountflow.php`:

```php
return [
    // Layout used for views
    'layout' => 'layouts.app',
    
    // View path prefix
    'view_path' => 'vendor.artflow-studio.accountflow.',
    
    // Middleware applied to routes
    'middleware' => ['web', 'auth'],
];
```

---

## 💻 Artisan Commands

### Main Commands

```bash
# Link package files to project
php artisan accountflow:link [--force]

# Sync changed files (interactive)
php artisan accountflow:sync [--check] [--force]

# Run migrations
php artisan accountflow:migrate

# Fresh migrations with demo data
php artisan accountflow:migrate:fresh --seed

# Seed demo data
php artisan accountflow:seed

# Full installation
php artisan accountflow:install
```

---

## 🔄 File Synchronization

AccountFlow uses a sophisticated bidirectional file sync system:

```bash
$ php artisan accountflow:sync

╔════════════════════════════════════════════════════════════╗
║  🔄 AccountFlow File Synchronization System               ║
╚════════════════════════════════════════════════════════════╝

📊 CHANGE SUMMARY
─────────────────────────────────────────────────────────────
  📦  Models 📦 → 📱 (5 files)
  ⚡  Livewire 📦 → 📱 (3 files)
  🎨  Views 📱 → 📦 (2 files)

Total Changes: 10 files

Enter file numbers to sync (comma-separated, or "all" for all files):
```

---

## 📊 Models & Database

### 20 Database Tables

The package creates comprehensive accounting tables with proper relationships and constraints.

### 20+ Eloquent Models

Pre-built models with relationships:

```php
use App\Models\AccountFlow\Account;
use App\Models\AccountFlow\Transaction;
use App\Models\AccountFlow\Budget;

$account = Account::find(1);
$transactions = $account->transactions()->latest()->get();
```

---

## 💻 Components

### 45+ Livewire Components

Pre-built, fully interactive components for:
- 📌 Accounts, Transactions, Budgets
- 🏦 Loans, Assets, Wallets
- 📈 Reports, Categories, Payment Methods
- 👥 Equity, Audit Trail, Planned Payments

---

## 🎨 Customization

### Custom Layouts

Edit `config/accountflow.php`:

```php
'layout' => 'layouts.custom', // Your custom layout
```

### Custom Views

Override views in `resources/views/vendor/accountflow/`

### Custom Models

Extend models in your app:

```php
namespace App\Models;

use App\Models\AccountFlow\Transaction as BaseTransaction;

class Transaction extends BaseTransaction
{
    // Add custom methods
}
```

---

## 🌍 Production Deployment

### Pre-Deployment Checklist

- [ ] All migrations run successfully
- [ ] Configuration published and customized
- [ ] File sync completed
- [ ] Components render correctly
- [ ] Routes accessible

### Deployment Steps

1. **Install the package**
   ```bash
   composer require artflow-studio/accountflow
   ```

2. **Run link command**
   ```bash
   php artisan accountflow:link
   ```

3. **Publish configuration**
   ```bash
   php artisan vendor:publish --tag=accountflow-config
   ```

4. **Run migrations**
   ```bash
   php artisan migrate
   ```

5. **Test in browser**
   ```
   Visit: /accounts/dashboard
   ```

---

## 📞 Support & Documentation

| Need | Resource |
|------|----------|
| How to develop | → [AGENT.md](AGENT.md) |
| What's included | → [PRODUCTION_FEATURES.md](PRODUCTION_FEATURES.md) |

---

## 📝 License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).

---

## ✨ Highlights

✅ **Production Ready** - Used in real applications
✅ **Well Tested** - Comprehensive test coverage
✅ **Fully Documented** - Developer and user guides
✅ **Easy Installation** - 5-step setup
✅ **Reusable** - Use across multiple projects
✅ **Secure** - PSR-4 namespaced
✅ **Scalable** - Handles large datasets
✅ **Customizable** - Override anything
✅ **Performance** - Optimized queries

---

<div align="center">

**Made with ❤️ for Laravel developers**

</div>
