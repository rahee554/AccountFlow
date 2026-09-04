<?php

namespace ArtflowStudio\AccountFlow\Models;

use ArtflowStudio\AccountFlow\Concerns\HasPackageFactory;
use ArtflowStudio\AccountFlow\Enums\Feature;
use ArtflowStudio\AccountFlow\Enums\SettingType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * A single AccountFlow setting.
 *
 * `key` is UNIQUE and is the only identity — `type` is metadata. Never match on
 * both in an updateOrCreate: a row seeded as type 1 and saved as type 2 misses
 * the lookup, falls through to INSERT, and violates the unique index.
 *
 * Reads go through a two-level cache: a per-request static array, backed by the
 * application cache. 0.2.x queried the table on every feature check.
 *
 * @property int $id
 * @property string|null $name
 * @property string $key
 * @property string $value
 * @property int $type
 */
class Setting extends Model
{
    use HasPackageFactory;

    public const CACHE_KEY = 'accountflow.settings';

    protected $table = 'ac_settings';

    protected $fillable = [
        'name',
        'key',
        'value',
        'type',
    ];

    /**
     * Per-request cache; null means "not loaded yet".
     *
     * @var array<string,mixed>|null
     */
    protected static ?array $cached = null;

    /**
     * Defaults for a fresh install.
     *
     * Every module is enabled, so AccountFlow is fully usable before any seeder
     * runs. In 0.2.x this list omitted most of the keys the feature checks
     * looked up, which left budgets, equity, transfers, templates and the audit
     * trail switched off and their routes returning 403.
     *
     * @return array<string,mixed>
     */
    public static function defaults(): array
    {
        return Feature::defaults() + [
            'default_transaction_type' => 2,
            'default_account_id' => 1,
            'default_payment_method_id' => 1,
            'default_sales_category_id' => 2,
            'default_expense_category_id' => 5,
            'route_prefix' => 'accounts',
            'currency' => 'PKR',
        ];
    }

    /**
     * All settings, database values layered over the defaults.
     *
     * @return array<string,mixed>
     */
    public static function values(): array
    {
        if (self::$cached !== null) {
            return self::$cached;
        }

        $defaults = self::defaults();

        try {
            $rows = Cache::remember(
                self::CACHE_KEY,
                now()->addHour(),
                fn (): array => self::query()->pluck('value', 'key')->all(),
            );
        } catch (Throwable) {
            // Table missing (pre-migration) or cache unavailable — fall back to
            // defaults so the application still boots.
            return self::$cached = $defaults;
        }

        $merged = $defaults;

        foreach ($rows as $key => $value) {
            $merged[$key] = array_key_exists($key, $defaults) && is_int($defaults[$key])
                ? (int) $value
                : $value;
        }

        return self::$cached = $merged;
    }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        return self::values()[$key] ?? $default;
    }

    /**
     * Write a setting and invalidate both cache levels.
     */
    public static function put(string $key, mixed $value, SettingType $type = SettingType::Value): self
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value, 'type' => $type->value],
        );

        self::flushCache();

        return $setting;
    }

    public static function isEnabled(Feature|string $feature): bool
    {
        $case = Feature::tryParse($feature);
        $key = $case?->value ?? (is_string($feature) ? $feature : $feature->value);

        return (string) self::getValue($key, 'disabled') === 'enabled';
    }

    public static function defaultTransactionType(): int
    {
        return (int) self::getValue('default_transaction_type', 2);
    }

    public static function defaultAccountId(): int
    {
        return (int) self::getValue('default_account_id', 1);
    }

    public static function defaultPaymentMethodId(): int
    {
        return (int) self::getValue('default_payment_method_id', 1);
    }

    public static function defaultSalesCategoryId(): int
    {
        return (int) self::getValue('default_sales_category_id', 2);
    }

    public static function defaultExpenseCategoryId(): int
    {
        return (int) self::getValue('default_expense_category_id', 5);
    }

    public static function routePrefix(): string
    {
        return (string) self::getValue('route_prefix', 'accounts');
    }

    public static function currency(): string
    {
        return (string) self::getValue('currency', config('accountflow.currency', 'PKR'));
    }

    /**
     * Drop the request-level and application-level caches.
     */
    public static function flushCache(): void
    {
        self::$cached = null;

        try {
            Cache::forget(self::CACHE_KEY);
        } catch (Throwable) {
            // A cache store that is unavailable must not break a settings write.
        }
    }

    /**
     * @deprecated Use flushCache().
     */
    public static function clearCache(): void
    {
        self::flushCache();
    }

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'type' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saved(static fn () => self::flushCache());
        static::deleted(static fn () => self::flushCache());
    }
}
