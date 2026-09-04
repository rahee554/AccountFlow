<?php

namespace ArtflowStudio\AccountFlow\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * ArtflowStudio\AccountFlow\Models\EquityPartner
 *
 * @property int $id
 * @property string $name
 * @property string|null $email
 * @property bool $is_active
 * @property string|null $ownership_percentage
 * @property Carbon|null $joined_at
 * @property Carbon|null $left_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
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
     * Scope a query to only include active partners.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Return the attribute casts for the model.
     *
     * @return array<string,string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'ownership_percentage' => 'decimal:4',
            'current_equity' => 'decimal:2',
            'is_active' => 'boolean',
            'joined_at' => 'date',
            'left_at' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /*
     * There is deliberately no `name` accessor/mutator here.
     *
     * 0.2.x stored strtolower($value) and read back ucwords($value), which
     * destroyed the casing of every partner name it touched: "ABC Ltd" became
     * "Abc Ltd" and "McDonald" became "Mcdonald", with no way to recover the
     * original. Names are now stored and returned exactly as entered.
     */
}
