# 3 — Tables & listings

Listings are rendered by [`artflow-studio/table`](https://github.com/artflow-studio/table)
(AFTable). Column definitions live in PHP, not inline in Blade.

## The short version

```blade
@livewire('aftable', [
    'model'         => \ArtflowStudio\AccountFlow\Models\Transaction::class,
    'columns'       => \ArtflowStudio\AccountFlow\Support\TableColumns::transactions(),
    'vars'          => \ArtflowStudio\AccountFlow\Support\TableColumns::vars(),
    'sortBy'        => 'date',
    'sortDirection' => 'desc',
])
```

## Available column sets

| Method | For |
|--------|-----|
| `TableColumns::transactions()` | Ledger entries |
| `TableColumns::accounts()` | Accounts with balances |
| `TableColumns::categories()` | Category tree |
| `TableColumns::transfers()` | Transfers |
| `TableColumns::auditTrail()` | Audit log |

`TableColumns::vars()` supplies the currency symbol and icon base path, resolved
**once per table** rather than once per rendered row.

## Why the definitions moved out of Blade

The 0.2.x transactions view carried a 30-line column array with three levels of
nested quote escaping, a `config()` lookup per row for the currency symbol, and
`$row->category->icon` on a nullable relation — one uncategorised transaction was
enough to fatal the page. All the `raw` templates are null-safe now.

## Eager loading

Every relation column declares `relation`, which is what tells AFTable to
eager-load it:

```php
[
    'key'      => 'category_id',
    'label'    => 'Category',
    'relation' => 'category:name',   // <- without this, one query per row
    'raw'      => '...',
]
```

## Scoping a table

`query` accepts an **array** of constraints:

```blade
@livewire('aftable', [
    'model'   => \ArtflowStudio\AccountFlow\Models\Transaction::class,
    'columns' => \ArtflowStudio\AccountFlow\Support\TableColumns::transactions(),
    'query'   => ['account_id' => $accountId],
])
```

AFTable also accepts a callable there, but **do not use one**: a closure cannot
survive Livewire hydration between requests.

## Embedding a ready-made list

Both forms render the list with no page layout or header:

```blade
<x-accountflow::table table="transactions" />
@accountflow(['table' => 'accounts'])
```

Recognised keys: `transactions`, `accounts`, `transfers`, `categories`,
`planned-payments`, `assets`, `asset-transactions`, `equity-transactions`,
`equity-partners`, `loans`, `budgets`.

The table is bare — no page title, no "Add Record" button, no nav header —
so it drops cleanly into a `<div>` on any page you own, at any width.

## Embedding a ready-made "add record" form

The companion to `<x-accountflow::table>`. Same idea, but for creating (or
editing) a record instead of listing them:

```blade
<x-accountflow::create table="transactions" />
<x-accountflow::create table="accounts" />
<x-accountflow::create table="categories" />
<x-accountflow::create table="payment-methods" />
```

Pass an `id` to edit an existing record instead of creating a new one:

```blade
<x-accountflow::create table="accounts" :id="$account->id" />
```

A typical page combines both — a form to add a record, and the list right
below it, updating live once the form saves:

```blade
<div>
    <x-accountflow::create table="transactions" />
    <x-accountflow::table table="transactions" />
</div>
```

That works because every bundled "Create" component dispatches a
`refreshTable` browser event on save, and every bundled list listens for it
— no wiring required on your part.

Recognised keys today: `transactions`, `accounts`, `categories`,
`payment-methods`. (Fewer than the table map — only the forms that have been
made embed-safe so far. Ask if you need another one added.)

Or mount a component directly:

```blade
<livewire:accountflow.transactions.transactions :standalone="true" />
```

## Component names

Every bundled component resolves under two prefixes:

```
accountflow.transactions.transactions    (current)
account-flow.transactions.transactions   (0.2.x, still works)
```

Names map to the directory structure — `accountflow.accounts.accounts-list`
resolves to `Livewire\Accounts\AccountsList`. Registration is explicit, so it
does not depend on the host application's `App\Livewire` convention or on the
legacy-alias shim being enabled.

## Row links

Edit links carry the plain record id. 0.2.x base64-encoded them, which is
obfuscation rather than authorization and defeats route-model binding. Old
base64 URLs still resolve, via `Support\RouteKey`.
