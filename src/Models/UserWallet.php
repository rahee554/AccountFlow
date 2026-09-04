<?php

namespace ArtflowStudio\AccountFlow\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A per-user balance.
 *
 * @property int $id
 * @property string $balance
 * @property int $user_id
 * @property int|null $status
 */
class UserWallet extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 1;

    public const STATUS_FROZEN = 2;

    protected $table = 'ac_user_wallets';

    /**
     * Neither $fillable nor $guarded was declared, so Eloquent's default
     * `$guarded = ['*']` blocked every create() call on this model.
     */
    protected $fillable = [
        'balance',
        'user_id',
        'status',
    ];

    /**
     * Resolved from the application's auth config rather than a hardcoded
     * `\App\Models\User`, which tied the package to one application layout.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(
            config('auth.providers.users.model', 'App\Models\User'),
            'user_id',
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function isFrozen(): bool
    {
        return (int) $this->status === self::STATUS_FROZEN;
    }

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'user_id' => 'integer',
            'status' => 'integer',
            'balance' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
