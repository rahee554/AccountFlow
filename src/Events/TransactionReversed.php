<?php

namespace ArtflowStudio\AccountFlow\Events;

use ArtflowStudio\AccountFlow\Models\Transaction;
use Illuminate\Foundation\Events\Dispatchable;

class TransactionReversed
{
    use Dispatchable;

    public function __construct(
        public readonly Transaction $original,
        public readonly Transaction $reversal,
        public readonly ?string $reason = null,
    ) {}
}
