<?php

namespace ArtflowStudio\AccountFlow\Enums;

enum EquityTransactionType: int
{
    case Contribution = 1;
    case Withdrawal = 2;
    case ProfitShare = 3;
    case LossShare = 4;

    public function label(): string
    {
        return match ($this) {
            self::Contribution => 'Contribution',
            self::Withdrawal => 'Withdrawal',
            self::ProfitShare => 'Profit share',
            self::LossShare => 'Loss share',
        };
    }

    /**
     * Does this increase (+1) or decrease (-1) the partner's equity?
     */
    public function sign(): int
    {
        return match ($this) {
            self::Contribution, self::ProfitShare => 1,
            self::Withdrawal, self::LossShare => -1,
        };
    }
}
