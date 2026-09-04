<?php

use ArtflowStudio\AccountFlow\Enums\Ability;
use ArtflowStudio\AccountFlow\Enums\Feature;
use ArtflowStudio\AccountFlow\Enums\TransactionType;

it('parses every legacy transaction type representation', function (mixed $input, ?TransactionType $expected) {
    expect(TransactionType::tryParse($input))->toBe($expected);
})->with([
    'int income' => [1, TransactionType::Income],
    'int expense' => [2, TransactionType::Expense],
    'string income' => ['income', TransactionType::Income],
    'string expense' => ['expense', TransactionType::Expense],
    'numeric string' => ['1', TransactionType::Income],
    'mixed case' => ['Income', TransactionType::Income],
    'garbage' => ['nonsense', null],
    'null' => [null, null],
]);

it('signs balances by direction', function () {
    expect(TransactionType::Income->sign())->toBe(1)
        ->and(TransactionType::Expense->sign())->toBe(-1)
        ->and(TransactionType::Income->opposite())->toBe(TransactionType::Expense);
});

it('resolves feature aliases to canonical keys', function (string $alias, Feature $expected) {
    expect(Feature::tryParse($alias))->toBe($expected);
})->with([
    ['budgets', Feature::Budgets],
    ['budgets_module', Feature::Budgets],
    ['audit', Feature::AuditTrail],
    ['audit_trail', Feature::AuditTrail],
    ['templates', Feature::TransactionTemplates],
    ['transfers', Feature::Transfers],
    ['profit_loss', Feature::ProfitLossReport],
]);

it('enables every feature by default so a fresh install works', function () {
    $defaults = Feature::defaults();

    expect($defaults)->toHaveCount(count(Feature::cases()))
        ->and(array_unique(array_values($defaults)))->toBe(['enabled']);
});

it('classifies write abilities as management', function () {
    expect(Ability::ManageTransactions->isManagement())->toBeTrue()
        ->and(Ability::ViewTransactions->isManagement())->toBeFalse();
});
