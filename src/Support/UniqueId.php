<?php

namespace ArtflowStudio\AccountFlow\Support;

use Illuminate\Database\Eloquent\Model;

/**
 * Generates the short public reference stored in `unique_id` columns.
 *
 * AccountFlow used to call the global `generateUniqueID()` helper directly.
 * That helper ships with `artflow-studio/snippets`, but snippets registers it
 * from its *service provider* rather than through Composer's `files`
 * autoloading — so the function only exists once that provider has booted.
 * Anything running before or without it (a queue worker with a trimmed
 * provider list, a package test harness) hit
 * "Call to undefined function generateUniqueID()".
 *
 * The helper is still preferred when present, so ids keep the same shape as
 * rows written by earlier versions; otherwise an equivalent is generated here.
 */
final class UniqueId
{
    private const ALPHABET = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    private const LENGTH = 6;

    private const MAX_ATTEMPTS = 10;

    /**
     * A reference that is not yet used in $column of $model.
     *
     * @param class-string<Model> $model
     */
    public static function for(string $model, string $column = 'unique_id'): string
    {
        if (function_exists('generateUniqueID')) {
            return generateUniqueID($model, $column);
        }

        for ($attempt = 0; $attempt < self::MAX_ATTEMPTS; $attempt++) {
            $candidate = self::random();

            if (! $model::query()->where($column, $candidate)->exists()) {
                return $candidate;
            }
        }

        // Fall back to something long enough that a collision is not a concern.
        return strtoupper(bin2hex(random_bytes(8)));
    }

    private static function random(): string
    {
        $max = strlen(self::ALPHABET) - 1;
        $id = '';

        for ($i = 0; $i < self::LENGTH; $i++) {
            $id .= self::ALPHABET[random_int(0, $max)];
        }

        return $id;
    }
}
