<?php

namespace App\Models\AccountFlow;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionTemplate extends Model
{
    /**
     * Table backing this model.
     */
    protected $table = 'ac_trx_templates';

    /**
     * Mass assignable attributes.
     *
     * @var string[]
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
     * Type constants.
     */
    public const TYPE_INCOME = 1;

    public const TYPE_EXPENSE = 2;

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

    /**
     * Default account for the template.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Account::class, 'account_id');
    }

    /**
     * Default category for the template.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(\App\Models\AccountFlow\AcCategory::class, 'category_id');
    }

    /**
     * Default payment method for the template.
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(\App\Models\AccountFlow\PaymentMethod::class, 'payment_method');
    }

    /**
     * User who created the template.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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
}

