<?php

namespace ArtflowStudio\AccountFlow\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetTransaction extends Model
{
    use HasFactory;

    // Specify the table name
    protected $table = 'ac_assets_trx';

    // Define the relationship with AccountAsset model
    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'trx_id');
    }
    // Add other model-specific code as needed
}

