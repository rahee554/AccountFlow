<?php

namespace App\Models\AccountFlow;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
