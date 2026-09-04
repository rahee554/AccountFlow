<?php

namespace ArtflowStudio\AccountFlow\Models;

use ArtflowStudio\AccountFlow\Concerns\HasPackageFactory;
use ArtflowStudio\AccountFlow\Enums\CategoryType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * An income or expense category. `parent_id` null means a top-level group.
 *
 * @property int $id
 * @property int|null $type
 * @property string|null $name
 * @property int|null $parent_id
 * @property int $privacy
 * @property string|null $icon
 * @property int $status
 */
class Category extends Model
{
    use HasPackageFactory;

    protected $table = 'ac_categories';

    protected $fillable = [
        'type',
        'name',
        'parent_id',
        'privacy',
        'icon',
        'status',
        'added_by',
    ];

    public function categoryType(): ?CategoryType
    {
        return CategoryType::tryParse($this->type);
    }

    public function isIncome(): bool
    {
        return (int) $this->type === CategoryType::Income->value;
    }

    public function isExpense(): bool
    {
        return (int) $this->type === CategoryType::Expense->value;
    }

    /**
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * @return BelongsTo<self, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<self, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }

    public function scopeOfType(Builder $query, CategoryType|int $type): Builder
    {
        return $query->where('type', $type instanceof CategoryType ? $type->value : $type);
    }

    public function scopeTopLevel(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Public URL of this category's icon, or null when it has none.
     */
    public function iconUrl(): ?string
    {
        return $this->icon
            ? asset(config('accountflow.asset_path').'icons/accounts_icons/'.$this->icon)
            : null;
    }

    /**
     * `type` is cast to int, not to CategoryType. Views and services compare it
     * loosely (`$row->type == 1`, `$category->type === 1`), and an enum cast
     * would make every one of those comparisons silently false. Call
     * categoryType() when you want the enum.
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'type' => 'integer',
            'parent_id' => 'integer',
            'privacy' => 'integer',
            'status' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
