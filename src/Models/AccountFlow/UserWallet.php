<?php

namespace App\Models\AccountFlow;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserWallet extends Model
{
    use HasFactory;

    protected $table = 'ac_user_wallets';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
