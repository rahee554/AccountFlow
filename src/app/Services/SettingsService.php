<?php

namespace ArtflowStudio\AccountFlow\App\Services;

use App\Models\AccountFlow\Setting;
use Illuminate\Support\Facades\DB;

/**
 * SettingsService - Configuration Management
 *
 * Manages AccountFlow package settings and configuration defaults.
 * All settings are stored in the ac_settings table.
 *
 * @example
 * // Get a setting
 * $defaultType = SettingsService::get('default_transaction_type', 2);
 *
 * // Set a setting
 * SettingsService::set('default_transaction_type', 1);
 *
 * // Get default payment method
 * $methodId = SettingsService::defaultPaymentMethodId();
 */
class SettingsService
{
    /**
     * Get a setting value
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = Setting::where('key', $key)->first();

        if (!$setting) {
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
     * @param string $key
     * @param mixed $value
     * @param int $type 1=string/bool, 2=numeric
     *
     * @return Setting
     */
    public static function set(string $key, mixed $value, int $type = 1): Setting
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
     *
     * @return array
     */
    public static function getAll(): array
    {
        $settings = Setting::all();
        $result = [];

        foreach ($settings as $setting) {
            $result[$setting->key] = self::get($setting->key);
        }

        return $result;
    }

    /**
     * Get default transaction type
     *
     * @return int 1=income, 2=expense
     */
    public static function defaultTransactionType(): int
    {
        return (int) self::get('default_transaction_type', 2);
    }

    /**
     * Get default payment method ID
     *
     * @return int
     */
    public static function defaultPaymentMethodId(): int
    {
        return (int) self::get('default_payment_method_id', 1);
    }

    /**
     * Get default account ID
     *
     * @return int
     */
    public static function defaultAccountId(): int
    {
        return (int) self::get('default_account_id', 1);
    }

    /**
     * Get default sales category ID
     *
     * @return int
     */
    public static function defaultSalesCategoryId(): int
    {
        return (int) self::get('default_sales_category_id', 2);
    }

    /**
     * Get default expense category ID
     *
     * @return int
     */
    public static function defaultExpenseCategoryId(): int
    {
        return (int) self::get('default_expense_category_id', 5);
    }

    /**
     * Check if a feature is enabled
     *
     * @param string $feature
     *
     * @return bool
     */
    public static function isFeatureEnabled(string $feature): bool
    {
        $value = self::get($feature, 'disabled');

        return $value === 'enabled';
    }

    /**
     * Enable a feature
     *
     * @param string $feature
     *
     * @return void
     */
    public static function enableFeature(string $feature): void
    {
        self::set($feature, 'enabled');
    }

    /**
     * Disable a feature
     *
     * @param string $feature
     *
     * @return void
     */
    public static function disableFeature(string $feature): void
    {
        self::set($feature, 'disabled');
    }

    /**
     * Delete a setting
     *
     * @param string $key
     *
     * @return bool
     */
    public static function delete(string $key): bool
    {
        return DB::transaction(function () use ($key) {
            $setting = Setting::where('key', $key)->first();

            if (!$setting) {
                return false;
            }

            return $setting->delete();
        });
    }
}
