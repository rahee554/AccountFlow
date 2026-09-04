<?php

use ArtflowStudio\AccountFlow\Enums\Feature;
use ArtflowStudio\AccountFlow\Enums\SettingType;
use ArtflowStudio\AccountFlow\Models\Setting;
use ArtflowStudio\AccountFlow\Services\FeatureService;

it('has every module enabled on a fresh install with no seeder', function (Feature $feature) {
    // 0.2.x left these off because Setting::defaults() omitted the keys the
    // feature checks looked up, so their routes returned 403 until the
    // (destructive) seeder had been run.
    expect(app(FeatureService::class)->isEnabled($feature))->toBeTrue();
})->with(Feature::cases());

it('resolves short aliases used by route middleware', function () {
    $features = app(FeatureService::class);

    expect($features->isEnabled('budgets'))->toBeTrue()
        ->and($features->isEnabled('audit'))->toBeTrue()
        ->and($features->isEnabled('templates'))->toBeTrue();
});

it('toggles a feature and invalidates the cache', function () {
    $features = app(FeatureService::class);

    expect($features->isEnabled(Feature::Budgets))->toBeTrue();

    $features->disable(Feature::Budgets);
    expect($features->isEnabled(Feature::Budgets))->toBeFalse();

    $features->enable(Feature::Budgets);
    expect($features->isEnabled(Feature::Budgets))->toBeTrue();
});

it('stores a setting keyed only on key, whatever its type', function () {
    // `ac_settings.key` is UNIQUE. Matching on ['key','type'] missed a row
    // stored under a different type, fell through to INSERT, and violated the
    // unique index — a 500 on the settings screen.
    Setting::put('currency', 'PKR', SettingType::Feature);
    Setting::put('currency', 'USD', SettingType::Value);

    expect(Setting::where('key', 'currency')->count())->toBe(1)
        ->and(Setting::currency())->toBe('USD');
});

it('survives repeated writes of the same key', function () {
    foreach (range(1, 5) as $i) {
        Setting::put('default_account_id', $i, SettingType::Value);
    }

    expect(Setting::where('key', 'default_account_id')->count())->toBe(1)
        ->and(Setting::defaultAccountId())->toBe(5);
});
