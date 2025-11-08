<?php

namespace App\Models\AccountFlow;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    protected $table = 'ac_assets';

    protected $fillable = [
        'name',
        'description',
        'category_id',
        'value',
        'status',
        'acquisition_date',
    ];

    public function transactions()
    {
        return $this->hasMany(AssetTransaction::class, 'asset_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
}

