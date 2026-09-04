# AccountFlow — Restructure & Optimization Plan

**Package:** `artflow-studio/accountflow`
**Status:** In progress
**Baseline:** `83b1efd` (tag `0.2.8`)
**Target:** `0.3.0`

This document is the single source of truth for the restructure. It records what is
wrong, what the package will look like when finished, and the exact order of work.

---

## 1. Goal

Turn AccountFlow from an app-coupled module into a real, installable Laravel package
that is **powerful**, **safe by default**, and **obvious to use** — where the three
things an end user actually does are each a one-liner:

1. **Add a transaction** — from anywhere, in one call.
2. **Load / list transactions** — a drop-in AFTable with zero column boilerplate.
3. **Use it directly** — facade, helper, service, or model, all reaching the same code.

Everything else in this plan exists to make those three safe and fast.

---

## 2. Audit summary

Full findings from the audit pass, condensed. IDs are referenced by the task list.

### P0 — Critical

| ID | Finding |
|----|---------|
| C1 | `config/accountflow.php` ships `'middlewares' => ['web']` with `auth` commented out → **every accounting route is public** |
| C2 | **Zero authorization** in all 41 Livewire components — no `authorize()`, Gate, policy or `can()` |
| C3 | `accountflow:install` accepts `--force` but passes `'--force' => true` unconditionally → **silently overwrites the user's published config, views, models, components, controllers** |
| C4 | `AccountsTableSeeder` calls `->delete()` on `ac_categories`, `accounts`, `ac_payment_methods`, `ac_settings` before inserting, with hardcoded IDs → **destroys the chart of accounts that live transactions reference**; never resets AUTO_INCREMENT, so re-runs point payment methods at nonexistent accounts |
| C5 | `Settings::saveSettings()` uses `updateOrCreate(['key' => $k, 'type' => 2], …)` but only `key` is UNIQUE → lookup misses seeded rows (`type = 1`), INSERT fires, **unique-index violation → 500** |

### P1 — High

| ID | Finding |
|----|---------|
| H1 | `AuditService` and `BudgetService` are **never called** anywhere — the audit trail has a page, route, flag, table and records nothing |
| H2 | Provider's `spl_autoload_register` maps `App\…\AccountFlow\*` into the package **while** `install` publishes the same classes into `app/` → two files, one FQCN, load-order dependent |
| H3 | Provider `loadMigrationsFrom()` **and** `install` copies migrations to `database/migrations` → migrations run twice |
| H4 | `Account::$fillable` lists `status` (no such column), omits `opening_balance` and `active` → **opening balance is silently discarded** |
| H5 | `addToBalance`/`subtractFromBalance` are read-modify-write with **no row lock** → lost updates corrupt balances |
| H6 | `ReportService::byPaymentMethod()` / `categoryPerformance()` dereference nullable relations (`$method->id`, `$category->id`) → fatal on one uncategorised row |
| H7 | `9901_add_columns_to_account.php` is an empty no-op whose `down()` calls `dropColumn('')`; `9900`'s `down()` drops `ac_planned_payments_trx` but `up()` creates `ac_planned_payment_trx` |
| H8 | `SettingsService::set()` → `firstOrCreate(['key' => $k])` inserts with no `value`, which is `NOT NULL` |
| H9 | `EquityPartner::name` mutator stores `strtolower()`, reads `ucwords()` → "ABC Ltd" becomes "Abc Ltd", irreversibly |
| H10 | `AssetTransaction`, `LoanTransaction`, `UserWallet` declare neither `$fillable` nor `$guarded` → `$guarded = ['*']` blocks `create()` |
| H11 | Transactions list view dereferences `$row->category->icon` in a `raw` template; `category_id` is nullable → fatal row |

### P2 — Architectural

| ID | Finding |
|----|---------|
| A1 | Models / Livewire / Controllers declare the **host app's `App\` namespace**, loaded by an autoloader hack |
| A2 | Overlapping PSR-4 roots: `ArtflowStudio\AccountFlow\` → `src/` **and** `…\App\` → `src/app/` |
| A3 | Nine services are `static` methods yet bound as container **singletons** — unmockable, unswappable |
| A4 | **MySQL-locked**: 17 double-quoted SQL string literals (`type IN ("income","1",1)`) + `DATE_FORMAT` — breaks Postgres/SQLite, which is why there is no test suite |
| A5 | The `IN ("income","1",1)` defence proves `type` holds mixed strings/ints; `TransactionService` compares `==`, `ReportService` `===` |
| A6 | `ReportService` `->get()`s whole tables and groups in PHP, with N+1 on `category`/`paymentMethod` |
| A7 | Three hard couplings to the CRM: `Transaction::invoicePayment()` → `App\Models\InvoicePayment`, `UserWallet::user()` → `App\Models\User`, `role:business` in routes |
| A8 | Undeclared deps (spatie permission); `laravel/framework: "*"`, `table: "*"`, `snippets: "*"`; no `php` constraint, license or authors |
| A9 | `admin_management.check` supports a closure → **`config:cache` breaks deploys** |
| A10 | `accountflow.admin` aliased but applied to no route; `CheckFeatureEnabled` entirely dead; `CheckAdminAccess::isUserAdmin()` uses `property_exists()` on an Eloquent model (always false for DB attributes) |
| A11 | Feature flags hit the DB **per check, uncached**, with a 20-entry alias map duplicated across two methods; `Setting::defaults()` omits most mapped keys so modules are **off** on a fresh install, fixable only via the destructive seeder (C4) |

### P3 — Consistency

Two `AccountFlowManager` classes (one dead) · 21 commands, 13 of them `Test*`, no `tests/` ·
2 of 20 models define `$casts` · `accounts` is the only unprefixed table · money is
`decimal(10,2)` in some tables and `(15,2)` in others · all four helpers are
`@deprecated` on arrival · `dashboard-header.blade.php` is 1008 lines (510 inline CSS,
78 inline JS) re-sent every request · `EquityPartner` docblock documents three columns
that do not exist · `LoanTransaction::transaction()` is `hasOne` where it means
`belongsTo` · no events, observers, or domain exceptions — everything throws bare
`\Exception` · `docs/` empty, `SKILL.md` 32KB.

---

## 3. Target structure

Matches the house style already used by `artflow-studio/tenancy`: package root holds the
Laravel-facing directories, `src/` holds only PHP, one PSR-4 root.

```
AccountFlow/
├── composer.json              # one PSR-4 root, real constraints, check scripts
├── pint.json  phpstan.neon.dist  phpunit.xml.dist
├── plan.md  README.md  CHANGELOG.md  LICENSE
├── config/accountflow.php
├── database/
│   ├── migrations/            # timestamped, one concern per file
│   ├── factories/
│   └── seeders/
├── docs/
├── public/assets/{icons,payment_methods}/
├── resources/views/{layout,livewire,components,partials}/
├── routes/accountflow.php
├── src/
│   ├── AccountFlowServiceProvider.php
│   ├── Concerns/              # HasAuditTrail, TouchesAccountBalance
│   ├── Console/Commands/
│   ├── Contracts/             # one interface per service
│   ├── Enums/                 # TransactionType, CategoryType, Feature, …
│   ├── Events/                # TransactionCreated, TransactionReversed, …
│   ├── Exceptions/            # AccountFlowException + subclasses
│   ├── Facades/               # AccountFlow, AC
│   ├── Http/{Controllers,Middleware}/
│   ├── Livewire/
│   ├── Models/
│   ├── Services/
│   ├── Support/               # Money, TableColumns, aliases.php
│   └── helpers.php
└── tests/{Unit,Feature}/
```

**Namespaces.** Single root `ArtflowStudio\AccountFlow\` → `src/`.
`App\Models\AccountFlow\Transaction` → `ArtflowStudio\AccountFlow\Models\Transaction`,
and likewise for Livewire and Controllers. The `spl_autoload_register` hack is deleted.

**Backward compatibility.** `src/Support/aliases.php`, loaded via composer `files`,
registers a `class_alias()` for every legacy FQCN, so any app still importing
`App\Models\AccountFlow\Transaction` keeps working. AF_CRM's six referencing files are
updated to the real namespace regardless.

---

## 4. Public API contract

These must keep working — AF_CRM depends on them across six files:

```php
ArtflowStudio\AccountFlow\Facades\Accountflow::transactions()   // create, createIncome
ArtflowStudio\AccountFlow\Facades\Accountflow::paymentMethods() // getAll
ArtflowStudio\AccountFlow\Facades\Accountflow::settings()       // defaultPaymentMethodId,
                                                                // defaultSalesCategoryId
App\Models\AccountFlow\Transaction                              // via class_alias
route('accountflow::dashboard')                                 // and all accountflow::* names
config('accountflow.*')                                         // all existing keys
```

Anything new is additive.

---

## 5. Core flows

The three flows the plan exists to make good.

### 5.1 Adding a transaction

**Today.** `TransactionService::create()` is a 60-line static method that normalizes a
mixed-type `type`, resolves defaults through four different lookups, mutates the account
balance without a lock (H5), validates by throwing bare `\Exception`, writes `user_id`
where the column is `added_by`, and fires no events.

**Target.** One call, safe by construction:

```php
use ArtflowStudio\AccountFlow\Facades\AccountFlow;
use ArtflowStudio\AccountFlow\Enums\TransactionType;

// Minimal — everything else resolves from settings
AccountFlow::transactions()->income(1500, 'Invoice #221 payment');

// Full control
AccountFlow::transactions()->create([
    'type'           => TransactionType::Income,   // enum, int or string all accepted
    'amount'         => 1500,
    'description'    => 'Invoice #221 payment',
    'account_id'     => $accountId,      // optional → resolved from payment method
    'category_id'    => $categoryId,     // optional → default sales/expense category
    'payment_method' => $methodId,       // optional → default payment method
    'date'           => now(),           // optional → today
]);
```

Design rules:

- **`TransactionType` backed enum** (`Income = 1`, `Expense = 2`) carrying `label()` and
  `sign()`. DB values are unchanged, so no data conversion. Kills A5 and the
  `IN ("income","1",1)` defence (A4).
- **One write path.** `income()` and `expense()` delegate to `create()`; `create()` is the
  only method that inserts. Batch create wraps the same path in one transaction.
- **`BalanceUpdater` owns every balance mutation** — `lockForUpdate()` inside the DB
  transaction, used by create / update / delete / reverse. Fixes H5.
- **Validation raises domain exceptions** (`InvalidTransactionException`,
  `AccountNotFoundException`) extending `AccountFlowException`, not bare `\Exception`.
- **Events fire**: `TransactionCreated`, `TransactionUpdated`, `TransactionDeleted`,
  `TransactionReversed`. The audit trail subscribes to these — which is how H1 gets fixed
  rather than by sprinkling `AuditService::log()` calls.
- **Reversal becomes correct.** New `reversal_of_id` + `reversed_at`. A reversal is a
  contra entry of the *same* type with a negative amount, linked to the original, so P&L
  nets to zero instead of booking an income reversal as an expense.
- **`added_by` → `created_by`** with a BC accessor; fixes the silently-dropped field.

### 5.2 Loading & listing transactions (AFTable)

**Today.** [`transactions.blade.php`](resources/views/livewire/transactions/transactions.blade.php)
inlines a 30-line `@livewire('aftable', …)` array with `raw` Blade templates carrying
three levels of nested quote escaping, a hardcoded model FQCN string, a `config()` call
per row for the currency symbol, `base64_encode($row->id)` in the edit URL, and
`$row->category->icon` on a nullable relation (H11).

**Target.** Column definitions move out of Blade into a PHP builder, so every list is
declarative and reusable:

```php
// src/Support/TableColumns.php
TableColumns::transactions();   // returns the AFTable column array
TableColumns::accounts();
TableColumns::transfers();
```

```blade
{{-- resources/views/livewire/transactions/transactions.blade.php --}}
@livewire('aftable', [
    'model'         => \ArtflowStudio\AccountFlow\Models\Transaction::class,
    'columns'       => \ArtflowStudio\AccountFlow\Support\TableColumns::transactions(),
    'filters'       => \ArtflowStudio\AccountFlow\Support\TableColumns::transactionFilters(),
    'query'         => $scope,          // array form — see note
    'sortBy'        => 'date',
    'sortDirection' => 'desc',
])
```

AFTable specifics that shape this (verified against `AftableComponent::mount()` and
`HasQueryBuilding`):

- `mount($model, $columns, $filters, $actions, $index, $tableId, $query,
  $countAggregations, $sortBy, $sortDirection, $sort, $data, $vars, $customTemplate)`.
- `relation => 'category:name'` triggers automatic eager loading — **use it for every
  relation column** to fix the N+1 (A6) and H11.
- `query` accepts an **array** of constraints or a callable. Only the array form is safe:
  a closure cannot survive Livewire hydration between requests. All scoping is therefore
  expressed as arrays.
- `vars` passes values into `raw` templates, which is how the currency symbol gets
  resolved **once** instead of per row.

Also in scope here: null-safe `raw` templates (`$row->category?->name`), drop
`base64_encode()` from row URLs in favour of the plain key plus a real authorization
check, and register the embed component properly so
`<x-accountflow::table table="transactions" />` and `@accountflow(['table' => …])`
keep working.

### 5.3 Using it directly

Four entry points, all reaching the same service instances — no duplicate logic:

```php
// 1. Facade
AccountFlow::transactions()->income(500, 'Cash sale');

// 2. Helper
accountflow()->transactions()->income(500, 'Cash sale');

// 3. Container / constructor injection
public function __construct(private TransactionService $transactions) {}

// 4. Model scopes, for reading
Transaction::income()->between($from, $to)->sum('amount');
Transaction::forAccount($id)->latest('date')->paginate();
```

The four `@deprecated`-on-arrival helper functions are removed; `accountflow()` stays as
the single helper. Services become **instance-based behind `Contracts/` interfaces**
(A3), bound in the provider so a host app can swap any one of them. Static call sites
inside the package are rewritten to instance calls.

---

## 6. Data model

**Superseded.** This section originally planned a clean migration rewrite under
`migrate:fresh`, including renaming `accounts` to `ac_accounts`,
`payment_method` to `payment_method_id` and `added_by` to `created_by`.

Two later instructions cancelled all of that: migrations must never disturb
existing data, and no table, column or model may be renamed. The schema
therefore keeps its current shape, and the work became additive only.

What was actually done:

- Every table in `9900_create_accounts_tables` is created **only when absent**,
  through a `createIfMissing()` guard, so re-running the migration against a
  populated database is a no-op rather than a rebuild.
- `9901` — an empty no-op whose `down()` called `dropColumn('')` and threw — is
  repaired in place. It is kept rather than deleted, because 0.2.x installs
  already have it recorded; renaming it would make it run again (H7).
- `9902` adds `reversal_of_id` and `reversed_at` to `ac_transactions`, plus
  indexes on `(account_id, date)`, `(type, date)`, `(category_id)` and
  `(model_type, model_id)`. Every column and index is guarded by `hasColumn` /
  `hasIndex`, and `down()` deliberately does not drop the columns, since they
  carry reversal history.
- The `down()` table-name mismatch (`ac_planned_payments_trx` vs
  `ac_planned_payment_trx`) is fixed.
- `$casts` and corrected `$fillable` across the models, fixing H4 and H10.

Deliberately **not** done, and why:

| Planned | Status |
|---------|--------|
| `accounts` → `ac_accounts` | Cancelled. Foreign keys point at it; a rename cannot be made data-safe. |
| `payment_method` → `payment_method_id` | Cancelled — no column renames. |
| `added_by` → `created_by` | Cancelled — no column renames. |
| Money unified to `decimal(15,2)` | Cancelled. Changing a live column's precision is not additive. |
| `SoftDeletes` on master tables | Cancelled. Needs a `deleted_at` column on populated tables. |
| `currency` on accounts | Cancelled. Currency stays a single global setting. |
| `type` cast to enum | Cancelled — it would break every `$row->type == 1` in the views. Enum accessors added instead. |

**Settings.** `key` alone is the identity — `type` is metadata and never part of
an `updateOrCreate` match (fixes C5). `Setting::defaults()` gains every key the
`Feature` enum maps to, all modules **enabled by default**, so a fresh install
works with no seeder run (fixes A11). The seeder is idempotent, matches on
natural keys, never calls `delete()`, and preserves settings an operator has
already changed (fixes C4).

---

## 7. Phases

Each phase ends green: `composer check` passes and AF_CRM still boots.

### Phase 1 — Foundation (structure & packaging) — DONE
- [x] `composer.json`: single PSR-4 root, `php >=8.2`, `laravel/framework ^12|^13`,
      pinned `table`/`snippets`, license/authors/scripts (A2, A8)
- [x] Move `src/config`, `src/database`, `src/routes`, `src/resources`, `src/public`
      to package root; flatten the `vendor/artflow-studio/accountflow` view path
- [x] Move Models / Livewire / Controllers into `ArtflowStudio\AccountFlow\` (A1)
- [x] Delete `spl_autoload_register`; add `Support/LegacyAliases.php` BC shim
- [x] Register Livewire components explicitly (`Support/ComponentResolver.php`) so
      name lookup no longer relies on the host app's `App\Livewire` convention
- [x] Delete the duplicate `AccountFlowManager` and the four `@deprecated` helpers
- [x] Add `pint.json`, `phpstan.neon.dist`, `phpunit.xml.dist`, `LICENSE`, `CHANGELOG.md`
- [x] Update AF_CRM's referencing files (4 model refs; 2 facade-only files unchanged)
- [x] **Pulled forward from Phase 2/6:** fixed `accountflow:install --force` (C3) and
      stopped it copying migrations (H3), since the provider rewrite touched both

**Deviations from this plan, and why**

- `spatie/laravel-permission` is declared under `suggest`, not `require`. It is
  only reachable through the `role:` middleware in the route file and through
  `admin_management`; making an optional authorization hook a hard dependency of
  an accounting package is wrong. Phase 4 removes the hardcoded `role:business`
  and makes the check configurable (A7).
- `pint.json` omits `declare_strict_types`, `strict_comparison`, `strict_param`
  and `void_return`, which the tenancy preset enables. `strict_comparison` would
  rewrite `==` to `===` across code where `type` holds mixed strings and integers
  (A5), silently changing behaviour. These are enabled in Phase 3, after the
  `TransactionType` enum makes the comparisons honest.
- `phpstan` starts at level 3 rather than tenancy's 5 — the honest current state.

**Known friction:** because the package is consumed through a `path` repository,
Composer keys its cached metadata on the git commit. Editing `composer.json`
without committing leaves `vendor/composer/installed.json` stale, and
`dump-autoload` reads from there rather than from `composer.lock`. Recovering
needs the lock entry dropped and `installed.json` re-synced. This disappears once
the work is committed.

### Constraints added during the work

Two instructions changed the plan mid-flight, and both override what sections 3
and 6 originally said:

1. **Migrations must never touch existing data.** Every table in
   `9900_create_accounts_tables` is now created only when absent, via a
   `createIfMissing()` guard, and new schema goes into separate additive
   migrations that check `hasColumn` / `hasIndex` first. Nothing is dropped or
   rebuilt.
2. **No table, column or model renames.** `accounts` stays unprefixed,
   `payment_method` keeps its name (not `payment_method_id`), `added_by` is not
   renamed to `created_by`, and every model class keeps its name. Only genuinely
   broken things were changed.

A third constraint emerged from the code itself: **new migrations must use a
`99xx` prefix.** Laravel orders migrations by filename, and a conventional
`2026_..._` name sorts *before* `9900` — so on a fresh install it would run
first, find no tables, and silently skip every guard, leaving new columns
missing. The test suite caught this.

### Phase 2 — Security (P0) — DONE
- [x] `middlewares` defaults to `['web', 'auth']` (C1)
- [x] `Enums\Ability` + `Support\Authorization` + `Concerns\AuthorizesAccountFlow`;
      all 41 Livewire components authorize on mount and on every write (C2)
- [x] `accountflow.can:<ability>` route middleware across all 47 routes
- [x] `accountflow:install --force` honoured (C3)
- [x] Seeder is idempotent, never deletes, preserves operator settings (C4)
- [x] `Settings::saveSettings()` keys on `key` alone (C5)
- [x] `admin_management.check` no longer accepts closures, so `config:cache`
      works (A9); `CheckAdminAccess` uses `data_get` instead of `property_exists`
      and only redirects to a route that exists (A10)
- [x] Dead `CheckFeatureEnabled` deleted; `role:business` removed from routes (A7)

### Phase 3 — Core domain — DONE
- [x] `Enums/`: TransactionType, CategoryType, Feature, SettingType, LoanType,
      EquityTransactionType, ScheduleType, Ability
- [x] `Exceptions/`, `Events/`, `Listeners/AuditSubscriber`, `Concerns/`
- [x] `Support\BalanceUpdater` — every balance write under `lockForUpdate()` (H5)
- [x] Services are instance-based; `ForwardsStaticCalls` keeps `Service::method()`
      working for existing callers (A3)
- [x] `TransactionService` rewritten around a single write path; reversals are
      same-type contra entries; `added_by` no longer silently dropped
- [x] Audit trail wired to events, so it finally records anything (H1)
- [x] H4, H6, H8, H9, H10 fixed

**Enums are deliberately not cast onto the models.** `type` is cast to `int`,
with `transactionType()` / `categoryType()` / `equityType()` accessors returning
the enum. Casting the column to an enum would have made every existing
`$row->type == 1` in the views, and every `->where('type', 1)` on a collection,
silently false.

### Phase 4 — Data layer — DONE (within the data-safety constraint)
- [x] All 22 tables guarded by `hasTable`; `9901` no-op repaired; `down()`
      table-name mismatch fixed (H7)
- [x] `9902` adds `reversal_of_id`, `reversed_at` and the missing indexes,
      guarded by `hasColumn` / `hasIndex`
- [x] `$casts` and corrected `$fillable` across the models
- [x] Host coupling removed: `InvoicePayment` is config-driven through
      `resolveRelationUsing`; `User` resolves from `auth.providers.users.model`;
      controllers extend the framework `Controller` (A7)
- [x] Factories for Account, Category, PaymentMethod, Transaction

Further real bugs surfaced here by static analysis, all pre-existing:

- `App\Models\Accountflow\*` — a second, differently-cased namespace (lowercase
  `f`) referenced by two components. Those classes have never existed.
- `TransactionTemplate::account()` pointed at `\App\Models\Account` and
  `category()` at `AcCategory`; neither class exists anywhere, so both relations
  threw the moment anything touched them.
- `EquityTransaction` did not match its own table: `$fillable` listed three
  columns that do not exist, `type` was cast to a string-backed enum while the
  column is a tinyint, and the `partner` relation the equity list eager-loads
  was never defined.
- `Loan` defined `loan_partner()` with no foreign key — Eloquent guessed
  `loan_user_id` while the column is `loan_partner_id` — and the list
  eager-loads `loanPartner`, which did not exist.
- `SeedAccountflowData` still instantiated the seeder's pre-move namespace.

### Phase 5 — Lists & reporting — DONE
- [x] `Support\TableColumns` replaces the inline column arrays; the currency
      symbol resolves once per table through `vars` instead of once per row
- [x] Null-safe `raw` templates; `relation` on every relation column (A6)
- [x] `ReportService` rewritten to SQL-side aggregation, plus a running-balance
      `ledger()`; null category / payment method reported rather than
      dereferenced (H6)
- [x] `Support\SqlDialect` replaces `DATE_FORMAT`; all 17 double-quoted SQL
      literals gone, so the package runs on SQLite and PostgreSQL (A4)
- [x] `Support\RouteKey` accepts plain ids and still resolves legacy base64 links

### Phase 6 — DX & tests — DONE
- [x] `accountflow:install` reworked (publish / migrate / seed, `--force` honoured)
- [x] Pest + Testbench suite: **71 tests, 121 assertions**, SQLite in memory
- [x] Feature flags cached; one alias map, on the `Feature` enum (A11)
- [x] `Support\UniqueId` removes the hidden dependency on the global
      `generateUniqueID()` helper — `artflow-studio/snippets` registers it from
      its *service provider*, so it did not exist in a queue worker or in a
      package test harness
- [x] README rewritten around the three core flows; CHANGELOG written
- [x] Larastan replaces bare PHPStan, which reported ~180 false positives on
      Eloquent's static magic

### Closeout — the previously-open items

All five items listed as open at the end of the first pass are now done.

- [x] **`Test*` console commands deleted.** Nine of them, plus the
      `DEV_COMMANDS` registration. Their only unique value — proving every
      service resolves from the container — became `tests/Feature/ContainerTest.php`.
- [x] **PHPStan raised from level 1 to level 3, clean.** Getting there needed
      `@property` docblocks on the seven undocumented models, relation generics
      (`@return BelongsTo<Category, $this>`) across the model layer, and routing
      SQL aggregate rows through `->toBase()` — they are alias bags, not models,
      so typing them as models was wrong in the first place.
- [x] **Pint now enforces `strict_comparison` and `strict_param`.** This was
      only safe once every loose comparison was made explicit; see below.
- [x] **510 lines of inline CSS and 74 of JS extracted** to
      `public/assets/css/accountflow.css` and `.../js/accountflow.js`. The header
      partial dropped from 1008 lines to 422. Both blocks were verified to
      contain zero Blade expressions before extraction, and brace balance was
      checked after.
- [x] **`docs/` written** — nine guides covering installation, transactions,
      tables, reports, authorization, features, extending and upgrading.

**`SKILL.md` was kept, not retired.** The earlier plan said to retire it, but it
has a real consumer: `accountflow:skill-install` copies it to `.github/skills/`
for Copilot. It was also far less stale than assumed — the namespace rewrite had
already updated it. It was corrected in place instead.

**`declare_strict_types` is still off, deliberately.** Livewire binds form input
as strings, so a component with `strict_types` calling
`$transactions->income("500")` would raise a `TypeError` where it currently
coerces. Enabling it needs every component boundary typed first, which is a
larger and riskier change than it looks.

### Bugs found while closing out

Raising the static-analysis level surfaced defects that reading had missed:

- **`ForwardsStaticCalls` never worked.** `__callStatic` only fires for methods
  that do not exist, so it could never intercept a public instance method. The
  BC it claimed to provide was imaginary; the trait is deleted and the break is
  documented instead.
- **The service refactor was half-finished.** Five services had been converted
  to instances while four (`SettingsService`, `CategoryService`,
  `PaymentMethodService`, `BudgetService`) were still entirely static. All nine
  are instances now, asserted by a test.
- **`AccountService::updateAllAccountBalances()` was deleted during the rewrite**
  but `CreateTransfer` still called it — and the call sits inside
  `catch (Exception)`, which does not catch an `Error`, so every transfer save
  would have fatalled. Restored as an alias, and the call site updated.
- **`BudgetService` targeted a schema that does not exist** (`start_date`,
  `end_date`, `alert_threshold`, `status`). Rewritten against the real columns.
- **`SeedAccountflowData` still instantiated the seeder's pre-move namespace.**
- **`TransactionTemplate` and `Asset` relations pointed at non-existent classes.**
- **`CreateTransaction` dispatched `refreshTable` after `return`.**

### Final sweep — screens, not just services

A service nobody calls fixes nothing. After building the money services, a sweep
for components still writing model rows directly found three more:

- [x] **`CreateLoan` still called `Loan::create()`** and stopped, so recording a
      loan through the UI *still* moved no money — the service existed but the
      screen never used it. Now routed through `LoanService`, with an account
      selector added to the form (recording a loan has to know which account the
      cash lands in) and the amount locked once posted, so the ledger entry and
      the loan can never disagree.
- [x] **`CreateTransactionMultiple` bypassed `TransactionService`**, writing rows
      and then nudging the balance in a second step. That skipped validation,
      raised no events — so nothing reached the audit trail — and could leave the
      insert committed with the balance un-adjusted. Now uses `createBatch()`,
      which is atomic.
- [x] Both are covered by `ScreensMoveMoneyTest`, which drives the actual
      Livewire components rather than the services underneath them.

### Obsolete tooling removed

- [x] **`accountflow:link` and `accountflow:sync` deleted.** They symlinked and
      synchronised package classes into `app/Models/AccountFlow` — the exact
      dual-class problem this release removed, where one fully-qualified name
      existed in two files and autoloader ordering decided which won. Their
      source paths (`src/app/Models`, `src/app/Livewire`) no longer exist either,
      so they were broken as well as harmful.
- [x] `accountflow:delink` is **kept**: it removes those old links and copies
      from `app/`, which is exactly what step 1 of the upgrade runbook asks for.

### Dead schema, deliberately left alone

`ac_purchases` and `ac_purchase_trx` exist, with `Purchase` and
`PurchaseTransaction` models, but there is **no UI, no service and no route** —
the module has never been reachable. The tables are left in place because
dropping them is not a data-safe operation and they may hold rows from an older
system. They are simply unused.

### Where the package stands

| | |
|---|---|
| Tests | 162, 270 assertions, SQLite in memory |
| Static analysis | Larastan level 3, clean |
| Style | Pint with `strict_comparison` and `strict_param`, clean |
| Commands | 14, all working (down from 21, of which 9 asserted nothing and 2 were harmful) |
| Money paths | every one goes through a service, each with a test proving the balance moves |

### Genuinely still open

- **PHPStan level 4+** is not worth taking. Its findings are mostly
  "unnecessary nullsafe" on `Model::find()`, which Larastan types as
  non-nullable — following them would strip `?->` from calls that really can
  return null.
- **`declare_strict_types`** stays off. Livewire binds form input as strings, so
  a component with strict types calling `income("500")` would `TypeError` where
  it currently coerces.
- **No multi-currency**, despite the `currency_symbols` config. One currency per
  install.
- **No tax/VAT**, no period close — any date stays postable forever.
- **The bundled views are Metronic/Bootstrap markup** and are not themeable
  beyond publishing them.
- **`AccountsController` and `DefaultController`** remain thin static helpers
  that nothing routes to.
- **Assets are a register, not depreciated.**

---

## 8. Testing

Pest + Orchestra Testbench, SQLite in memory — which only becomes possible once the
MySQL-only SQL is gone (A4). Minimum coverage before a phase is called done:

- **Transactions** — create/update/delete/reverse each move the balance correctly and
  symmetrically; concurrent writes do not lose updates; validation raises the right
  domain exception; events fire once each.
- **Settings & features** — a fresh install has every module enabled with no seeder;
  saving settings twice does not collide; toggling invalidates the cache.
- **Reports** — P&L, trial balance and cashbook agree with hand-computed fixtures,
  including rows with null category and null payment method (H6).
- **Structure** — an arch test asserting PSR-4, that no `App\` namespace is declared
  inside the package, and that every registered command class exists.

---

## 9. Rollout

1. Package is symlinked into AF_CRM via a `path` repository — edits are live, no
   reinstall needed.
2. Work proceeds phase by phase on `main`; nothing is committed or pushed without
   explicit instruction.
3. AF_CRM needs `migrate:fresh` on the `ac_*` tables once Phase 4 lands.
4. `0.3.0` is tagged only after Phase 6 is green.

---

## 10. Done criteria

- `composer check` (pint + phpstan + pest) passes.
- No `App\` namespace declared anywhere inside the package.
- Fresh install → every module works without running a seeder.
- Adding a transaction, listing transactions, and direct API use each work from a cold
  install and are covered by tests.
- AF_CRM boots, its six touch points work, and `route:list --name=accountflow` is intact.
