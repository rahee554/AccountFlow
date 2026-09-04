<?php

namespace ArtflowStudio\AccountFlow\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Links a loan to the ledger entry that disbursed or repaid it.
 *
 * @property int $id
 * @property string $unique_id
 * @property int|null $loan_id
 * @property int|null $trx_id
 */
class LoanTransaction extends Model
{
    use HasFactory;

    protected $table = 'ac_loan_trx';

    /**
     * Neither $fillable nor $guarded was declared, so Eloquent's default
     * `$guarded = ['*']` blocked every create() call on this model.
     */
    protected $fillable = [
        'unique_id',
        'loan_id',
        'trx_id',
    ];

    /**
     * @return BelongsTo<Loan, $this>
     */
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class, 'loan_id');
    }

    /**
     * This row points at the transaction, so it belongsTo. 0.2.x declared
     * `hasOne(Transaction::class, 'id', 'trx_id')`, which happened to return
     * the right row but inverted the relationship — breaking eager-load
     * constraints and any attempt to query through it.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'trx_id');
    }

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'loan_id' => 'integer',
            'trx_id' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
