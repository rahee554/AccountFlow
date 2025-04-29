<?php

namespace App\Models\AccountFlow;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $table = 'ac_payment_methods';

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
