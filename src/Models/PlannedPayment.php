<?php

namespace ArtflowStudio\AccountFlow\Models;

use ArtflowStudio\AccountFlow\Concerns\HasPackageFactory;
use ArtflowStudio\AccountFlow\Enums\ScheduleType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A scheduled, optionally recurring payment.
 *
 * `$fillable` used to list `trx_id`, `due_date`, `period`, `auto_post_date` and
 * `recurring` — none of which are columns — while omitting the real
 * `start_date`, `end_date`, `last_run_date` and `next_run_date`. Creating one
 * therefore failed outright with "Unknown column 'due_date'", so the whole
 * feature was unusable.
 *
 * The real columns, per the migration: name, category_id, description, amount,
 * start_date, end_date, schedule_type, weekly_days, monthly_day, auto_post,
 * last_run_date, next_run_date.
 *
 * @property int $id
 * @property string $name
 * @property int|null $category_id
 * @property string|null $description
 * @property string $amount
 * @property \Illuminate\Support\Carbon|null $start_date
 * @property \Illuminate\Support\Carbon|null $end_date
 * @property string $schedule_type
 * @property array<int,int>|null $weekly_days
 * @property int|null $monthly_day
 * @property bool $auto_post
 * @property \Illuminate\Support\Carbon|null $last_run_date
 * @property \Illuminate\Support\Carbon|null $next_run_date
 */
class PlannedPayment extends Model
{
    use HasPackageFactory;

    protected $table = 'ac_planned_payments';

    protected $fillable = [
        'name',
        'category_id',
        'description',
        'amount',
        'start_date',
        'end_date',
        'schedule_type',
        'weekly_days',
        'monthly_day',
        'auto_post',
        'last_run_date',
        'next_run_date',
    ];

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function schedule(): ScheduleType
    {
        return ScheduleType::tryFrom((string) $this->schedule_type) ?? ScheduleType::Once;
    }

    public function isRecurring(): bool
    {
        return $this->schedule()->isRecurring();
    }

    /**
     * Auto-posting, in-window, and due on or before today.
     */
    public function scopeDue(Builder $query, mixed $asOf = null): Builder
    {
        $asOf ??= now();

        return $query
            ->where('auto_post', true)
            ->where(fn (Builder $q) => $q->whereNull('next_run_date')->orWhereDate('next_run_date', '<=', $asOf))
            ->where(fn (Builder $q) => $q->whereNull('start_date')->orWhereDate('start_date', '<=', $asOf))
            ->where(fn (Builder $q) => $q->whereNull('end_date')->orWhereDate('end_date', '>=', $asOf));
    }

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'category_id' => 'integer',
            'monthly_day' => 'integer',
            'amount' => 'decimal:2',
            'auto_post' => 'boolean',
            'weekly_days' => 'array',
            'start_date' => 'date',
            'end_date' => 'date',
            'last_run_date' => 'date',
            'next_run_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
