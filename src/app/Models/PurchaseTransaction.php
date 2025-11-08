<?php

namespace App\Models\AccountFlow;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseTransaction extends Model
{
    use HasFactory;

    protected $table = 'ac_purchase_trx';

    protected $fillable = [
        'unique_id',
        'purchase_id',
        'trx_id',
        'created_at',
        'updated_at',
    ];

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'id', 'trx_id');
    }
    // Add other model-specific code as needed
}

