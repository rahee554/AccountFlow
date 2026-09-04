<?php

namespace ArtflowStudio\AccountFlow\Events;

use ArtflowStudio\AccountFlow\Models\Transaction;
use Illuminate\Foundation\Events\Dispatchable;

class TransactionUpdated
{
    use Dispatchable;

    /**
     * @param array<string,mixed> $before Attributes as they were before the update.
     */
    public function __construct(
        public readonly Transaction $transaction,
        public readonly array $before = [],
    ) {}
}
