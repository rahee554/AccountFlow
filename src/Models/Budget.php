<?php

namespace ArtflowStudio\AccountFlow\Models;

use ArtflowStudio\AccountFlow\Concerns\HasPackageFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A spending target for an account and category over a period.
 *
 * The window comes from `period` + `year` + `month`. There are no
 * `start_date` / `end_date` / `alert_threshold` / `status` columns, whatever
 * the 0.2.x BudgetService assumed.
 *
 * @property int $id
 * @property int|null $account_id
 * @property int|null $category_id
 * @property string $amount
 * @property string $period 'monthly' or 'yearly'
 * @property int|null $year
 * @property int|null $month
 * @property string|null $description
 * @property int|null $created_by
 */
class Budget extends Model
{
    use HasPackageFactory;

    protected $table = 'ac_budgets';

    protected $fillable = [
        'account_id',
        'category_id',
        'amount',
        'period',
        'year',
        'month',
        'description',
        'created_by',
    ];

    /**
     * @return BelongsTo<Account, $this>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', 'App\Models\User'), 'created_by');
    }

    public function scopeForYear(Builder $query, int $year): Builder
    {
        return $query->where('year', $year);
    }

    public function isMonthly(): bool
    {
        return $this->period === 'monthly';
    }

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'account_id' => 'integer',
            'category_id' => 'integer',
            'created_by' => 'integer',
            'year' => 'integer',
            'month' => 'integer',
            'amount' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
