<?php

namespace ArtflowStudio\AccountFlow\Models;

use ArtflowStudio\AccountFlow\Concerns\HasPackageFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One recorded change to an AccountFlow record.
 *
 * @property int $id
 * @property string $model_type
 * @property int $model_id
 * @property string $action
 * @property array<string,mixed>|null $before
 * @property array<string,mixed>|null $after
 * @property int|null $user_id
 */
class AuditTrail extends Model
{
    use HasPackageFactory;

    protected $table = 'ac_audit_trail';

    protected $fillable = [
        'model_type',
        'model_id',
        'action',
        'before',
        'after',
        'user_id',
    ];

    /**
     * Resolved from the application's auth config rather than a hardcoded
     * `\App\Models\User`, which tied the package to one application layout.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', 'App\Models\User'));
    }

    public function scopeForModel(Builder $query, string $type, int $id): Builder
    {
        return $query->where('model_type', $type)->where('model_id', $id);
    }

    public function scopeAction(Builder $query, string $action): Builder
    {
        return $query->where('action', $action);
    }

    /**
     * Fields whose value differs between before and after.
     *
     * @return list<string>
     */
    public function changedFields(): array
    {
        $before = $this->before ?? [];
        $after = $this->after ?? [];

        $keys = array_unique([...array_keys($before), ...array_keys($after)]);

        return array_values(array_filter(
            $keys,
            fn (string $key): bool => $this->normalise($before[$key] ?? null)
                !== $this->normalise($after[$key] ?? null),
        ));
    }

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'model_id' => 'integer',
            'user_id' => 'integer',
            'before' => 'array',
            'after' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Reduce a stored value to a comparable form.
     *
     * A JSON round-trip can turn 100 into "100", and a type-only difference is
     * not a real change. This says so explicitly instead of leaning on a loose
     * `!=`, which reads as an oversight.
     */
    private function normalise(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return is_scalar($value) ? (string) $value : json_encode($value);
    }
}
