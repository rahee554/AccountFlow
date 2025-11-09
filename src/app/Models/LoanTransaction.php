<?php

namespace App\Models\AccountFlow;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanTransaction extends Model
{
    use HasFactory;

    protected $table = 'ac_loan_trx';

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'id', 'trx_id');
    }
}
