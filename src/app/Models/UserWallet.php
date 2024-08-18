<?php

namespace ArtflowStudio\AccountFlow\App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserWallet extends Model
{
    use HasFactory;

    protected $table = 'ac_user_wallets';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

