<?php

namespace ArtflowStudio\AccountFlow\Enums;

/**
 * What kind of thing a row in `ac_settings` holds.
 *
 * This is metadata only. `ac_settings.key` is UNIQUE, so the key alone
 * identifies a row — never include the type in an updateOrCreate match.
 */
enum SettingType: int
{
    case Feature = 1;
    case Value = 2;
    case Permission = 3;

    public function label(): string
    {
        return match ($this) {
            self::Feature => 'Feature toggle',
            self::Value => 'Value',
            self::Permission => 'Permission',
        };
    }
}
