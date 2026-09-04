<?php

namespace ArtflowStudio\AccountFlow\Support;

/**
 * Resolves a record id from a route parameter.
 *
 * 0.2.x base64-encoded ids into edit URLs and called `base64_decode()` on the
 * way back in. That is obfuscation, not authorization — anyone can decode it —
 * and it breaks route-model binding. Links now carry the plain id, but old
 * base64 URLs (bookmarks, emails) still resolve.
 *
 * `base64_decode()` is deliberately not used in strict mode alone: it happily
 * "decodes" a plain numeric id such as "5" into binary garbage, which is why
 * mixing the two formats needs an explicit check.
 */
final class RouteKey
{
    /**
     * @return int|null Null when the parameter cannot be read as an id.
     */
    public static function decode(int|string|null $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Already a plain id.
        if (is_int($value) || ctype_digit((string) $value)) {
            return (int) $value;
        }

        // Legacy base64 form: must decode strictly *and* yield digits.
        $decoded = base64_decode((string) $value, true);

        if ($decoded !== false && $decoded !== '' && ctype_digit($decoded)) {
            return (int) $decoded;
        }

        return null;
    }
}
