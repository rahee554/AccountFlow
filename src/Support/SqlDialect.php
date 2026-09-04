<?php

namespace ArtflowStudio\AccountFlow\Support;

use Illuminate\Support\Facades\DB;

/**
 * Small SQL fragments that differ between database drivers.
 *
 * AccountFlow used `DATE_FORMAT()` and double-quoted string literals
 * throughout, both of which are MySQL-only — `"income"` is an *identifier* in
 * PostgreSQL and in SQLite under ANSI_QUOTES. That is what kept the package
 * from being tested against SQLite at all.
 */
final class SqlDialect
{
    /**
     * An expression yielding 'YYYY-MM' for a date column.
     */
    public static function yearMonth(string $column = 'date', ?string $connection = null): string
    {
        return match (DB::connection($connection)->getDriverName()) {
            'sqlite' => "strftime('%Y-%m', {$column})",
            'pgsql' => "to_char({$column}, 'YYYY-MM')",
            'sqlsrv' => "FORMAT({$column}, 'yyyy-MM')",
            default => "DATE_FORMAT({$column}, '%Y-%m')",
        };
    }

    /**
     * An expression yielding 'YYYY-MM-DD' for a date column.
     */
    public static function isoDate(string $column = 'date', ?string $connection = null): string
    {
        return match (DB::connection($connection)->getDriverName()) {
            'sqlite' => "strftime('%Y-%m-%d', {$column})",
            'pgsql' => "to_char({$column}, 'YYYY-MM-DD')",
            'sqlsrv' => "FORMAT({$column}, 'yyyy-MM-dd')",
            default => "DATE_FORMAT({$column}, '%Y-%m-%d')",
        };
    }
}
