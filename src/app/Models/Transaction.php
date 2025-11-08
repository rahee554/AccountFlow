<?php

namespace App\Models\AccountFlow;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'ac_transactions';

    protected $fillable = [
        'amount',
        'unique_id',
        'payment_method',
        'account_id',
        'type',
        'category_id',
        'date',
        'description',
        'user_id',
        'created_at',
        'updated_at',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }





    // Transaction.php

    public function scopeIncome($q)
    {
        return $q->whereIn('type', ['income', '1', 1]);
    }

    public function scopeExpense($q)
    {
        return $q->whereIn('type', ['expense', '2', 2]);
    }

    public function scopeSince($q, $date)
    {
        return $q->whereDate('date', '>=', $date);
    }

    public function scopeBetweenMonths($q, $start)
    {
        return $q->whereDate('date', '>=', $start);
    }

}

