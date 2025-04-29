<?php

namespace App\Models\AccountFlow;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Transfer extends Model
{
    use HasFactory;

    protected $table = 'ac_transfers';

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
