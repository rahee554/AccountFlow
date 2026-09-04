<?php

namespace ArtflowStudio\AccountFlow\Support;

use ArtflowStudio\AccountFlow\Enums\Ability;
use ArtflowStudio\AccountFlow\Exceptions\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/**
 * Resolves whether the current user may perform an AccountFlow ability.
 *
 * Resolution order:
 *
 *   1. Authorization disabled in config      -> allow
 *   2. Host application defined the gate     -> the gate decides
 *   3. Ability writes (manage-*)             -> the configured admin check
 *   4. Otherwise                             -> any authenticated user
 *
 * Step 4 is safe because AccountFlow's routes require authentication; it means
 * a host application that defines no gates still gets a working module rather
 * than a wall of 403s, while anything that writes stays admin-only.
 */
final class Authorization
{
    public static function allows(Ability $ability, ?Authenticatable $user = null): bool
    {
        if (! config('accountflow.authorization.enabled', true)) {
            return true;
        }

        $user ??= Auth::user();

        if ($user === null) {
            return false;
        }

        $gate = $ability->gate();

        if (Gate::has($gate)) {
            return Gate::forUser($user)->allows($gate);
        }

        if ($ability->isManagement()) {
            return self::isAdmin($user);
        }

        return true;
    }

    public static function denies(Ability $ability, ?Authenticatable $user = null): bool
    {
        return ! self::allows($ability, $user);
    }

    /**
     * @throws AuthorizationException
     */
    public static function authorize(Ability $ability, ?Authenticatable $user = null): void
    {
        if (self::denies($ability, $user)) {
            throw AuthorizationException::forAbility($ability);
        }
    }

    /**
     * Run the configured admin check against a user.
     *
     * Two independent ways to grant admin, checked in this order — either is
     * enough on its own:
     *
     *   1. `admin_management.roles`  — role name(s), checked via Spatie's
     *      hasRole()/hasAnyRole() when the user model has them. This is what
     *      lets a host application say "the 'business' role manages
     *      AccountFlow" purely through config, the way 0.2.x hardcoded
     *      `role:business` into its route file.
     *   2. `admin_management.check`  — a method name on the user model, an
     *      invokable class name, or a [class, method] pair.
     *
     * Closures are deliberately NOT supported for `check`: they cannot be
     * serialized, and a closure here made `php artisan config:cache` fail.
     *
     * If neither is configured and no fallback attribute matches, this
     * returns false — which is the safe failure. A silent "always admin"
     * default would be far worse than a 403 that tells you to configure it.
     */
    public static function isAdmin(?Authenticatable $user = null): bool
    {
        $config = config('accountflow.admin_management', []);

        if (! ($config['enabled'] ?? true)) {
            return true;
        }

        $user ??= Auth::user();

        if ($user === null) {
            return false;
        }

        if (self::hasConfiguredRole($user, $config)) {
            return true;
        }

        $check = $config['check'] ?? null;

        if ($check === null) {
            return false;
        }

        // [SomeClass::class, 'method'] or an invokable class name.
        if (is_array($check) || (is_string($check) && class_exists($check))) {
            $callable = is_array($check) ? [app($check[0]), $check[1]] : app($check);

            return is_callable($callable) && (bool) $callable($user);
        }

        // A method on the user model.
        if (is_string($check) && method_exists($user, $check)) {
            return (bool) $user->{$check}();
        }

        // Fall back to common attributes. Note: Eloquent attributes are NOT
        // real PHP properties, so property_exists() is always false for them —
        // which is why the 0.2.x version of this check never fired.
        foreach (['is_admin', 'admin'] as $attribute) {
            $value = data_get($user, $attribute);

            if ($value !== null) {
                return (bool) $value;
            }
        }

        if (data_get($user, 'role') === 'admin') {
            return true;
        }

        return false;
    }

    /**
     * Check `admin_management.roles` against the user, via Spatie's
     * hasRole() / hasAnyRole() if the user model has them.
     *
     * Uses method_exists() rather than an instanceof check against Spatie's
     * trait, so this works whether or not `spatie/laravel-permission` is
     * installed at all — it is only ever suggested, never required.
     *
     * @param array<string,mixed> $config
     */
    private static function hasConfiguredRole(Authenticatable $user, array $config): bool
    {
        $roles = $config['roles'] ?? null;

        if ($roles === null || $roles === []) {
            return false;
        }

        $roles = is_array($roles) ? $roles : [$roles];

        if (method_exists($user, 'hasAnyRole')) {
            return (bool) $user->hasAnyRole($roles);
        }

        if (method_exists($user, 'hasRole')) {
            foreach ($roles as $role) {
                if ($user->hasRole($role)) {
                    return true;
                }
            }
        }

        return false;
    }
}
