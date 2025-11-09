<?php

namespace App\Models\AccountFlow;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @psalm-type SettingsArray = array{
 *   multi_accounts_module: string,
 *   custom_category: string,
 *   ledger_module: string,
 *   cashbook_module: string,
 *   trial_balance_module: string,
 *   assets_module: string,
 *   purchase_module: string,
 *   multi_payment_methods: string,
 *   loan_module: string,
 *   user_wallet_module: string,
 *   income_form: string,
 *   default_transaction_type: int,
 *   default_account_id: int,
 *   default_payment_method_id: int,
 *   default_sales_category_id: int,
 *   default_expense_category_id: int,
 *   route_prefix: string
 * }
 */
class Setting extends Model
{
    use HasFactory;

    protected $table = 'ac_settings';

    protected $fillable = [
        'name',
        'key',
        'value',
        'type', // 1 = Feature, 2 = Value, 3 = Other
    ];

    /**
     * Cached settings loaded from database.
     *
     * @var array<string,mixed>
     */
    protected static array $cached = [];

    /**
     * Application defaults for account flow settings.
     *
     * @return SettingsArray
     */
    public static function defaults(): array
    {
        return [
            // modules
            'multi_accounts_module' => 'enabled',
            'custom_category' => 'enabled',
            'ledger_module' => 'enabled',
            'cashbook_module' => 'enabled',
            'trial_balance_module' => 'enabled',
            'assets_module' => 'enabled',
            'purchase_module' => 'enabled',
            'multi_payment_methods' => 'enabled',
            'loan_module' => 'enabled',
            'user_wallet_module' => 'enabled',
            'income_form' => 'enabled',
            'default_transaction_type' => 2,
            'default_account_id' => 1,
            'default_payment_method_id' => 1,
            'default_sales_category_id' => 2,
            'default_expense_category_id' => 5,
            'route_prefix' => 'accounts',
        ];
    }

    /**
     * Load settings from DB and merge with defaults.
     *
     * @return array<string,mixed>
     */
    protected static function loadAll(): array
    {
        if (count(self::$cached) > 0) {
            return self::$cached;
        }

        $defaults = self::defaults();
        $keys = array_keys($defaults);

        $rows = self::query()
            ->whereIn('key', $keys)
            ->pluck('value', 'key')
            ->toArray();

        $merged = $defaults;

        foreach ($rows as $key => $value) {
            if (array_key_exists($key, $defaults)) {
                // Cast numeric defaults to int, otherwise keep string
                if (is_int($defaults[$key])) {
                    $merged[$key] = (int) $value;
                } else {
                    $merged[$key] = (string) $value;
                }
            }
        }

        self::$cached = $merged;

        return self::$cached;
    }

    /**
     * Get a single setting value (merged with defaults).
     */
    public static function getValue(string $key): mixed
    {
        $all = self::loadAll();

        return $all[$key] ?? null;
    }

    // Boolean/feature checks
    public static function isMultiAccountsModuleEnabled(): bool
    {
        return (string) self::getValue('multi_accounts_module') === 'enabled';
    }

    public static function isCustomCategoryEnabled(): bool
    {
        return (string) self::getValue('custom_category') === 'enabled';
    }

    public static function isLedgerModuleEnabled(): bool
    {
        return (string) self::getValue('ledger_module') === 'enabled';
    }

    public static function isCashbookModuleEnabled(): bool
    {
        return (string) self::getValue('cashbook_module') === 'enabled';
    }

    public static function isTrialBalanceModuleEnabled(): bool
    {
        return (string) self::getValue('trial_balance_module') === 'enabled';
    }

    public static function isAssetsModuleEnabled(): bool
    {
        return (string) self::getValue('assets_module') === 'enabled';
    }

    public static function isPurchaseModuleEnabled(): bool
    {
        return (string) self::getValue('purchase_module') === 'enabled';
    }

    public static function isMultiPaymentMethodsEnabled(): bool
    {
        return (string) self::getValue('multi_payment_methods') === 'enabled';
    }

    public static function isLoanModuleEnabled(): bool
    {
        return (string) self::getValue('loan_module') === 'enabled';
    }

    public static function isUserWalletModuleEnabled(): bool
    {
        return (string) self::getValue('user_wallet_module') === 'enabled';
    }

    public static function isIncomeFormEnabled(): bool
    {
        return (string) self::getValue('income_form') === 'enabled';
    }

    // Typed getters for numeric/string defaults
    public static function defaultTransactionType(): int
    {
        return (int) self::getValue('default_transaction_type');
    }

    public static function defaultAccountId(): int
    {
        return (int) self::getValue('default_account_id');
    }

    public static function defaultPaymentMethodId(): int
    {
        return (int) self::getValue('default_payment_method_id');
    }

    public static function defaultSalesCategoryId(): int
    {
        return (int) self::getValue('default_sales_category_id');
    }

    public static function defaultExpenseCategoryId(): int
    {
        return (int) self::getValue('default_expense_category_id');
    }

    public static function routePrefix(): string
    {
        return (string) self::getValue('route_prefix');
    }

    /**
     * Clear internal cache so fresh DB values will be loaded on next request.
     */
    public static function clearCache(): void
    {
        self::$cached = [];
    }
}
