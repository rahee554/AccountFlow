<?php

namespace ArtflowStudio\AccountFlow\Models;

use ArtflowStudio\AccountFlow\Concerns\HasPackageFactory;
use ArtflowStudio\AccountFlow\Enums\LoanType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Money lent out or borrowed.
 *
 * @property int $id
 * @property string|null $unique_id
 * @property string $name
 * @property string $amount
 * @property int $loan_type
 * @property int $loan_partner_id
 * @property int|null $status
 * @property string|null $description
 * @property int|null $roi
 * @property int|null $installments
 * @property int|null $installment_type
 * @property \Illuminate\Support\Carbon $date
 * @property \Illuminate\Support\Carbon|null $due_date
 */
class Loan extends Model
{
    use HasPackageFactory;

    protected $table = 'ac_loans';

    protected $fillable = [
        'unique_id',
        'name',
        'description',
        'amount',
        'loan_type',
        'loan_partner_id',
        'roi',
        'installments',
        'installment_type',
        'status',
        'date',
        'due_date',
        'created_at',
        'updated_at',
    ];

    /**
     * The counterparty.
     *
     * The relation had no explicit foreign key, so Eloquent guessed
     * `loan_user_id` from the related class name; the column is
     * `loan_partner_id`.
     */
    public function loanPartner(): BelongsTo
    {
        return $this->belongsTo(LoanUser::class, 'loan_partner_id');
    }

    /**
     * Snake-case alias kept for 0.2.x callers and views.
     */
    public function loan_partner(): BelongsTo
    {
        return $this->loanPartner();
    }

    /**
     * @return HasMany<LoanTransaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(LoanTransaction::class, 'loan_id');
    }

    /**
     * The id of the posting that opened this loan.
     *
     * The opening entry is the oldest one linked to the loan; everything after
     * it is a repayment.
     */
    public function openingTransactionId(): ?int
    {
        $id = LoanTransaction::query()
            ->where('loan_id', $this->id)
            ->orderBy('id')
            ->value('trx_id');

        return $id !== null ? (int) $id : null;
    }

    public function type(): ?LoanType
    {
        return LoanType::tryFrom((int) $this->loan_type);
    }

    public function scopeLent(Builder $query): Builder
    {
        return $query->where('loan_type', LoanType::Lent->value);
    }

    public function scopeBorrowed(Builder $query): Builder
    {
        return $query->where('loan_type', LoanType::Borrowed->value);
    }

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'loan_type' => 'integer',
            'loan_partner_id' => 'integer',
            'installments' => 'integer',
            'installment_type' => 'integer',
            'status' => 'integer',
            'amount' => 'decimal:2',
            'date' => 'date',
            'due_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
