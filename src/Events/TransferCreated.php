<?php

namespace ArtflowStudio\AccountFlow\Events;

use ArtflowStudio\AccountFlow\Models\Transfer;
use Illuminate\Foundation\Events\Dispatchable;

class TransferCreated
{
    use Dispatchable;

    public function __construct(public readonly Transfer $transfer) {}
}
