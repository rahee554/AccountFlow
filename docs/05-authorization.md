# 5 — Authorization

## Two independent guards

| Guard | Question |
|-------|----------|
| `accountflow.feature:<key>` | Is this module switched on for this install? |
| `accountflow.can:<ability>` | May this user do it? |

Both are applied in the package's route file.

## Abilities

Every screen and action maps to a case of
`ArtflowStudio\AccountFlow\Enums\Ability`. `view-*` abilities gate reading a
screen; `manage-*` abilities gate anything that writes.

```
view-dashboard          view/manage-transactions    view/manage-accounts
view/manage-categories  view/manage-payment-methods view/manage-transfers
view/manage-budgets     view/manage-assets          view/manage-loans
view/manage-equity      view/manage-planned-payments
view/manage-wallets     view/manage-templates
view-reports            view-audit-trail
manage-settings         manage-features
```

## How a decision is made

1. `accountflow.authorization.enabled` is `false` → allow
2. Your application defined the gate → **the gate decides**
3. The ability writes (`manage-*`) → the configured admin check
4. Otherwise → any authenticated user

Step 4 is safe because the routes already require authentication, and it means
an application that defines no gates still gets a working module rather than a
wall of 403s — while anything that writes stays admin-only.

## Overriding

```php
use Illuminate\Support\Facades\Gate;

Gate::define('accountflow.manage-transactions', fn ($user) => $user->isAccountant());
Gate::define('accountflow.view-reports',        fn ($user) => $user->can('see-finances'));
```

The prefix is configurable via `accountflow.authorization.gate_prefix`.

## Recognising an administrator

Two independent ways to grant it — either is enough on its own:

```php
'admin_management' => [
    'enabled'     => true,
    'roles'       => 'business',  // or ['admin', 'business']
    'check'       => 'isAdmin',   // method on your user model
    'redirect_to' => null,        // route name, or null to abort
    'abort_code'  => 403,
],
```

**`roles`** is the simplest option if you use `spatie/laravel-permission` (or any
user model with `hasRole()` / `hasAnyRole()`): give it a role name or a list.
This is how a host application says "the `business` role manages AccountFlow"
purely through config — no route file to edit, no gate to define.

**`check`** is the fallback: a method name on your user model, an invokable
class name, or a `[Class::class, 'method']` pair. **Closures are not
supported** — they cannot be serialized, and a closure here makes
`php artisan config:cache` fail.

If neither matches, common attributes are tried (`is_admin`, `admin`,
`role === 'admin'`) via `data_get`. If nothing matches at all, nobody is an
admin — a safe failure, not a silent wildcard.

**A `spatie/laravel-permission` gotcha worth knowing.** Roles are checked with
`hasRole()`, a *method* — not an attribute. If your user model has no
`isAdmin()` method (most don't), `check => 'isAdmin'` silently resolves to
false for every user, including a real admin, because none of the attribute
fallbacks understand a method call. Set `roles` and it works regardless of
what `check` says. This is exactly the trap 0.2.x fell into: it hardcoded
`role:business` into its route file instead of making it configurable, so a
package update couldn't fix it — the config option didn't exist.

`redirect_to` is only followed if the named route actually exists; 0.2.x
defaulted to a hardcoded `dashboard` and threw `RouteNotFoundException` in any
application that did not happen to define one.

## Inside components

Route middleware only guards the **initial page load**. A Livewire action
arrives over `livewire/update` and never passes through the route's middleware
stack, so components authorize again:

```php
use ArtflowStudio\AccountFlow\Concerns\AuthorizesAccountFlow;
use ArtflowStudio\AccountFlow\Enums\Ability;

class MyComponent extends Component
{
    use AuthorizesAccountFlow;

    public function mount(): void
    {
        $this->authorizeAccountFlow(Ability::ViewTransactions);
    }

    public function save(): void
    {
        $this->authorizeAccountFlow(Ability::ManageTransactions);
        // ...
    }
}
```

`canAccountFlow()` is the non-throwing form, for hiding UI a user cannot use.

All 41 bundled components authorize on mount and on every write. 0.2.x had no
authorization in any of them.

## Turning it off

```php
'authorization' => ['enabled' => false],
```

Only sensible when your application gates `/accounts` some other way. The routes
still require `auth`.
