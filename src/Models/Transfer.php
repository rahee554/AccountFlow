<?php

namespace ArtflowStudio\AccountFlow\Models;

use ArtflowStudio\AccountFlow\Concerns\HasPackageFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Money moved between two accounts.
 *
 * A transfer is posted as two linked ledger entries — `from_trx_id` debits the
 * source, `to_trx_id` credits the destination. Before 0.3.0 a transfer wrote
 * only this row, so the money never actually moved.
 *
 * @property int $id
 * @property string $unique_id
 * @property string $amount
 * @property int $from_account
 * @property int $to_account
 * @property int|null $from_trx_id
 * @property int|null $to_trx_id
 * @property string|null $description
 * @property \Illuminate\Support\Carbon $date
 * @property int $created_by
 */
class Transfer extends Model
{
    use HasPackageFactory;

    protected $table = 'ac_transfers';

    protected $fillable = [
        'unique_id',
        'amount',
        'from_account',
        'to_account',
        'from_trx_id',
        'to_trx_id',
        'description',
        'date',
        'created_by',
    ];

    /**
     * @return BelongsTo<Account, $this>
     */
    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'from_account');
    }

    /**
     * @return BelongsTo<Account, $this>
     */
    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'to_account');
    }

    /**
     * @return BelongsTo<Transaction, $this>
     */
    public function fromTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'from_trx_id');
    }

    /**
     * @return BelongsTo<Transaction, $this>
     */
    public function toTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'to_trx_id');
    }

    /**
     * Every ledger entry belonging to this transfer.
     *
     * @return HasMany<Transaction, $this>
     */
    public function entries(): HasMany
    {
        return $this->hasMany(Transaction::class, 'transfer_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', 'App\Models\User'), 'created_by');
    }

    /**
     * The posted legs, whether they were linked by id or only by transfer_id.
     *
     * @return Collection<int,Transaction>
     */
    public function legs(): Collection
    {
        return Transaction::query()
            ->where('transfer_id', $this->id)
            ->orWhereIn('id', array_filter([$this->from_trx_id, $this->to_trx_id]))
            ->get();
    }

    /**
     * Has this transfer actually been posted to the ledger?
     */
    public function isPosted(): bool
    {
        return $this->from_trx_id !== null && $this->to_trx_id !== null;
    }

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'from_account' => 'integer',
            'to_account' => 'integer',
            'from_trx_id' => 'integer',
            'to_trx_id' => 'integer',
            'created_by' => 'integer',
            'amount' => 'decimal:2',
            'date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
