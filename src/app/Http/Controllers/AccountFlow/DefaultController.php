<?php

namespace App\Http\Controllers\AccountFlow;

use App\Http\Controllers\Controller;
use App\Models\AccountFlow\Setting;

class DefaultController extends Controller
{
    public static function defaultSalesCategoryId()
    {
        $value = Setting::where('key', 'default_sales_category_id')->value('value');

        return $value !== null ? (int) $value : 2;
    }

    public static function defaultAccountId()
    {
        $value = Setting::where('key', 'default_account_id')->value('value');

        return $value !== null ? (int) $value : 1;
    }

    public static function defaultExpenseCategoryId()
    {
        $value = Setting::where('key', 'default_expense_category_id')->value('value');

        return $value !== null ? (int) $value : 5;
    }

    public static function routePrefix()
    {
        $value = Setting::where('key', 'route_prefix')->value('value');

        return $value !== null ? $value : 'accounts';
    }
}

