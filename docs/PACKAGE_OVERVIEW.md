# AccountFlow Package - Complete Overview

Professional accounting system for Laravel applications with comprehensive service layer and reporting.

## 📦 What's Included

### Services (8 Total)

| Service | Purpose | Key Methods |
|---------|---------|-------------|
| **TransactionService** | Transaction management with auto-defaults | `create()`, `createIncome()`, `createExpense()`, `reverse()` |
| **AccountService** | Account lifecycle and balance tracking | `create()`, `getBalance()`, `recalculateBalance()`, `getStatistics()` |
| **CategoryService** | Category hierarchy and classification | `create()`, `getByType()`, `getHierarchy()`, `lock()`, `unlock()` |
| **PaymentMethodService** | Payment method configuration | `create()`, `getActive()`, `linkToAccount()`, `validate()` |
| **BudgetService** | Budget planning and tracking | `create()`, `analyze()`, `getAlertsForAccount()` |
| **ReportService** | Financial reporting | `profitAndLoss()`, `cashFlowReport()`, `balanceReport()` |
| **SettingsService** | Configuration management | `get()`, `set()`, `enableFeature()`, `disableFeature()` |
| **AuditService** | Audit trail logging | `log()`, `getRecent()`, `export()` |

### Access Methods

```php
// Facade (recommended for brevity)
use ArtflowStudio\AccountFlow\Facades\AC;
Accountflow::transactions()->create([...]);

// Container
app('accountflow')->transactions()->create([...]);

// Direct service
use ArtflowStudio\AccountFlow\App\Services\TransactionService;
TransactionService::create([...]);
```

### Database Tables (20 Total)

Core accounting infrastructure:

```
accounts               - Account master records
ac_transactions       - Transaction ledger
ac_payment_methods    - Payment configurations
ac_categories         - Income/Expense categories
ac_budgets           - Budget planning
ac_assets            - Fixed asset tracking
ac_loans             - Loan management
ac_equity_partners   - Equity management
ac_user_wallets      - User wallet system
ac_audit_trail       - Compliance logging
ac_settings          - Configuration storage
... and more
```

### Models (20+ Total)

Pre-built Eloquent models with relationships:

- Account, Transaction, Category, PaymentMethod
- Budget, AuditTrail, Setting
- Asset, AssetTransaction
- Loan, LoanUser, LoanTransaction
- EquityPartner, EquityTransaction
- Purchase, PurchaseTransaction
- Transfer, UserWallet
- And more...

---

## 🚀 Quick Start

### 1. Installation

```bash
composer require artflow-studio/accountflow
```

### 2. Link Package Files

```bash
php artisan accountflow:link
```

Copies package files to your project for customization.

### 3. Publish Configuration

```bash
php artisan vendor:publish --tag=accountflow-config
```

Creates `config/accountflow.php`.

### 4. Run Migrations

```bash
php artisan migrate
```

Creates 20 accounting database tables.

### 5. (Optional) Seed Demo Data

```bash
php artisan accountflow:seed
```

Populates sample data for testing.

---

## 💡 Usage Examples

### Example 1: Create Invoice with Transaction

```php
use ArtflowStudio\AccountFlow\Facades\AC;

// Create invoice (your logic)
$invoice = Invoice::create($data);

// Create income transaction automatically
$transaction = Accountflow::transactions()->createIncome([
    'amount' => $invoice->total,
    'category_id' => Accountflow::settings()->defaultSalesCategoryId(),
    'description' => "Invoice #{$invoice->reference}",
]);

// Log the action
Accountflow::audit()->logTransactionCreated($transaction->id, $transaction->toArray());
```

### Example 2: Monthly Budget Monitoring

```php
// Create monthly budget
$budget = Accountflow::budgets()->create([
    'account_id' => 1,
    'category_id' => 5,
    'amount' => 5000,
    'period' => 'monthly',
    'alert_threshold' => 80,
]);

// Check for alerts
$alerts = Accountflow::budgets()->getAlertsForAccount(1);

foreach ($alerts as $alert) {
    if ($alert['is_alert']) {
        notify_admin("Budget alert: {$alert['category_name']} is at {$alert['percentage_used']}%");
    }
}
```

### Example 3: Generate Financial Reports

```php
$startDate = '2024-01-01';
$endDate = '2024-12-31';

$reports = [
    'profit_loss' => Accountflow::reports()->profitAndLoss($startDate, $endDate),
    'cash_flow' => Accountflow::reports()->cashFlowReport($startDate, $endDate),
    'by_category' => Accountflow::reports()->categoryPerformance($startDate, $endDate),
    'balance' => Accountflow::reports()->balanceReport(),
];

return response()->json($reports);
```

### Example 4: Track Expenses

```php
$expense = Accountflow::transactions()->createExpense([
    'amount' => 250,
    'category_id' => 10,
    'payment_method' => 2,
    'description' => 'Office supplies',
    'date' => '2024-12-15',
]);

// Check budget status
$budgets = Accountflow::budgets()->getActive($expense->account_id);
foreach ($budgets as $budget) {
    if ($budget->category_id === $expense->category_id) {
        $analysis = Accountflow::budgets()->analyze($budget->id);
        if ($analysis['is_over_budget']) {
            Log::warning("Budget exceeded!");
        }
    }
}
```

---

## 📁 Directory Structure

```
vendor/artflow-studio/accountflow/
├── docs/                          # Documentation
│   ├── SERVICES_INDEX.md         # Complete service reference
│   ├── SERVICES_QUICK_GUIDE.md   # Usage examples
│   ├── ARCHITECTURE.md            # TransactionService architecture
│   └── MIGRATION_GUIDE.md         # Migration from old approach
├── src/
│   ├── Facades/
│   │   └── AC.php                # Main facade for short imports
│   ├── app/
│   │   ├── Services/
│   │   │   ├── TransactionService.php
│   │   │   ├── AccountService.php
│   │   │   ├── CategoryService.php
│   │   │   ├── PaymentMethodService.php
│   │   │   ├── BudgetService.php
│   │   │   ├── ReportService.php
│   │   │   ├── SettingsService.php
│   │   │   ├── AuditService.php
│   │   │   └── AccountFlowManager.php     # Service container
│   │   ├── Models/               # 20+ Eloquent models
│   │   ├── Console/Commands/     # Artisan commands
│   │   ├── Http/Controllers/     # Base controllers
│   │   └── Livewire/             # Interactive components
│   ├── config/
│   │   └── accountflow.php       # Configuration
│   ├── database/
│   │   ├── migrations/           # 20 database tables
│   │   └── seeders/             # Demo data
│   └── resources/
│       └── views/               # Blade templates
├── composer.json
├── README.md
└── AccountFlowServiceProvider.php
```

---

## ⚙️ Configuration

### config/accountflow.php

```php
return [
    'layout' => 'layouts.app',                    // Main layout
    'view_path' => 'vendor.artflow-studio.accountflow.',
    'middleware' => ['web', 'auth'],              // Route middleware
    
    // Features (enable/disable)
    'modules' => [
        'multi_accounts' => true,
        'budgets' => true,
        'loans' => true,
        'assets' => true,
        'wallets' => true,
        'reporting' => true,
    ],
];
```

### Default Settings

Configured in `ac_settings` table:

- `default_transaction_type`: 2 (expense)
- `default_payment_method_id`: 1
- `default_account_id`: 1
- `default_sales_category_id`: 2
- `default_expense_category_id`: 5

---

## 🔄 File Synchronization

Keep package customizations in sync:

```bash
# Check for changes
php artisan accountflow:sync --check

# Interactive sync
php artisan accountflow:sync

# Force sync all
php artisan accountflow:sync --force
```

---

## 📊 Available Routes

Once installed, routes are available:

```
GET     /accounts/dashboard       - Main dashboard
GET     /accounts/list            - Accounts list
GET     /accounts/transactions    - Transactions
GET     /accounts/categories      - Categories
GET     /accounts/budgets         - Budget management
GET     /accounts/reports/*       - Financial reports
POST    /accounts/transactions    - Create transaction
PUT     /accounts/transactions/:id- Update transaction
DELETE  /accounts/transactions/:id- Delete transaction
```

---

## 🛡️ Security Features

✅ **Database Transactions** - All operations use `DB::transaction()` for consistency
✅ **Validation** - Comprehensive input validation on all services
✅ **Authorization** - Middleware protection on routes
✅ **Audit Trail** - Complete logging of all actions
✅ **Account Linking** - Payment methods validated against accounts
✅ **Unique IDs** - Uses snippets package for collision-safe IDs

---

## 📈 Reporting Capabilities

### Financial Reports

- **Profit & Loss** - Revenue vs Expenses
- **Cash Flow** - Monthly inflows/outflows
- **Trial Balance** - Account balances
- **Balance Sheet** - Assets, Liabilities, Equity
- **Category Analysis** - By income/expense category
- **Payment Method Analysis** - By payment method
- **Daily Summary** - Transaction aggregation

### Export Options

```php
// Export audit logs
$data = Accountflow::audit()->export('2024-01-01', '2024-12-31');

// Export reports as arrays
$pl = Accountflow::reports()->profitAndLoss($start, $end);
```

---

## 🔌 Integration Points

### With Laravel Models

```php
use App\Models\Invoice;
use ArtflowStudio\AccountFlow\Facades\AC;

class Invoice extends Model {
    public function transaction() {
        return $this->belongsTo(Transaction::class);
    }
    
    public function saveWithTransaction() {
        $transaction = Accountflow::transactions()->createIncome([...]);
        $this->transaction_id = $transaction->id;
        $this->save();
    }
}
```

### With Controllers

```php
use App\Http\Controllers\Controller;
use ArtflowStudio\AccountFlow\Facades\AC;

class ExpenseController extends Controller {
    public function store(Request $request) {
        try {
            $expense = Accountflow::transactions()->createExpense($request->validated());
            Accountflow::audit()->logTransactionCreated($expense->id, $request->validated());
            return response()->json(['expense' => $expense]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
```

### With Events/Listeners

```php
// Listen for transaction events
Event::listen('transaction.created', function ($transaction) {
    // Update balance
    Accountflow::accounts()->recalculateBalance($transaction->account_id);
    
    // Check budget
    $budgets = Accountflow::budgets()->getActive($transaction->account_id);
    // ... handle budget alerts
});
```

---

## 📝 Artisan Commands

```bash
# Main installation
php artisan accountflow:install             # Full setup

# Link/Sync files
php artisan accountflow:link [--force]      # Link package files
php artisan accountflow:sync [--check]      # Sync changes

# Database
php artisan accountflow:migrate             # Run migrations
php artisan accountflow:migrate:fresh       # Fresh + seed
php artisan accountflow:seed                # Seed demo data

# Development
php artisan accountflow:db                  # Database status
php artisan accountflow:config              # Show configuration
```

---

## 🧪 Testing

Each service has comprehensive validation and error handling:

```php
// Services throw exceptions on invalid input
try {
    Accountflow::accounts()->delete($account); // Throws if has transactions
} catch (\Exception $e) {
    // Handle error
    Log::error($e->getMessage());
}
```

Write tests for your integration:

```php
it('creates transaction with service', function () {
    $transaction = Accountflow::transactions()->createIncome([
        'amount' => 1000,
    ]);
    
    expect($transaction)->toBeInstanceOf(Transaction::class);
    expect($transaction->type)->toBe(1);
    expect($transaction->unique_id)->toStartWith('TXN-');
});
```

---

## 🎯 Best Practices

### 1. Use Services Instead of Direct Model Creation

```php
// ❌ Avoid
Transaction::create($data);

// ✅ Prefer
Accountflow::transactions()->create($data);
```

### 2. Use Specific Methods When Available

```php
// ❌ Generic
Accountflow::transactions()->create(['type' => 1, ...]);

// ✅ Specific
Accountflow::transactions()->createIncome([...]);
```

### 3. Log Important Actions

```php
$transaction = Accountflow::transactions()->create([...]);
Accountflow::audit()->logTransactionCreated($transaction->id, $transaction->toArray());
```

### 4. Handle Exceptions

```php
try {
    $transaction = Accountflow::transactions()->create($data);
} catch (\Exception $e) {
    Log::error('Transaction failed: ' . $e->getMessage());
    return response()->json(['error' => $e->getMessage()], 422);
}
```

### 5. Cache Reports

```php
$pl = Cache::remember('pl_2024', 3600, function () {
    return Accountflow::reports()->profitAndLoss('2024-01-01', '2024-12-31');
});
```

---

## 📚 Documentation Files

| File | Purpose |
|------|---------|
| **SERVICES_INDEX.md** | Complete reference for all 8 services |
| **SERVICES_QUICK_GUIDE.md** | Usage examples and real-world scenarios |
| **ARCHITECTURE.md** | TransactionService detailed design |
| **MIGRATION_GUIDE.md** | How to migrate from manual to service-based |
| **README.md** | Installation and overview |

---

## 🤝 Contributing

Services are built with extensibility in mind. You can:

1. **Extend services** - Add custom methods to service classes
2. **Create custom services** - Add new service files following the pattern
3. **Hook into auditing** - Log custom actions with AuditService

---

## 📄 License

MIT License - See LICENSE.md

---

## 🆘 Support

- Check documentation files in `/docs`
- Review service PHPDoc comments
- Check source code examples
- Review ARCHITECTURE.md for design decisions

---

## ✨ Key Features Summary

✅ **8 Professional Services** - Complete accounting domain logic
✅ **AC Facade** - Short, clean imports
✅ **20+ Models** - Pre-built with relationships
✅ **Comprehensive Validation** - All inputs validated
✅ **Financial Reporting** - P&L, Cash Flow, Balance
✅ **Budget Tracking** - With alerts and variance analysis
✅ **Audit Trail** - Complete compliance logging
✅ **Database Transactions** - Data consistency
✅ **Auto-Defaults** - Smart defaults reduce code
✅ **Well Documented** - Multiple documentation files

---

Made with ❤️ for Laravel developers


