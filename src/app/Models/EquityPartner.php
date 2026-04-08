<?php

namespace App\Models\AccountFlow;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

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
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'ac_equity_partners';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<int,string>
     */
    protected $guarded = ['id'];

    /**
     * Return the attribute casts for the model.
     *
     * @return array<string,string>
     */
    protected function casts(): array
    {
        return [
            'id'                   => 'integer',
            'ownership_percentage' => 'decimal:4',
            'current_equity'       => 'decimal:2',
            'is_active'            => 'boolean',
            'joined_at'            => 'date',
            'left_at'              => 'date',
            'created_at'           => 'datetime',
            'updated_at'           => 'datetime',
        ];
    }

    /**
     * Scope a query to only include active partners.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
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