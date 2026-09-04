<?php

namespace ArtflowStudio\AccountFlow\Models;

use ArtflowStudio\AccountFlow\Concerns\HasPackageFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string|null $name
 * @property string|null $info
 * @property string|null $logo_icon
 * @property int|null $account_id
 * @property int $status
 */
class PaymentMethod extends Model
{
    use HasPackageFactory;

    protected $table = 'ac_payment_methods';

    protected $fillable = [
        'name',
        'info',
        'logo_icon',
        'account_id',
        'status',
    ];

    /**
     * The column on ac_transactions is `payment_method`, not `payment_method_id`.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'payment_method');
    }

    /**
     * @return BelongsTo<Account, $this>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }

    public function isActive(): bool
    {
        return (int) $this->status === 1;
    }

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'account_id' => 'integer',
            'status' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
