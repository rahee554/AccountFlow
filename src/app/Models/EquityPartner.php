
<?php

namespace ArtflowStudio\AccountFlow\App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Carbon;
use HasFactory;

/**
 * App\Models\AccountFlow\EquityPartner
 *
 * @property int $id
 * @property string $name
 * @property string|null $email
 * @property float $percentage
 * @property Carbon|null $invested_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class EquityPartner extends Model
{

    /**
     * The table associated with the model.
     */
    protected string $table = 'ac_equity_partners';

    /**
     * The attributes that aren't mass assignable.
     *
     * Using guarded to protect the primary key by default.
     *
     * @var array<int,string>
     */
    protected array $guarded = ['id'];

    /**
     * Return the attribute casts for the model.
     *
     * @return array<string,string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'percentage' => 'decimal:4',
            'invested_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Scope a query to only include partners with a non-zero percentage.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('percentage', '>', 0);
    }

    /**
     * A convenience method to determine whether the partner is active.
     */
    public function isActive(): bool
    {
        return (float) $this->percentage > 0.0;
    }

    /**
     * Ensure the partner name is stored lowercase and presented as title case.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => $value === null ? null : ucwords($value),
            set: fn (string $value): string => strtolower($value),
        );
    }
}
