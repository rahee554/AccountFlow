<?php

namespace ArtflowStudio\AccountFlow\Listeners;

use ArtflowStudio\AccountFlow\Events\TransactionCreated;
use ArtflowStudio\AccountFlow\Events\TransactionDeleted;
use ArtflowStudio\AccountFlow\Events\TransactionReversed;
use ArtflowStudio\AccountFlow\Events\TransactionUpdated;
use ArtflowStudio\AccountFlow\Services\AuditService;
use Illuminate\Events\Dispatcher;

/**
 * Records ledger changes in the audit trail.
 *
 * Wiring this to events rather than sprinkling `AuditService::log()` calls
 * through the services means nothing can write a transaction without being
 * audited, and the audit trail cannot silently stop working the way it did in
 * 0.2.x — where AuditService was never called at all.
 */
class AuditSubscriber
{
    public function __construct(private readonly AuditService $audit) {}

    public function subscribe(Dispatcher $events): array
    {
        return [
            TransactionCreated::class => 'onCreated',
            TransactionUpdated::class => 'onUpdated',
            TransactionDeleted::class => 'onDeleted',
            TransactionReversed::class => 'onReversed',
        ];
    }

    public function onCreated(TransactionCreated $event): void
    {
        $this->audit->log(
            action: 'created',
            modelType: 'Transaction',
            modelId: (int) $event->transaction->id,
            after: $this->snapshot($event->transaction->getAttributes()),
        );
    }

    public function onUpdated(TransactionUpdated $event): void
    {
        $this->audit->log(
            action: 'updated',
            modelType: 'Transaction',
            modelId: (int) $event->transaction->id,
            before: $this->snapshot($event->before),
            after: $this->snapshot($event->transaction->getAttributes()),
        );
    }

    public function onDeleted(TransactionDeleted $event): void
    {
        $this->audit->log(
            action: 'deleted',
            modelType: 'Transaction',
            modelId: (int) $event->transaction->id,
            before: $this->snapshot($event->transaction->getAttributes()),
        );
    }

    public function onReversed(TransactionReversed $event): void
    {
        $this->audit->log(
            action: 'reversed',
            modelType: 'Transaction',
            modelId: (int) $event->original->id,
            before: $this->snapshot($event->original->getAttributes()),
            after: [
                'reversal_id' => $event->reversal->id,
                'reason' => $event->reason,
            ],
        );
    }

    /**
     * Keep the audit payload to the fields that matter, so the JSON columns
     * stay readable and do not balloon.
     *
     * @param array<string,mixed> $attributes
     *
     * @return array<string,mixed>
     */
    private function snapshot(array $attributes): array
    {
        return array_intersect_key($attributes, array_flip([
            'id',
            'unique_id',
            'amount',
            'type',
            'account_id',
            'category_id',
            'payment_method',
            'date',
            'description',
            'added_by',
            'reversal_of_id',
        ]));
    }
}
