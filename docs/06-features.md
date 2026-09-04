# 6 — Features & settings

## Module toggles

```php
$features = Accountflow::features();

$features->isEnabled('budgets');       // canonical key or alias
$features->disable(Feature::Equity);
$features->enable('audit');
$features->toggle('transfers');        // returns the new state
$features->all();                      // key => bool
$features->enabled();                  // list<Feature>
```

In Blade:

```blade
@featureEnabled('budgets')  ... @endFeatureEnabled
@featureDisabled('equity')  ... @endFeatureDisabled
```

On routes:

```php
Route::middleware('accountflow.feature:budgets')->group(...);
```

## Keys and aliases

`ArtflowStudio\AccountFlow\Enums\Feature` is the single source of truth. The case
value is the canonical settings key; `aliases()` holds the short names used in
middleware and directives.

```
budgets_module            budgets
audit_trail               audit, audit-trail
transaction_templates     templates
planned_payments_module   planned_payments, planned-payments
user_wallet_module        wallets, user_wallets, users-wallets
profit_loss_report        profit_loss, profit-loss
trial_balance_report      trial_balance, trial-balance
```

0.2.x duplicated a 20-entry alias map across two methods of `FeatureService`,
and `Setting::defaults()` listed a *different* set of keys — so budgets, equity,
transfers, templates and the audit trail were all switched **off** on a fresh
install, and their routes returned 403 until the (destructive) seeder ran.

**Every module is now enabled by default.** A fresh install is fully usable with
no seeder run.

## Caching

Lookups read `Setting::values()`, cached per request and in the application
cache, invalidated on any write. 0.2.x ran a raw `DB::table('ac_settings')`
query on every single check — including inside Blade directives that fire many
times per page.

## Settings

```php
use ArtflowStudio\AccountFlow\Models\Setting;
use ArtflowStudio\AccountFlow\Enums\SettingType;

Setting::getValue('default_account_id');
Setting::put('default_account_id', 3, SettingType::Value);
Setting::currency();
Setting::flushCache();
```

Typed getters: `defaultTransactionType()`, `defaultAccountId()`,
`defaultPaymentMethodId()`, `defaultSalesCategoryId()`,
`defaultExpenseCategoryId()`, `routePrefix()`, `currency()`.

### `key` is the only identity

`ac_settings.key` is `UNIQUE`; `type` is metadata. Never include `type` in an
`updateOrCreate` match — 0.2.x did, so a row seeded as type 1 and saved as
type 2 missed the lookup, fell through to an INSERT, and hit the unique index.
Saving settings returned a 500.

## Console

```bash
php artisan accountflow:feature   # toggle interactively
php artisan accountflow:status    # module and settings state
```
