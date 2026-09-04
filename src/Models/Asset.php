<?php

namespace ArtflowStudio\AccountFlow\Models;

use ArtflowStudio\AccountFlow\Concerns\HasPackageFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A capital asset.
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int|null $category_id
 * @property string $value
 * @property int $status 1 = operating, 2 = not operating, 3 = sold
 * @property \Illuminate\Support\Carbon $acquisition_date
 */
class Asset extends Model
{
    use HasPackageFactory;

    protected $table = 'ac_assets';

    protected $fillable = [
        'name',
        'description',
        'category_id',
        'value',
        'status',
        'acquisition_date',
    ];

    /**
     * @return HasMany<AssetTransaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(AssetTransaction::class, 'asset_id');
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'category_id' => 'integer',
            'status' => 'integer',
            'value' => 'decimal:2',
            'acquisition_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
