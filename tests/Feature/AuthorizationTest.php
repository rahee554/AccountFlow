<?php

use ArtflowStudio\AccountFlow\Enums\Ability;
use ArtflowStudio\AccountFlow\Support\Authorization;
use Illuminate\Foundation\Auth\User as Authenticatable;

/*
 * The scenario that motivated this file: a host application using
 * spatie/laravel-permission has no `isAdmin()` method on its user model —
 * roles are checked with `hasRole()`, a method call, not an attribute.
 * `admin_management.check` defaulting to 'isAdmin' silently resolved to
 * false for every user, including real admins, because none of the
 * attribute fallbacks (`is_admin`, `admin`, `role`) understand a method call.
 * Every "manage-*" ability was therefore denied to everyone.
 */

class FakeRoleUser extends Authenticatable
{
    /** @var list<string> */
    public array $roles = [];

    protected $guarded = [];

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles, true);
    }

    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }
}

class FakeMethodUser extends Authenticatable
{
    public bool $admin = false;

    protected $guarded = [];

    public function isAdmin(): bool
    {
        return $this->admin;
    }
}

beforeEach(function () {
    config()->set('accountflow.authorization.enabled', true);
});

it('grants admin through a configured role, with no isAdmin() method anywhere', function () {
    config()->set('accountflow.admin_management', ['enabled' => true, 'roles' => 'business']);

    $owner = tap(new FakeRoleUser, fn ($u) => $u->roles = ['business']);
    $stranger = tap(new FakeRoleUser, fn ($u) => $u->roles = ['customer']);

    expect(Authorization::isAdmin($owner))->toBeTrue()
        ->and(Authorization::isAdmin($stranger))->toBeFalse();
});

it('accepts a list of roles, matching any one of them', function () {
    config()->set('accountflow.admin_management', ['enabled' => true, 'roles' => ['admin', 'business']]);

    $business = tap(new FakeRoleUser, fn ($u) => $u->roles = ['business']);

    expect(Authorization::isAdmin($business))->toBeTrue();
});

it('still supports a method-name check when no role is configured', function () {
    config()->set('accountflow.admin_management', ['enabled' => true, 'check' => 'isAdmin']);

    $admin = tap(new FakeMethodUser, fn ($u) => $u->admin = true);
    $regular = tap(new FakeMethodUser, fn ($u) => $u->admin = false);

    expect(Authorization::isAdmin($admin))->toBeTrue()
        ->and(Authorization::isAdmin($regular))->toBeFalse();
});

it('lets a role grant admin even when the configured check method does not exist', function () {
    // The exact failure this fixes: 'check' => 'isAdmin' pointed at a method
    // that was never defined, and every fallback silently returned false.
    config()->set('accountflow.admin_management', [
        'enabled' => true,
        'roles' => 'business',
        'check' => 'isAdmin',
    ]);

    $owner = tap(new FakeRoleUser, fn ($u) => $u->roles = ['business']);

    expect(method_exists($owner, 'isAdmin'))->toBeFalse()
        ->and(Authorization::isAdmin($owner))->toBeTrue();
});

it('fails safe — no admin — when neither roles nor a working check are configured', function () {
    config()->set('accountflow.admin_management', ['enabled' => true, 'roles' => null, 'check' => 'isAdmin']);

    $user = new FakeRoleUser;

    expect(Authorization::isAdmin($user))->toBeFalse();
});

it('denies every manage-* ability for a user with no matching role', function () {
    config()->set('accountflow.admin_management', ['enabled' => true, 'roles' => 'business']);

    $stranger = tap(new FakeRoleUser, fn ($u) => $u->roles = ['customer']);

    expect(Authorization::allows(Ability::ManageTransactions, $stranger))->toBeFalse()
        ->and(Authorization::allows(Ability::ManageSettings, $stranger))->toBeFalse();
});

it('grants every manage-* ability once the role matches', function () {
    config()->set('accountflow.admin_management', ['enabled' => true, 'roles' => 'business']);

    $owner = tap(new FakeRoleUser, fn ($u) => $u->roles = ['business']);

    expect(Authorization::allows(Ability::ManageTransactions, $owner))->toBeTrue()
        ->and(Authorization::allows(Ability::ManageSettings, $owner))->toBeTrue();
});

it('still lets any authenticated user through a view-* ability regardless of role', function () {
    config()->set('accountflow.admin_management', ['enabled' => true, 'roles' => 'business']);

    $stranger = tap(new FakeRoleUser, fn ($u) => $u->roles = ['customer']);

    expect(Authorization::allows(Ability::ViewTransactions, $stranger))->toBeTrue();
});

it('lets a defined gate override the role check entirely', function () {
    config()->set('accountflow.admin_management', ['enabled' => true, 'roles' => 'business']);

    Illuminate\Support\Facades\Gate::define(
        Ability::ManageTransactions->gate(),
        fn (): bool => true,
    );

    $stranger = tap(new FakeRoleUser, fn ($u) => $u->roles = ['customer']);

    expect(Authorization::allows(Ability::ManageTransactions, $stranger))->toBeTrue();
});
