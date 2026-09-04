<?php

namespace ArtflowStudio\AccountFlow\Models;

use ArtflowStudio\AccountFlow\Concerns\HasPackageFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Money handed from one person's wallet to another's.
 *
 * `ac_user_transfers` had no model at all, so nothing could read or write it —
 * one of the reasons the wallet module never worked.
 *
 * The columns are called `from` and `to`, which are SQL reserved-ish words and
 * awkward as property names; `fromUser` / `toUser` relations are provided.
 *
 * @property int $id
 * @property string $amount
 * @property int $from
 * @property int $to
 * @property \Illuminate\Support\Carbon $date
 */
class UserTransfer extends Model
{
    use HasPackageFactory;

    protected $table = 'ac_user_transfers';

    protected $fillable = [
        'amount',
        'from',
        'to',
        'date',
    ];

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', 'App\Models\User'), 'from');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', 'App\Models\User'), 'to');
    }

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'from' => 'integer',
            'to' => 'integer',
            'amount' => 'decimal:2',
            'date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
