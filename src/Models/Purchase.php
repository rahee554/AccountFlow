<?php

namespace ArtflowStudio\AccountFlow\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A purchase, paid up front or in installments.
 *
 * @property int $id
 * @property string $unique_id
 * @property string $name
 * @property string|null $description
 * @property int $type 1 = advance, 2 = installments
 * @property int|null $installments
 * @property int|null $repayment
 * @property string $amount
 * @property string $amount_paid
 * @property int|null $status
 * @property int|null $category_id
 * @property \Illuminate\Support\Carbon $date
 */
class Purchase extends Model
{
    use HasFactory;

    protected $table = 'ac_purchases';

    protected $fillable = [
        'name',
        'unique_id',
        'type',
        'category_id',
        'purchase_type',
        'installments',
        'date',
        'amount',
        'amount_paid',
        'description',
        'created_at',
        'updated_at',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
