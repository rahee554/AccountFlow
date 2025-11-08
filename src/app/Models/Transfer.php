<?php

namespace App\Models\AccountFlow;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    use HasFactory;

    protected $table = 'ac_transfers';

    protected $fillable = [
        'unique_id',
        'amount',
        'from_account',
        'to_account',
        'description',
        'date',
        'created_by',
    ];

    public function fromAccount()
    {
        return $this->belongsTo(Account::class, 'from_account');
    }

    public function toAccount()
    {
        return $this->belongsTo(Account::class, 'to_account');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

