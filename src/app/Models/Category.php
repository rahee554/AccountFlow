<?php

namespace App\Models\AccountFlow;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

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

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
