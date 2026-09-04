<?php

namespace ArtflowStudio\AccountFlow\Events;

use ArtflowStudio\AccountFlow\Models\Transfer;
use Illuminate\Foundation\Events\Dispatchable;

class TransferDeleted
{
    use Dispatchable;

    public function __construct(
        public readonly Transfer $transfer,
        public readonly float $amount,
    ) {}
}
