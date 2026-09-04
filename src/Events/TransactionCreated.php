<?php

namespace ArtflowStudio\AccountFlow\Events;

use ArtflowStudio\AccountFlow\Models\Transaction;
use Illuminate\Foundation\Events\Dispatchable;

class TransactionCreated
{
    use Dispatchable;

    public function __construct(public readonly Transaction $transaction) {}
}
