<?php

namespace ArtflowStudio\AccountFlow\App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\AccountFlow\EquityTransaction
 *
 * @property int $id
 * @property int $equity_account_id
 * @property float|string $amount
 * @property TransactionType $type
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $processed_at
 * @property array|null $meta
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
enum TransactionType: string
{
    case Deposit = 'deposit';
    case Withdrawal = 'withdrawal';
    case Transfer = 'transfer';
    case Adjustment = 'adjustment';
}

class EquityTransaction extends Model
{
    /**
     * The table associated with the model.
     */
    protected string $table = 'ac_equity_trx';

    /**
     * Mass assignable attributes.
     *
     * @var array<int,string>
     */
    protected array $fillable = [
        'equity_account_id',
        'amount',
        'type',
        'description',
        'processed_at',
        'meta',
    ];

    /**
     * Create a new EquityTransaction instance.
     *
     * Use constructor property promotion to satisfy project PHP conventions
     * while forwarding attributes to the parent Eloquent constructor.
     *
     * @param  array<string,mixed>  $attributes
     */
    public function __construct(public array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * Attribute casting definitions.
     *
     * Use casts() method per Laravel 12 conventions.
     *
     * @return array<string,string|class-string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'processed_at' => 'datetime',
            'meta' => 'array',
            'type' => TransactionType::class,
        ];
    }

    /**
     * Define the relationship to the equity account.
     *
     * @return BelongsTo
     */

    /**
     * Scope a query to only include transactions of a given type.
     */
    public function scopeOfType(Builder $query, TransactionType $type): Builder
    {
        return $query->where('type', $type->value);
    }
}

