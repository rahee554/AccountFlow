<?php

namespace App\Models\AccountFlow;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlannedPayment extends Model
{
    use HasFactory;

    protected $table = 'ac_planned_payments';

    protected $fillable = [
        'name',
        'category_id',
        'description',
        'amount',
        'trx_id',
        'due_date',
        'period',
        'auto_post_date',
        'schedule_type',
        'weekly_days',
        'monthly_day',
        'auto_post',
        'recurring',
    ];

    protected $casts = [
        'auto_post' => 'boolean',
        'recurring' => 'boolean',
        'weekly_days' => 'array',
        'due_date' => 'date',
        'auto_post_date' => 'date',
        'monthly_day' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'trx_id');
    }
}

