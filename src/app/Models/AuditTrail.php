<?php

namespace ArtflowStudio\AccountFlow\App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditTrail extends Model
{
    protected $table = 'ac_audit_trail';

    protected $fillable = [
        'model_type', 'model_id', 'action', 'before', 'after', 'user_id', 'created_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

