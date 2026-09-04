# Changelog — artflow-studio/accountflow

All notable changes are documented here.
Format: `[version] - date — summary`

---

## [Unreleased] — 0.3.0

The restructure release: AccountFlow becomes a real package rather than a module
wired into one application. See [plan.md](plan.md) for the audit this came out
of, and [docs/08-upgrading.md](docs/08-upgrading.md) for the upgrade path.

**Upgrading is normally just `composer update` + `php artisan migrate`.** No
table, column or model was renamed, migrations never touch existing data, and
the old class names keep resolving.

### 🔒 Security

- **Routes were public by default.** `config/accountflow.php` shipped with
  `auth` commented out, so a fresh install exposed the entire ledger —
  balances, transactions, reports — to anonymous visitors. `auth` is now in the
  default middleware stack.
- **No component had any authorization.** None of the 41 Livewire components
  checked anything; route middleware was the only gate, and per the previous
  point there effectively wasn't one. Every screen and action now maps to an
  ability in `Enums\Ability`, enforced by `accountflow.can:<ability>` middleware
  **and** re-checked inside each component — a Livewire action arrives over
  `livewire/update` and never passes through route middleware.
- **`config:cache` could not be used.** `admin_management.check` accepted a
  closure, which is unserializable. It now takes a method name, an invokable
  class, or a `[Class, 'method']` pair.
- `CheckAdminAccess` used `property_exists()` to detect `is_admin` / `role`,
  which is always `false` for an Eloquent attribute — so both fallbacks were
  unreachable. It uses `data_get()` now, and only redirects to a route that
  actually exists (it previously hardcoded `dashboard` and threw
  `RouteNotFoundException` in applications without one).
- The hardcoded `role:business` middleware is gone from the route file.

### 💥 Money never moved

- **Transfers had no effect on any balance.** A transfer wrote one row to
  `ac_transfers` and nothing else. Balances are derived from `ac_transactions`,
  so the money never moved — and the screen then called `recalculateAll()`,
  which rebuilt balances from a ledger with no record of the transfer, erasing
  any earlier correction too. Transfers are now posted as a linked pair of
  ledger entries via a new `TransferService`, excluded from profit & loss
  (moving your own cash is neither revenue nor a cost), and reposted correctly
  on edit or delete. Run `accountflow:backfill-transfers` once after migrating
  to post the missing entries for historical transfers.
- **`ac_transfers.created_by` was NOT NULL**, so only an authenticated request
  could record a transfer — the scheduler, an importer or a command hit a
  constraint violation. Widened to nullable.
- **Recording a loan on the loan screen still moved no money.** `LoanService`
  posts to the ledger, but `CreateLoan` never called it — it wrote
  `Loan::create()` and stopped. The screen now uses the service and asks which
  account the cash lands in, and a posted loan's amount can no longer be edited
  in a way that would leave the ledger and the loan disagreeing.
- **Multi-entry transactions bypassed `TransactionService`**, writing rows and
  then adjusting the balance in a separate step. That skipped validation, raised
  no events — so nothing reached the audit trail — and could leave rows inserted
  with the balance un-adjusted. It now uses `createBatch()`, which is atomic.
- **Removed `accountflow:link` and `accountflow:sync`.** They symlinked package
  classes into `app/Models/AccountFlow`, recreating the dual-class problem this
  release removed; their source paths no longer existed either, so they were
  broken as well as harmful. `accountflow:delink`, which *removes* those old
  copies, is kept — it is what the upgrade runbook asks you to run.
- **Buying an asset did not take the money out.** `CreateAssetTransaction`
  built `new Transaction(...)->save()` directly, bypassing the only write path
  that moves a balance — so the purchase was recorded and the cash stayed in
  the account. It also redirected to `assets.transactions.index`, which is not
  a registered route name, so it threw after saving. Both fixed, via a new
  `AssetService` with `purchase()`, `sell()` and `netCost()`.
- **Receiving or giving a loan moved no money.** `CreateLoan` wrote a row to
  `ac_loans` and stopped. New `LoanService` posts to the ledger — borrowing
  brings cash in, lending sends it out — and adds `repay()`, `outstanding()`
  and `summary()`, keeping the loan status in step.
- **Equity could not be recorded at all.** `CreateEquityTransaction` had only
  `mount()` and `render()`; there was no save method, so a partner could invest
  and nothing happened anywhere — while the equity list could delete rows it
  had no way to create. New `EquityService` with `contribute()`, `withdraw()`,
  `shareProfit()` and `shareLoss()`; contributions and withdrawals move cash,
  profit and loss shares move only equity.
- **The whole staff-wallet module was inert.** `ac_user_transfers` had no model
  at all, the create screen had only `mount()` and `render()` with no save
  method, and nothing ever updated `ac_user_wallets.balance` — the page opened
  and achieved nothing. Added the missing `UserTransfer` model and a
  `WalletService` that distinguishes the two kinds of movement: `topUp()` and
  `settle()` cross between the business and a person so they post to the ledger,
  while `transfer()` passes money between two people and correctly posts
  nothing, since the business still holds the same total. Wallet balances are
  written under a row lock, like account balances.
- **`accountflow:seed` had never worked.** It guarded on
  `Schema::hasTable('ac_accounts')`, but the table is called `accounts`, so it
  always reported "AccountFlow tables not found" and exited. A second command
  also claimed the `accountflow:seed` signature and silently shadowed it — that
  one failed too, checking `class_exists('AccountsTableSeeder')` without a
  namespace. The duplicate is deleted, and the surviving command works, reports
  what it added, and no longer warns about deleting data it does not delete.
- **Planned payments could not be created, and never posted.**
  `PlannedPayment::$fillable` listed `trx_id`, `due_date`, `period`,
  `auto_post_date` and `recurring` — none of which are columns — so every save
  failed with `Unknown column 'due_date'`. Separately, nothing ever ran the
  schedule: `auto_post`, `last_run_date` and `next_run_date` existed but no
  command, job or service used them. Fixed the model and form, and added
  `PlannedPaymentService` plus `accountflow:post-planned-payments` for the
  scheduler.

### 💥 Data-loss fixes

- **`accountflow:install` destroyed your customisations.** It accepted
  `--force`, then passed `--force` to every `vendor:publish` regardless —
  silently overwriting published config, views, models, components and
  controllers. It now honours the flag and warns instead.
- **The seeder deleted live data.** It began with `->delete()` on
  `ac_categories`, `accounts`, `ac_payment_methods` and `ac_settings`, inserting
  at hardcoded ids — destroying the chart of accounts that live transactions
  referenced by foreign key, and (never resetting AUTO_INCREMENT) leaving
  payment methods pointing at accounts that no longer existed. Its
  `dummy_data_seed` branch additionally wiped `ac_transactions`, `ac_transfers`,
  `ac_loans` and `ac_user_wallets`. The seeder is now idempotent, matches on
  natural keys, never deletes, and preserves settings you have changed. The
  dummy-data branch is gone; test data belongs in factories.
- **Migrations are guarded.** Every table is created only when absent, and new
  columns and indexes are added only when missing. Re-running against a
  populated database is a no-op.

### 🐛 Correctness

- **Reversals were booked as the opposite type**, so a reversed sale appeared as
  an expense and inflated both revenue and costs. A reversal is now a contra
  entry — same type, negative amount, linked to the original via
  `reversal_of_id` — so profit & loss nets to zero.
- **Account balances raced.** `addToBalance()` / `subtractFromBalance()` were
  read-modify-write with no lock, so concurrent writes lost updates. All balance
  writes now go through `Support\BalanceUpdater`, which holds a row lock.
- **Opening balances were silently discarded.** `Account::$fillable` listed
  `status` (never a column) and omitted `opening_balance` and `active`.
- **Saving settings returned a 500.** `ac_settings.key` is UNIQUE, but the code
  matched on `['key', 'type']`, missed rows stored under a different type, fell
  through to INSERT and hit the unique index.
- **Most modules were switched off on a fresh install.** `Setting::defaults()`
  omitted most of the keys the feature checks looked up, so budgets, equity,
  transfers, templates and the audit trail returned 403 until the (destructive)
  seeder ran. Every module is enabled by default now.
- **The audit trail recorded nothing.** `AuditService` was never called from
  anywhere, despite having a table, a settings flag, a route and a UI page. It
  is now driven by domain events through `Listeners\AuditSubscriber`.
- **Reports crashed on a transaction with no payment method.** `payment_method`
  is nullable and the code dereferenced `$group->first()->paymentMethod->id`.
  Such rows are reported as `Unassigned` now.
- **`BudgetService` was written against a schema that does not exist** — it read
  and wrote `start_date`, `end_date`, `alert_threshold` and `status`, none of
  which are columns. `analyze()` ran `whereBetween('date', [null, now()])`,
  compared a percentage against `null` so every budget "alerted", and reported
  every budget as inactive. Rewritten against the real columns, deriving the
  window from `period` + `year` + `month`.
- **`EquityTransaction` did not match its own table**: `$fillable` listed three
  phantom columns, `type` was cast to a string-backed enum against a tinyint
  column, and the `partner` relation the equity list eager-loads was never
  defined.
- **`Loan::loan_partner()` had no foreign key** — Eloquent guessed
  `loan_user_id` while the column is `loan_partner_id` — and the loans list
  eager-loads `loanPartner`, which did not exist at all.
- **`TransactionTemplate::account()` and `category()` pointed at classes that do
  not exist** (`\App\Models\Account`, `AcCategory`), so both threw on contact.
- **Two components imported `App\Models\Accountflow\*`** — a second,
  differently-cased namespace whose classes have never existed.
- `9901_add_columns_to_account` was an empty no-op whose `down()` called
  `dropColumn('')` and threw; `9900`'s `down()` dropped `ac_planned_payments_trx`
  while `up()` created `ac_planned_payment_trx`.
- `EquityPartner` stored names via `strtolower()` and read them back through
  `ucwords()`, so "ABC Ltd" became "Abc Ltd" and "McDonald" became "Mcdonald",
  irreversibly. Names are stored as entered.
- `AssetTransaction`, `LoanTransaction` and `UserWallet` declared neither
  `$fillable` nor `$guarded`, so Eloquent's `$guarded = ['*']` blocked every
  `create()` call.
- The transaction update path wrote `user_id`, but the column is `added_by`, so
  the value was silently dropped.
- `CreateTransaction` dispatched `refreshTable` *after* its `return`, so the
  table never refreshed.

### ⚡ Performance & portability

- **The package was MySQL-only.** 17 double-quoted SQL string literals
  (`type IN ("income","1",1)`) plus `DATE_FORMAT()` — `"income"` is an
  *identifier* in PostgreSQL and in SQLite under ANSI_QUOTES. All replaced;
  `Support\SqlDialect` handles the per-driver date expression. This is what
  made a real test suite possible.
- **Reports loaded whole tables into PHP.** Every method called `->get()` on all
  matching transactions and grouped in memory, with an N+1 on `category` and
  `paymentMethod` inside each map. Everything aggregates in SQL now.
- Feature flags are cached per request and in the application cache. They
  previously ran a raw query on *every* check, including inside Blade directives
  that fire many times per page.
- Indexes added on `ac_transactions (account_id, date)`, `(type, date)`,
  `(category_id)` and `ac_audit_trail (model_type, model_id)`.
- 510 lines of CSS and 74 of JavaScript were inlined into every page render;
  they are now cacheable files under `public/assets/`. The header partial went
  from 1008 lines to 422.

### 🏗 Structure

**Breaking — namespaces.** The package no longer declares classes in the host
application's `App\` namespace.

| Before | After |
|--------|-------|
| `App\Models\AccountFlow\*` | `ArtflowStudio\AccountFlow\Models\*` |
| `App\Livewire\AccountFlow\*` | `ArtflowStudio\AccountFlow\Livewire\*` |
| `App\Http\Controllers\AccountFlow\*` | `ArtflowStudio\AccountFlow\Http\Controllers\*` |
| `ArtflowStudio\AccountFlow\App\Services\*` | `ArtflowStudio\AccountFlow\Services\*` |

Every old name is lazily aliased, so existing applications keep working. Set
`accountflow.legacy_aliases => false` once migrated.

- **Removed the autoloader hack.** The provider used `spl_autoload_register()`
  to load `App\…\AccountFlow\*` out of the package. Ordinary PSR-4 now.
- **One PSR-4 root.** The overlapping second root (`…\App\` → `src/app/`) is gone.
- **Standard layout.** `config/`, `database/`, `routes/`, `resources/` and
  `public/` moved from inside `src/` to the package root.
- **Livewire components are registered explicitly** (`Support\ComponentResolver`),
  so name lookup no longer depends on the host's `App\Livewire` convention. Both
  `accountflow.*` and the 0.2.x `account-flow.*` prefixes resolve.
- **Models, Livewire components and controllers are no longer publishable.**
  Publishing them put the same fully-qualified class name in both the package
  and `app/`, and which one loaded depended on autoloader ordering. **If you
  published them before, delete those copies from `app/`.**
- Added `Enums/` (TransactionType, CategoryType, Feature, SettingType, LoanType,
  EquityTransactionType, ScheduleType, Ability), `Events/`, `Exceptions/`,
  `Listeners/`, `Contracts`-style service boundaries and `Support/`.
- Removed the duplicate `AccountFlowManager` (two classes, one dead).
- Real dependency constraints: `php >=8.2`, `laravel/framework ^12.0|^13.0`,
  `artflow-studio/table ^1.5`, `artflow-studio/snippets ^2.3` — replacing `"*"`.
  Added license, authors and `format` / `lint` / `analyse` / `test` / `check`.

### 💥 Breaking — services are instance-only

Every service is an ordinary instance resolved from the container. 0.2.x
declared them `static` while *also* binding them as container singletons — two
contradictory designs that made them impossible to mock, extend or inject.

```php
// no longer works
TransactionService::create([...]);

// use any of these
Accountflow::transactions()->create([...]);
accountflow()->transactions()->create([...]);
app(TransactionService::class)->create([...]);
```

Calls made through the facade or manager — the documented API — are unaffected.

### 💥 Breaking — removed

- The four helper functions that shipped marked `@deprecated`:
  `transaction_service()`, `create_transaction()`, `create_income()`,
  `create_expense()`. Use `accountflow()->transactions()`.
- The nine `accountflow:test-*` console commands. They printed check marks
  rather than asserting anything; a real Pest suite replaces them.
- `CheckFeatureEnabled` middleware (dead — never aliased or referenced).

### 🔌 Decoupling

- `Transaction::invoicePayment()` hardcoded `\App\Models\InvoicePayment`, tying
  the package to one CRM. Set `accountflow.models.invoice_payment` to opt in.
- User relations resolve from `auth.providers.users.model` instead of assuming
  `App\Models\User`.
- Controllers extend the framework's `Controller`, not
  `App\Http\Controllers\Controller`.
- `Support\UniqueId` removes a hidden dependency on the global
  `generateUniqueID()` helper. `artflow-studio/snippets` registers that from its
  *service provider*, so it did not exist in a queue worker or a test harness.
  The helper is still preferred when available, so ids keep their existing shape.

### 🧪 Tooling

- **Pest + Orchestra Testbench: 100 tests, 144 assertions**, SQLite in memory.
- Factories for Account, Category, PaymentMethod and Transaction, resolved
  through `Concerns\HasPackageFactory`.
- **Larastan at level 3, clean.** Plain PHPStan reported ~180 false positives on
  Eloquent's static magic; Larastan replaced it, and the genuine findings it
  surfaced are listed under Correctness above.
- **Pint with `strict_comparison` and `strict_param`.** These could not be
  enabled while `type` comparisons mixed strings and ints; every comparison is
  now explicit, so the rules are safe.
- `pint.json`, `phpstan.neon.dist`, `phpunit.xml.dist`, `LICENSE`.
- Rewritten `README.md` and a `docs/` set; `SKILL.md` corrected.
- [docs/09-accounting-model.md](docs/09-accounting-model.md) states plainly that
  this is a **cash book, not a double-entry general ledger** — the trial balance
  does not balance by construction and the balance sheet is a reconciliation
  hint, not an identity. Read it before relying on the package for statutory
  accounts.

### Upgrading

1. `composer update artflow-studio/accountflow`
2. `php artisan migrate` — additive and guarded
3. `php artisan vendor:publish --tag=accountflow-assets` — required for the
   dashboard CSS, now that it is a file rather than inline
4. Delete any previously published `app/Models/AccountFlow`,
   `app/Livewire/AccountFlow` or `app/Http/Controllers/AccountFlow` copies
5. Replace static service calls with the facade, helper or injection
6. When convenient, update imports and set `accountflow.legacy_aliases => false`
