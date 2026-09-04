# 1 — Installation

```bash
composer require artflow-studio/accountflow
php artisan accountflow:install --migrate --seed
```

`/accounts` is then live with every module enabled.

## What the installer does

| Step | Detail |
|------|--------|
| Publishes config | `config/accountflow.php` |
| Publishes assets | `public/vendor/artflow-studio/accountflow/assets/` — required for dashboard CSS |
| `--migrate` | Runs migrations (they are auto-discovered; `php artisan migrate` alone works too) |
| `--seed` | Default accounts, categories, payment methods and settings |

It will not overwrite anything you have already published unless you pass
`--force`, and it warns rather than clobbering:

```bash
php artisan accountflow:install --force
```

## Publishing individually

```bash
php artisan vendor:publish --tag=accountflow-config
php artisan vendor:publish --tag=accountflow-views      # to customise screens
php artisan vendor:publish --tag=accountflow-assets     # CSS and JS
php artisan vendor:publish --tag=accountflow-seeders
```

Models, Livewire components and controllers are deliberately **not**
publishable. Publishing them would put the same fully-qualified class name in
both the package and `app/`, and which one loaded would depend on autoloader
ordering. Override behaviour through config, container bindings and published
views instead — see [07 — Extending](07-extending.md).

## Migrations and your existing data

Every table is created **only when it does not already exist**, and later
migrations add columns and indexes only when they are missing. Re-running
migrations against a populated database is a no-op, never a rebuild.

New migrations in this package must use a `99xx` filename prefix. Laravel orders
migrations by filename, and the original one is `9900_create_accounts_tables`; a
conventional `2026_..._` name would sort *before* it, run first against an empty
schema, and silently skip its own guards.

## Seeding

```bash
php artisan accountflow:seed
```

The seeder is idempotent — it matches on natural keys, never deletes, and leaves
settings you have already changed alone. Running it twice changes nothing.

## Requirements

- PHP 8.2+
- Laravel 12 or 13
- Livewire 4
- `artflow-studio/table` ^1.5 and `artflow-studio/snippets` ^2.3

`spatie/laravel-permission` is only suggested — needed just if you point the
authorization config at role-based checks.
