<?php

use ArtflowStudio\AccountFlow\Facades\AC;
use ArtflowStudio\AccountFlow\Facades\Accountflow;
use ArtflowStudio\AccountFlow\Services\AccountFlowManager;
use ArtflowStudio\AccountFlow\Services\AccountService;
use ArtflowStudio\AccountFlow\Services\AuditService;
use ArtflowStudio\AccountFlow\Services\BudgetService;
use ArtflowStudio\AccountFlow\Services\CategoryService;
use ArtflowStudio\AccountFlow\Services\FeatureService;
use ArtflowStudio\AccountFlow\Services\PaymentMethodService;
use ArtflowStudio\AccountFlow\Services\ReportService;
use ArtflowStudio\AccountFlow\Services\SettingsService;
use ArtflowStudio\AccountFlow\Services\TransactionService;

/*
 * Replaces the 0.2.x accountflow:test-container / :test-all / :test-facade
 * console commands, which printed check marks instead of asserting anything.
 */

it('resolves every service from the container', function (string $service) {
    expect(app($service))->toBeInstanceOf($service);
})->with([
    TransactionService::class,
    AccountService::class,
    CategoryService::class,
    PaymentMethodService::class,
    BudgetService::class,
    ReportService::class,
    SettingsService::class,
    AuditService::class,
    FeatureService::class,
]);

it('binds services as singletons', function () {
    expect(app(TransactionService::class))->toBe(app(TransactionService::class));
});

it('exposes every service through the manager', function (string $method, string $class) {
    expect(Accountflow::{$method}())->toBeInstanceOf($class);
})->with([
    ['transactions', TransactionService::class],
    ['accounts', AccountService::class],
    ['categories', CategoryService::class],
    ['paymentMethods', PaymentMethodService::class],
    ['budgets', BudgetService::class],
    ['reports', ReportService::class],
    ['settings', SettingsService::class],
    ['audit', AuditService::class],
    ['features', FeatureService::class],
]);

it('reaches the same manager through both facades and the helper', function () {
    expect(app('accountflow'))->toBeInstanceOf(AccountFlowManager::class)
        ->and(accountflow())->toBe(app('accountflow'))
        ->and(AC::transactions())->toBeInstanceOf(TransactionService::class)
        ->and(Accountflow::transactions())->toBeInstanceOf(TransactionService::class);
});

it('exposes every service as instance methods, never static', function (string $service) {
    // 0.2.x mixed the two: services were declared static yet bound as
    // container singletons. Everything is an instance now, which is what makes
    // them mockable and swappable.
    $reflection = new ReflectionClass($service);

    $static = array_filter(
        $reflection->getMethods(ReflectionMethod::IS_PUBLIC),
        fn (ReflectionMethod $m): bool => $m->isStatic() && $m->getDeclaringClass()->getName() === $service,
    );

    expect(array_map(fn (ReflectionMethod $m): string => $m->getName(), $static))->toBe([]);
})->with([
    TransactionService::class,
    AccountService::class,
    CategoryService::class,
    PaymentMethodService::class,
    BudgetService::class,
    ReportService::class,
    SettingsService::class,
    AuditService::class,
    FeatureService::class,
]);
