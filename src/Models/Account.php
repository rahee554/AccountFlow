<?php

namespace ArtflowStudio\AccountFlow\Models;

use ArtflowStudio\AccountFlow\Concerns\HasPackageFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A cash, bank or wallet account.
 *
 * Table name is `accounts` (unprefixed) for historical reasons; it is not
 * renamed because live installs have foreign keys pointing at it.
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property bool $active
 * @property string $opening_balance
 * @property string $balance
 */
class Account extends Model
{
    use HasPackageFactory;

    protected $table = 'accounts';

    /**
     * `opening_balance` and `active` were missing here, while `status` was
     * listed but has never been a column — so AccountService::create() silently
     * discarded the opening balance of every account it made.
     */
    protected $fillable = [
        'name',
        'description',
        'active',
        'opening_balance',
        'balance',
    ];

    /**
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * @return HasMany<PaymentMethod, $this>
     */
    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * Balance recomputed from the transaction ledger, in SQL.
     */
    public function calculatedBalance(): float
    {
        $totals = $this->transactions()
            ->selectRaw('COALESCE(SUM(CASE WHEN type = 1 THEN amount ELSE 0 END), 0) as income')
            ->selectRaw('COALESCE(SUM(CASE WHEN type = 2 THEN amount ELSE 0 END), 0) as expense')
            ->first();

        return (float) $this->opening_balance
            + (float) ($totals->income ?? 0)
            - (float) ($totals->expense ?? 0);
    }

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'active' => 'boolean',
            'opening_balance' => 'decimal:2',
            'balance' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
