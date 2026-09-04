# AccountFlow

A complete, drop-in accounting module for Laravel — accounts, transactions,
transfers, budgets, assets, loans, equity and financial reports, with a
Livewire 4 UI and AFTable listings.

**PHP** 8.2+ · **Laravel** 12 / 13 · **Livewire** 4

---

## Install

```bash
composer require artflow-studio/accountflow
php artisan accountflow:install --migrate --seed
```

That is the whole setup. Migrations are auto-discovered, every module is
enabled by default, and `/accounts` is live.

`accountflow:install` never overwrites files you have already published unless
you pass `--force`.

---

## The three things you'll actually do

### 1. Add a transaction

```php
use ArtflowStudio\AccountFlow\Facades\Accountflow;

// Minimal — account, category and payment method resolve from your settings
Accountflow::transactions()->income(1500, 'Invoice #221 payment');
Accountflow::transactions()->expense(250, 'Office supplies');

// Full control
use ArtflowStudio\AccountFlow\Enums\TransactionType;

Accountflow::transactions()->create([
    'type'           => TransactionType::Income,  // enum, 1, or 'income'
    'amount'         => 1500,
    'description'    => 'Invoice #221 payment',
    'account_id'     => $accountId,      // optional → from the payment method
    'category_id'    => $categoryId,     // optional → default sales/expense
    'payment_method' => $methodId,       // optional → default method
    'date'           => now(),           // optional → today
]);
```

`create()` is the only method that inserts, so validation, balance updates and
events can never be bypassed. Balances are written under a row lock, so
concurrent requests cannot lose an update.

**Reversing.** A reversal is a contra entry — same type, negative amount, linked
to the original — so profit & loss nets to zero:

```php
$reversal = Accountflow::transactions()->reverse($transaction, 'Customer refund');
```

**Batches** are atomic; if one entry is invalid, none are written:

```php
Accountflow::transactions()->createBatch([
    ['type' => 1, 'amount' => 500, 'description' => 'Sale A'],
    ['type' => 2, 'amount' => 120, 'description' => 'Courier'],
]);
```

### 2. Load and list transactions

Drop the table anywhere — no column boilerplate:

```blade
@livewire('aftable', [
    'model'         => \ArtflowStudio\AccountFlow\Models\Transaction::class,
    'columns'       => \ArtflowStudio\AccountFlow\Support\TableColumns::transactions(),
    'vars'          => \ArtflowStudio\AccountFlow\Support\TableColumns::vars(),
    'sortBy'        => 'date',
    'sortDirection' => 'desc',
])
```

`TableColumns` also provides `accounts()`, `categories()`, `transfers()` and
`auditTrail()`. Every relation column declares `relation`, which is what makes
AFTable eager-load it instead of querying once per row.

Or embed a ready-made list with no layout:

```blade
<x-accountflow::table table="transactions" />
@accountflow(['table' => 'accounts'])
```

Querying directly:

```php
use ArtflowStudio\AccountFlow\Models\Transaction;

Transaction::income()->between($from, $to)->sum('amount');
Transaction::forAccount($id)->notReversed()->latest('date')->paginate();
```

### 3. Use it directly

Four entry points, all reaching the same service instances:

```php
Accountflow::transactions()->income(500, 'Cash sale');    // facade
accountflow()->transactions()->income(500, 'Cash sale');  // helper
app(TransactionService::class)->income(500, 'Cash sale'); // container
public function __construct(private TransactionService $transactions) {}
```

Services are bound as singletons and are ordinary instances, so you can mock or
swap any of them in tests.

---

## Reports

```php
$reports = Accountflow::reports();

$reports->incomeExpenseReport($from, $to);   // totals + per-category breakdown
$reports->profitAndLoss($from, $to);         // revenue, expenses, margin
$reports->cashFlowReport($from, $to);        // by month
$reports->balanceReport();                   // balance per account
$reports->byPaymentMethod($from, $to);
$reports->categoryPerformance($from, $to);
$reports->dailySummary($from, $to);
$reports->ledger($accountId, $from, $to);    // running balance
```

Every report aggregates in SQL and is driver-portable (MySQL, PostgreSQL,
SQLite, SQL Server). Rows with no category or payment method are reported as
"Uncategorised" / "Unassigned" rather than crashing the report.

---

## Authorization

Routes require authentication out of the box. Each screen and action maps to an
ability in `ArtflowStudio\AccountFlow\Enums\Ability`, resolved in this order:

1. authorization disabled in config → allow
2. your application defined the gate → the gate decides
3. the ability writes (`manage-*`) → the configured admin check
4. otherwise → any authenticated user

Override any ability with a gate:

```php
Gate::define('accountflow.manage-transactions', fn ($user) => $user->isAccountant());
```

Route middleware guards page loads; components authorize again on every action,
because a Livewire action never passes through route middleware.

---

## Feature toggles

```php
Accountflow::features()->isEnabled('budgets');   // canonical key or alias
Accountflow::features()->disable('equity');
Accountflow::features()->all();
```

```blade
@featureEnabled('budgets') ... @endFeatureEnabled
```

Keys and aliases come from `Enums\Feature`. Lookups are cached per request and
in the application cache, and invalidated on write.

---

## Configuration

```bash
php artisan vendor:publish --tag=accountflow-config
php artisan vendor:publish --tag=accountflow-views
php artisan vendor:publish --tag=accountflow-assets
```

Notable keys:

| Key | Purpose |
|-----|---------|
| `middlewares` | Middleware on every route. Ships with `auth` — leave it in. |
| `authorization.enabled` | Master switch for ability checks. |
| `admin_management.check` | Method name, invokable class, or `[Class, 'method']`. **Not** a closure — closures break `config:cache`. |
| `models.invoice_payment` | Point at your own model to get `$transaction->invoicePayment`. |
| `legacy_aliases` | Keep the pre-0.3.0 class names working. Set false once migrated. |
| `layout`, `view_path` | Where the bundled screens render. |
| `currency`, `currency_symbols` | Display formatting. |

---

## Commands

| Command | Purpose |
|---------|---------|
| `accountflow:install` | Publish, optionally migrate and seed |
| `accountflow:seed` | Seed defaults (idempotent, never deletes) |
| `accountflow:status` | Show module and settings state |
| `accountflow:feature` | Toggle a module |
| `accountflow:migrate` | Run the package migrations |
| `accountflow:db` | Inspect the AccountFlow tables |

---

## Upgrading from 0.2.x

Classes moved out of the host application's `App\` namespace:

| Before | After |
|--------|-------|
| `App\Models\AccountFlow\*` | `ArtflowStudio\AccountFlow\Models\*` |
| `App\Livewire\AccountFlow\*` | `ArtflowStudio\AccountFlow\Livewire\*` |
| `App\Http\Controllers\AccountFlow\*` | `ArtflowStudio\AccountFlow\Http\Controllers\*` |
| `ArtflowStudio\AccountFlow\App\Services\*` | `ArtflowStudio\AccountFlow\Services\*` |

`composer update` is enough — old names are lazily aliased. Update your imports
when convenient, then set `accountflow.legacy_aliases => false`.

Table and column names are unchanged, and the migrations create a table only
when it does not already exist, so your data is untouched.

If you previously published models, Livewire components or controllers into
`app/`, delete those copies: the package versions are authoritative now.

See [CHANGELOG.md](CHANGELOG.md) for the full list, and [plan.md](plan.md) for
the audit behind this release.

---

## Development

```bash
composer test      # Pest suite (SQLite in memory)
composer lint      # Pint, check only
composer format    # Pint, fix
composer analyse   # Larastan
composer check     # all of the above
```

## License

MIT — see [LICENSE](LICENSE).
