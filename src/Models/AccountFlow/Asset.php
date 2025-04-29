<?php

namespace App\Models\AccountFlow;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;
    protected $table = 'ac_assets';

    public function transactions()
    {
        return $this->hasMany(AssetTransaction::class, 'asset_id');
    }
    public function category()
    {
        return $this->hasOne(Category::class, 'id', 'category_id');
    }
}
