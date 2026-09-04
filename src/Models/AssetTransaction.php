<?php

namespace ArtflowStudio\AccountFlow\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Links an asset to the ledger entry that paid for or realised it.
 *
 * @property int $id
 * @property string $unique_id
 * @property int|null $asset_id
 * @property int|null $trx_id
 */
class AssetTransaction extends Model
{
    use HasFactory;

    protected $table = 'ac_assets_trx';

    /**
     * Neither $fillable nor $guarded was declared, so Eloquent's default
     * `$guarded = ['*']` blocked every create() call on this model.
     */
    protected $fillable = [
        'unique_id',
        'asset_id',
        'trx_id',
    ];

    /**
     * @return BelongsTo<Asset, $this>
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    /**
     * @return BelongsTo<Transaction, $this>
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'trx_id');
    }

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'asset_id' => 'integer',
            'trx_id' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
