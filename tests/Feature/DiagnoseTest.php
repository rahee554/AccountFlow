<?php

/*
 * accountflow:diagnose is what should catch the admin-lockout bug before a
 * real user does — a fresh install with the default config has no working
 * admin check (no isAdmin() method exists on Laravel's default User model),
 * so it must say so.
 */

it('flags that nobody can be an admin when neither roles nor check resolves', function () {
    config()->set('accountflow.admin_management', [
        'enabled' => true,
        'roles' => null,
        'check' => 'isAdmin', // does not exist on the stock Testbench User model
    ]);

    $this->artisan('accountflow:diagnose')
        ->expectsOutputToContain('Nobody can be recognised as an AccountFlow admin')
        ->assertSuccessful();
});

it('still flags the problem when roles is set but the user model has no hasRole()', function () {
    config()->set('accountflow.admin_management', [
        'enabled' => true,
        'roles' => 'business',
        'check' => 'isAdmin',
    ]);

    // The stock Testbench user has no hasRole() method, so setting 'roles'
    // alone is not enough — the check inspects whether the method actually
    // exists rather than assuming a role config implies Spatie is installed.
    $this->artisan('accountflow:diagnose')
        ->expectsOutputToContain('Nobody can be recognised as an AccountFlow admin')
        ->assertSuccessful();
});

it('passes once check points at a method that really exists', function () {
    config()->set('accountflow.admin_management', [
        'enabled' => true,
        'roles' => null,
        'check' => 'getAuthIdentifierName', // a real method every Authenticatable has
    ]);

    $output = $this->artisan('accountflow:diagnose');
    $output->assertSuccessful();
});

it('does not flag anything when admin management is disabled', function () {
    config()->set('accountflow.admin_management', ['enabled' => false]);

    $this->artisan('accountflow:diagnose')
        ->doesntExpectOutputToContain('Nobody can be recognised')
        ->assertSuccessful();
});

it('runs clean on a fresh, empty install', function () {
    config()->set('accountflow.admin_management', ['enabled' => true, 'check' => 'getAuthIdentifierName']);

    $this->artisan('accountflow:diagnose')
        ->expectsOutputToContain('No problems found.')
        ->assertSuccessful();
});
