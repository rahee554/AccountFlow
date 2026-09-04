<?php

namespace ArtflowStudio\AccountFlow\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A reusable set of transaction defaults.
 *
 * @property int $id
 * @property string $name
 * @property int|null $account_id
 * @property int|null $category_id
 * @property string|null $amount
 * @property int|null $payment_method
 * @property int $type
 * @property string|null $description
 * @property array|null $meta
 * @property int|null $created_by
 * @property bool $active
 */
class TransactionTemplate extends Model
{
    /**
     * Type constants.
     */
    public const TYPE_INCOME = 1;

    public const TYPE_EXPENSE = 2;

    /**
     * Table backing this model.
     */
    protected $table = 'ac_trx_templates';

    /**
     * Mass assignable attributes.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'account_id',
        'category_id',
        'amount',
        'payment_method',
        'type',
        'description',
        'meta',
        'created_by',
        'active',
    ];

    /**
     * Default account for the template.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    /**
     * Default category for the template.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Default payment method for the template.
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method');
    }

    /**
     * User who created the template.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', 'App\Models\User'), 'created_by');
    }

    /**
     * Scope to only active templates.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * Scope to filter by type (income/expense).
     */
    public function scopeOfType(Builder $query, int $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Casts for attributes.
     *
     * @return array<string,string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'meta' => 'array',
            'active' => 'boolean',
            'type' => 'integer',
        ];
    }
}
