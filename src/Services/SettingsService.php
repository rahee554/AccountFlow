<?php

namespace ArtflowStudio\AccountFlow\Services;

use ArtflowStudio\AccountFlow\Models\Setting;
use Illuminate\Support\Facades\DB;

/**
 * SettingsService - Configuration Management
 *
 * Manages AccountFlow package settings and configuration defaults.
 * All settings are stored in the ac_settings table.
 *
 * @example
 * // Get a setting
 * $defaultType = Accountflow::settings()->get('default_transaction_type', 2);
 *
 * // Set a setting
 * Accountflow::settings()->set('default_transaction_type', 1);
 *
 * // Get default payment method
 * $methodId = Accountflow::settings()->defaultPaymentMethodId();
 */
class SettingsService
{
    /**
     * Get a setting value
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $setting = Setting::where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        // Convert value based on type
        if ($setting->type === 1) {
            // Boolean/string type
            return match ($setting->value) {
                'true' => true,
                'false' => false,
                'enabled' => 'enabled',
                'disabled' => 'disabled',
                default => $setting->value,
            };
        }

        // Numeric type
        return is_numeric($setting->value) ? (int) $setting->value : $setting->value;
    }

    /**
     * Set a setting value
     *
     * @param int $type 1=string/bool, 2=numeric
     */
    public function set(string $key, mixed $value, int $type = 1): Setting
    {
        return DB::transaction(function () use ($key, $value, $type) {
            $setting = Setting::firstOrCreate(['key' => $key]);

            $setting->update([
                'value' => (string) $value,
                'type' => $type,
            ]);

            return $setting->fresh();
        });
    }

    /**
     * Get all settings
     */
    public function getAll(): array
    {
        $settings = Setting::all();
        $result = [];

        foreach ($settings as $setting) {
            $result[$setting->key] = $this->get($setting->key);
        }

        return $result;
    }

    /**
     * Get default transaction type
     *
     * @return int 1=income, 2=expense
     */
    public function defaultTransactionType(): int
    {
        return (int) $this->get('default_transaction_type', 2);
    }

    /**
     * Get default payment method ID
     */
    public function defaultPaymentMethodId(): int
    {
        return (int) $this->get('default_payment_method_id', 1);
    }

    /**
     * Get default account ID
     */
    public function defaultAccountId(): int
    {
        return (int) $this->get('default_account_id', 1);
    }

    /**
     * Get default sales category ID
     */
    public function defaultSalesCategoryId(): int
    {
        return (int) $this->get('default_sales_category_id', 2);
    }

    /**
     * Get default expense category ID
     */
    public function defaultExpenseCategoryId(): int
    {
        return (int) $this->get('default_expense_category_id', 5);
    }

    /**
     * Check if a feature is enabled
     */
    public function isFeatureEnabled(string $feature): bool
    {
        $value = $this->get($feature, 'disabled');

        return $value === 'enabled';
    }

    /**
     * Enable a feature
     */
    public function enableFeature(string $feature): void
    {
        $this->set($feature, 'enabled');
    }

    /**
     * Disable a feature
     */
    public function disableFeature(string $feature): void
    {
        $this->set($feature, 'disabled');
    }

    /**
     * Delete a setting
     */
    public function delete(string $key): bool
    {
        return DB::transaction(function () use ($key) {
            $setting = Setting::where('key', $key)->first();

            if (! $setting) {
                return false;
            }

            return $setting->delete();
        });
    }
}
