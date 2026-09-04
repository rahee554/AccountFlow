<?php

namespace ArtflowStudio\AccountFlow\Models;

use ArtflowStudio\AccountFlow\Concerns\HasPackageFactory;
use ArtflowStudio\AccountFlow\Enums\TransactionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A single ledger entry.
 *
 * Column names are unchanged from 0.2.x: the payment-method foreign key is
 * `payment_method` (not `payment_method_id`) and the author column is
 * `added_by`. Both are kept because live installs depend on them.
 *
 * @property int $id
 * @property int $account_id
 * @property int|null $category_id
 * @property string $unique_id
 * @property string $amount
 * @property int $type
 * @property int|null $payment_method
 * @property int|null $added_by
 * @property int|null $reversal_of_id
 * @property int|null $transfer_id
 * @property string|null $description
 * @property int|null $invoice_id
 * @property \Illuminate\Support\Carbon|null $date
 * @property \Illuminate\Support\Carbon|null $reversed_at
 *
 * A host application can attach its own payment record by setting
 * `accountflow.models.invoice_payment`; the provider then registers an
 * `invoicePayment` relation. 0.2.x hardcoded \App\Models\InvoicePayment
 * here, tying the package to one specific CRM.
 */
class Transaction extends Model
{
    use HasPackageFactory;

    protected $table = 'ac_transactions';

    protected $fillable = [
        'amount',
        'unique_id',
        'payment_method',
        'account_id',
        'type',
        'category_id',
        'date',
        'description',
        'added_by',
        'invoice_id',
        'reversal_of_id',
        'transfer_id',
        'reversed_at',
        'created_at',
        'updated_at',
    ];

    public function transactionType(): ?TransactionType
    {
        return TransactionType::tryParse($this->type);
    }

    public function isIncome(): bool
    {
        return (int) $this->type === TransactionType::Income->value;
    }

    public function isExpense(): bool
    {
        return (int) $this->type === TransactionType::Expense->value;
    }

    public function isReversed(): bool
    {
        return $this->reversed_at !== null;
    }

    public function isReversal(): bool
    {
        return $this->reversal_of_id !== null;
    }

    /**
     * Amount signed by direction: positive for income, negative for expense.
     */
    public function signedAmount(): float
    {
        return (float) $this->amount * ($this->transactionType()?->sign() ?? 1);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<Account, $this>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * @return BelongsTo<PaymentMethod, $this>
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', 'App\Models\User'), 'added_by');
    }

    /** The transaction this one reverses. */
    public function reverses(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reversal_of_id');
    }

    /** Reversals booked against this transaction. */
    public function reversals(): HasMany
    {
        return $this->hasMany(self::class, 'reversal_of_id');
    }

    public function scopeIncome(Builder $query): Builder
    {
        return $query->where('type', TransactionType::Income->value);
    }

    public function scopeExpense(Builder $query): Builder
    {
        return $query->where('type', TransactionType::Expense->value);
    }

    public function scopeOfType(Builder $query, TransactionType|int $type): Builder
    {
        return $query->where('type', $type instanceof TransactionType ? $type->value : $type);
    }

    public function scopeForAccount(Builder $query, int $accountId): Builder
    {
        return $query->where('account_id', $accountId);
    }

    public function scopeSince(Builder $query, mixed $date): Builder
    {
        return $query->whereDate('date', '>=', $date);
    }

    public function scopeUntil(Builder $query, mixed $date): Builder
    {
        return $query->whereDate('date', '<=', $date);
    }

    /**
     * Inclusive date range; either bound may be null.
     */
    public function scopeBetween(Builder $query, mixed $from = null, mixed $to = null): Builder
    {
        return $query
            ->when($from, fn (Builder $q) => $q->whereDate('date', '>=', $from))
            ->when($to, fn (Builder $q) => $q->whereDate('date', '<=', $to));
    }

    public function scopeNotReversed(Builder $query): Builder
    {
        return $query->whereNull('reversed_at')->whereNull('reversal_of_id');
    }

    /**
     * Exclude the ledger legs of transfers.
     *
     * Moving cash between your own accounts is neither revenue nor a cost, so
     * profit & loss must leave transfers out — while balances must include
     * them, because the cash really did move.
     */
    public function scopeExcludingTransfers(Builder $query): Builder
    {
        return $query->whereNull('transfer_id');
    }

    public function scopeOnlyTransfers(Builder $query): Builder
    {
        return $query->whereNotNull('transfer_id');
    }

    public function isTransferLeg(): bool
    {
        return $this->transfer_id !== null;
    }

    /**
     * @return BelongsTo<Transfer, $this>
     */
    public function transfer(): BelongsTo
    {
        return $this->belongsTo(Transfer::class, 'transfer_id');
    }

    /**
     * `type` is cast to int rather than to TransactionType: views and reports
     * compare it loosely (`$row->type == 1`) and collections filter on it
     * (`->where('type', 1)`), all of which an enum cast would silently break.
     * Use transactionType() for the enum.
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'account_id' => 'integer',
            'category_id' => 'integer',
            'payment_method' => 'integer',
            'added_by' => 'integer',
            'reversal_of_id' => 'integer',
            'transfer_id' => 'integer',
            'type' => 'integer',
            'amount' => 'decimal:2',
            'date' => 'date',
            'reversed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
