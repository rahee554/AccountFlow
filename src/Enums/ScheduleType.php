<?php

namespace ArtflowStudio\AccountFlow\Enums;

use Carbon\CarbonInterface;

enum ScheduleType: string
{
    case Once = 'once';
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case HalfYearly = 'half_yearly';
    case Yearly = 'yearly';

    public function label(): string
    {
        return match ($this) {
            self::Once => 'One time',
            self::Daily => 'Daily',
            self::Weekly => 'Weekly',
            self::Monthly => 'Monthly',
            self::Quarterly => 'Quarterly',
            self::HalfYearly => 'Half yearly',
            self::Yearly => 'Yearly',
        };
    }

    public function isRecurring(): bool
    {
        return $this !== self::Once;
    }

    /**
     * The next due date after $from, or null for a one-off schedule.
     */
    public function next(CarbonInterface $from): ?CarbonInterface
    {
        return match ($this) {
            self::Once => null,
            self::Daily => $from->copy()->addDay(),
            self::Weekly => $from->copy()->addWeek(),
            self::Monthly => $from->copy()->addMonthNoOverflow(),
            self::Quarterly => $from->copy()->addMonthsNoOverflow(3),
            self::HalfYearly => $from->copy()->addMonthsNoOverflow(6),
            self::Yearly => $from->copy()->addYearNoOverflow(),
        };
    }
}
