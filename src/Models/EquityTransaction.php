<?php

namespace ArtflowStudio\AccountFlow\Models;

use ArtflowStudio\AccountFlow\Concerns\HasPackageFactory;
use ArtflowStudio\AccountFlow\Enums\EquityTransactionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A movement in a partner's equity.
 *
 * The 0.2.x version of this model did not match its own table. `$fillable`
 * listed `equity_account_id`, `processed_at` and `meta` — none of which exist
 * in `ac_equity_trx` — so mass assignment silently dropped every field. It also
 * cast `type` to a string-backed enum (deposit/withdrawal/…) while the column
 * is a tinyint holding 1-4, and declared no `partner` relation even though the
 * equity list eager-loads one.
 *
 * Columns, per the migration: partner_id, trx_id, type, amount, description.
 *
 * @property int $id
 * @property int|null $partner_id
 * @property int|null $trx_id
 * @property int $type
 * @property string $amount
 * @property string|null $description
 */
class EquityTransaction extends Model
{
    use HasPackageFactory;

    protected $table = 'ac_equity_trx';

    protected $fillable = [
        'partner_id',
        'trx_id',
        'type',
        'amount',
        'description',
    ];

    public function equityType(): ?EquityTransactionType
    {
        return EquityTransactionType::tryFrom((int) $this->type);
    }

    /**
     * @return BelongsTo<EquityPartner, $this>
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(EquityPartner::class, 'partner_id');
    }

    /**
     * The ledger entry this equity movement was booked through, if any.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'trx_id');
    }

    public function scopeOfType(Builder $query, EquityTransactionType|int $type): Builder
    {
        return $query->where('type', $type instanceof EquityTransactionType ? $type->value : $type);
    }

    /**
     * Amount signed by whether this increases or decreases equity.
     */
    public function signedAmount(): float
    {
        return (float) $this->amount * ($this->equityType()?->sign() ?? 1);
    }

    /**
     * `type` is cast to int, not to EquityTransactionType: the equity views
     * compare it loosely (`$trx->type == 1`), which an enum cast would break.
     * Call equityType() for the enum.
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'partner_id' => 'integer',
            'trx_id' => 'integer',
            'type' => 'integer',
            'amount' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
