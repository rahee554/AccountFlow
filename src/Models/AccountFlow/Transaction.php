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
        'unqique_id',
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
        
}
