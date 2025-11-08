<?php

namespace App\Models\AccountFlow;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    protected $table = 'ac_budgets';

    protected $fillable = [
        'account_id', 'category_id', 'amount', 'period', 'year', 'month', 'description', 'created_by',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

