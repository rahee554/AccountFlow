<?php

namespace ArtflowStudio\AccountFlow\Services;

use ArtflowStudio\AccountFlow\Enums\Feature;
use ArtflowStudio\AccountFlow\Enums\SettingType;
use ArtflowStudio\AccountFlow\Models\Setting;

/**
 * Module feature toggles.
 *
 * Keys and aliases come from the Feature enum — 0.2.x duplicated a 20-entry
 * alias map inside two methods of this class. Lookups read Setting::values(),
 * which is cached per request and in the application cache; 0.2.x ran a raw
 * `DB::table('ac_settings')` query on every single check, including inside
 * Blade directives that fire many times per page.
 */
class FeatureService
{
    public function isEnabled(Feature|string $feature): bool
    {
        return Setting::isEnabled($feature);
    }

    public function isDisabled(Feature|string $feature): bool
    {
        return ! $this->isEnabled($feature);
    }

    public function enable(Feature|string $feature): bool
    {
        return $this->set($feature, true);
    }

    public function disable(Feature|string $feature): bool
    {
        return $this->set($feature, false);
    }

    /**
     * Flip a feature and return its new state.
     */
    public function toggle(Feature|string $feature): bool
    {
        $enabled = ! $this->isEnabled($feature);

        $this->set($feature, $enabled);

        return $enabled;
    }

    public function set(Feature|string $feature, bool $enabled): bool
    {
        $case = Feature::tryParse($feature);

        if ($case === null) {
            return false;
        }

        Setting::put($case->value, $enabled ? 'enabled' : 'disabled', SettingType::Feature);

        return true;
    }

    /**
     * Canonical key => enabled, for every known module.
     *
     * @return array<string,bool>
     */
    public function all(): array
    {
        $state = [];

        foreach (Feature::cases() as $case) {
            $state[$case->value] = $this->isEnabled($case);
        }

        return $state;
    }

    /**
     * @return list<Feature>
     */
    public function enabled(): array
    {
        return array_values(array_filter(
            Feature::cases(),
            fn (Feature $case): bool => $this->isEnabled($case),
        ));
    }

    /**
     * Resolve a key or alias to its Feature case.
     */
    public function resolve(Feature|string $feature): ?Feature
    {
        return Feature::tryParse($feature);
    }

    /**
     * Write every module's default state. Used by the installer and seeder.
     */
    public function seedDefaults(bool $overwrite = false): int
    {
        $written = 0;

        foreach (Feature::defaults() as $key => $value) {
            $exists = Setting::query()->where('key', $key)->exists();

            if ($exists && ! $overwrite) {
                continue;
            }

            Setting::put($key, $value, SettingType::Feature);
            $written++;
        }

        return $written;
    }
}
