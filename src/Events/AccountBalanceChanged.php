<?php

namespace ArtflowStudio\AccountFlow\Events;

use ArtflowStudio\AccountFlow\Models\Account;
use Illuminate\Foundation\Events\Dispatchable;

class AccountBalanceChanged
{
    use Dispatchable;

    public function __construct(
        public readonly Account $account,
        public readonly float $from,
        public readonly float $to,
    ) {}

    public function delta(): float
    {
        return $this->to - $this->from;
    }
}
