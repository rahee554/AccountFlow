# 8 — Upgrading from 0.2.x

```bash
composer update artflow-studio/accountflow
php artisan migrate
```

That is normally all. Old class names keep resolving, and your data is not
touched.

## Namespaces moved

The package no longer declares classes in your application's `App\` namespace.

| Before | After |
|--------|-------|
| `App\Models\AccountFlow\*` | `ArtflowStudio\AccountFlow\Models\*` |
| `App\Livewire\AccountFlow\*` | `ArtflowStudio\AccountFlow\Livewire\*` |
| `App\Http\Controllers\AccountFlow\*` | `ArtflowStudio\AccountFlow\Http\Controllers\*` |
| `ArtflowStudio\AccountFlow\App\Services\*` | `ArtflowStudio\AccountFlow\Services\*` |

Every old name is lazily aliased, so existing imports keep working. Update them
when convenient, then:

```php
'legacy_aliases' => false,
```

## Breaking: services are instance-only

`TransactionService::create()` and every other static service call no longer
work. Use the facade, the helper, or injection:

```php
Accountflow::transactions()->create([...]);
accountflow()->transactions()->create([...]);
app(TransactionService::class)->create([...]);
```

Calls made **through the facade or manager** — which is how the documented API
always worked — are unaffected.

## Breaking: helper functions removed

`transaction_service()`, `create_transaction()`, `create_income()` and
`create_expense()` are gone. They shipped marked `@deprecated`. Use
`accountflow()->transactions()`.

## Breaking: models are no longer publishable

`--tag=accountflow-models`, `accountflow-livewire` and `accountflow-controllers`
no longer exist. Publishing them put the same fully-qualified class name in both
the package and `app/`, and which loaded depended on autoloader ordering.

**If you previously published them, delete those copies from `app/`** — the
package versions are authoritative now.

## Behaviour changes worth knowing

| Change | Effect |
|--------|--------|
| Routes require `auth` by default | A fresh install is no longer publicly readable |
| Every screen and action is authorized | Define gates to widen access — see [05](05-authorization.md) |
| Reversals are contra entries | A reversed sale no longer shows up as an expense; P&L nets to zero |
| Every module enabled by default | Budgets, equity, transfers, templates and audit work without seeding |
| `accountflow:install` honours `--force` | It no longer silently overwrites published files |
| The seeder never deletes | Re-seeding does not destroy your chart of accounts |
| `accountflow:test-*` commands removed | Replaced by a Pest suite: `composer test` |
| Dashboard CSS/JS extracted to files | Requires `vendor:publish --tag=accountflow-assets` |

## What did *not* change

- **No table was renamed.** `accounts` is still unprefixed.
- **No column was renamed.** `payment_method` and `added_by` keep their names.
- **No model class was renamed.**
- Migrations create a table only when it is missing, and add columns only when
  they are absent. Nothing is dropped or rebuilt.
- Config keys, route names (`accountflow::*`) and the facade API are unchanged.

## New schema

`9902_add_transaction_reversal_columns` adds, all guarded:

- `ac_transactions.reversal_of_id`, `ac_transactions.reversed_at`
- indexes on `(account_id, date)`, `(type, date)`, `(category_id)`
- an index on `ac_audit_trail (model_type, model_id)`

Its `down()` deliberately does not drop the columns, since they carry reversal
history.
