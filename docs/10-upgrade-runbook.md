# 10 — Upgrade runbook for a live install

For an application already running 0.2.x **with real data**. Follow it in order.

Nothing here renames a table, renames a column, or deletes a row. But two steps
**do change account balances** — on purpose, because those balances are wrong
today. Read the "what changes" note on each before running it.

---

## Before you start

```bash
# 1. Back up. Non-negotiable — these steps write to money tables.
mysqldump -u user -p your_database > accountflow-before-upgrade.sql

# 2. Take a snapshot of current balances so you can compare afterwards.
php artisan tinker --execute 'foreach (\ArtflowStudio\AccountFlow\Models\Account::all() as $a) { printf("%-30s %s\n", $a->name, $a->balance); }' \
  > balances-before.txt
```

---

## Step 1 — Update the package

```bash
composer update artflow-studio/accountflow
```

Nothing breaks at this point. Old class names (`App\Models\AccountFlow\*`) still
resolve through a compatibility shim.

**One thing to check:** if you ever ran `vendor:publish --tag=accountflow-models`
(or `-livewire`, or `-controllers`), delete those copies from `app/`:

```bash
rm -rf app/Models/AccountFlow app/Livewire/AccountFlow app/Http/Controllers/AccountFlow
```

They are no longer publishable. Leaving them means the same class name exists
twice and which one loads depends on autoloader ordering.

**Check `admin_management` before you rely on it.** If you use
`spatie/laravel-permission` or any role system, `check => 'isAdmin'` almost
certainly resolves to false for everyone — `hasRole()` is a method, not an
attribute, and no fallback understands it. That would deny every "manage-*"
action in AccountFlow to every user, including your own admin. Set the role
explicitly:

```php
// config/accountflow.php
'admin_management' => [
    'roles' => 'business',   // whatever role your admins actually hold
    'check' => 'isAdmin',    // harmless to leave as a fallback
],
```

Confirm it before moving on:

```bash
php artisan tinker --execute '
$user = \App\Models\User::role("business")->first(); // or however you find an admin
var_dump(\ArtflowStudio\AccountFlow\Support\Authorization::isAdmin($user));
'
```

`true` means the fix is in place. `false` means nobody can manage anything
until `roles` (or `check`) actually matches your user model.

---

## Step 2 — See what is wrong, before changing anything

```bash
php artisan accountflow:diagnose
```

Read-only. It reports:

- account balances that disagree with the transaction ledger
- transfers that never moved any money
- loans and equity movements recorded with no cash movement
- planned payments that were due and never ran
- transactions pointing at deleted accounts

Keep the output. It is the list of what the next steps will fix, and roughly
what the numbers should move by.

---

## Step 3 — Migrate

```bash
php artisan migrate
```

**What changes:** three additive migrations. New nullable columns
(`transfer_id`, `reversal_of_id`, `reversed_at`, `from_trx_id`, `to_trx_id`),
some indexes, and `ac_transfers.created_by` widened to nullable. Every table is
created only if missing and every column added only if absent, so re-running is
a no-op. No data is touched.

---

## Step 4 — Post the transfers that never moved money

**Only if `diagnose` reported unposted transfers.**

```bash
php artisan accountflow:backfill-transfers --dry-run   # look first
php artisan accountflow:backfill-transfers
```

**What changes — read this.** Before 0.3.0 a transfer wrote a row to
`ac_transfers` and no ledger entries, so the money never actually moved. This
posts the two missing entries per transfer and adjusts both balances.

**Balances will change.** For each historical transfer of amount *X*, the source
account drops by *X* and the destination rises by *X*. That is the correction —
those transfers were never applied. Compare against the `--dry-run` list.

If your team compensated for the bug by manually entering matching income and
expense transactions, **those manual entries are still there** and you would now
be double-counting. Check for them before running this, and reverse the manual
pair (`AccountFlow::money()->undo($transaction)`) rather than deleting it.

---

## Step 5 — Rebuild balances from the ledger

```bash
php artisan accountflow:recalculate-balances --dry-run
php artisan accountflow:recalculate-balances
```

**What changes:** sets each `accounts.balance` to
`opening_balance + Σ income − Σ expense` from the transactions themselves.

This corrects drift left by the asset screen, which used to write a transaction
without updating the balance — so the stored figure was too high by the value of
every asset purchase ever recorded.

Safe to re-run: the ledger is the source of truth and the result is the same
every time.

---

## Step 6 — Decide about historical loans and equity

`diagnose` may report loans or equity movements with no ledger entry. These
**cannot be repaired automatically** — the package does not know which account
the money should have come from or gone to, or on what date.

Your options, per record:

1. **Leave them.** They stay as a register with no cash effect, exactly as they
   behaved before. Nothing gets worse.
2. **Post them by hand**, if you know the account and date:
   ```php
   AccountFlow::money()->received(500000, 'Bank loan (historical)', account: 'Bank Account');
   ```
3. **Re-enter them** through the loan or equity screens, which now post
   correctly, and delete the old register rows.

Option 1 is the safe default. Only do 2 or 3 if the missing cash actually
matters to your reporting.

---

## Step 6b — Staff wallets, if you use them

`diagnose` may report wallets with transfers recorded but a zero balance. The
module never maintained balances, so there is nothing to rebuild *from* — the
top-ups and settlements that would explain a balance were never recorded either.

Set each wallet's opening balance by hand to what the person actually holds:

```php
AccountFlow::wallets()->topUp($userId, 5000, account: 'Cash Account');
```

From then on the screens keep it correct.

---

## Step 7 — Turn on the scheduler for planned payments

Planned payments never posted before, because nothing ran them. If you use that
module, add this to `routes/console.php`:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('accountflow:post-planned-payments')->dailyAt('01:00');
```

**Check the dates first.** A payment whose `next_run_date` is a year old will
post *with that date*:

```bash
php artisan accountflow:post-planned-payments --dry-run
```

If the backlog is not wanted, move the dates forward before enabling the
schedule.

---

## Step 8 — Publish the assets

```bash
php artisan vendor:publish --tag=accountflow-assets
```

Required. The dashboard's CSS and JS used to be inlined into every page render
and are now cacheable files. Skip this and the dashboard loses its styling.

Your layout needs `@stack('styles')` in `<head>` and `@stack('scripts')` before
`</body>` — most already do.

---

## Step 9 — Confirm

```bash
php artisan accountflow:diagnose      # should now be clean, or only report step 6
```

Compare balances with your snapshot:

```bash
php artisan tinker --execute 'foreach (\ArtflowStudio\AccountFlow\Models\Account::all() as $a) { printf("%-30s %s\n", $a->name, $a->balance); }' \
  > balances-after.txt
diff balances-before.txt balances-after.txt
```

Every difference should be explainable by step 4 (a historical transfer) or
step 5 (an asset purchase that never left the account). If a difference is not
explainable, stop and restore the backup.

---

## Step 10 — Optional tidy-up

```php
// config/accountflow.php — once your imports use the current namespaces
'legacy_aliases' => false,
```

If your users are business owners rather than accountants, consider hiding the
report that cannot balance in a cash book:

```php
AccountFlow::features()->disable('trial_balance');
```

See [09 — The accounting model](09-accounting-model.md) for why.

---

## Rolling back

Nothing in this upgrade drops data, so a rollback is a restore:

```bash
mysql -u user -p your_database < accountflow-before-upgrade.sql
composer require artflow-studio/accountflow:^0.2.8
```

`php artisan migrate:rollback` deliberately does **not** drop the new columns —
they hold the only link between a transfer and its ledger entries, and dropping
them would orphan the postings and silently change balances again.

---

## Code changes you may need

Almost none. The facade API is unchanged. Two exceptions:

**Static service calls no longer work.** Services are instances now:

```php
TransactionService::create([...]);                  // ✗ no longer works
AccountFlow::transactions()->create([...]);         // ✓
app(TransactionService::class)->create([...]);      // ✓
```

**The deprecated helper functions are gone:**

```php
create_income([...]);                               // ✗ removed
AccountFlow::money()->received(1500, 'Sale');       // ✓ simplest
AccountFlow::transactions()->income(1500, 'Sale');  // ✓ explicit
```
