<?php

namespace ArtflowStudio\AccountFlow\Services;

use ArtflowStudio\AccountFlow\Enums\Feature;
use ArtflowStudio\AccountFlow\Models\AuditTrail;
use ArtflowStudio\AccountFlow\Models\Setting;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Writes and reads the audit trail.
 *
 * Entries are recorded by AuditSubscriber in response to domain events, not by
 * callers remembering to log. In 0.2.x nothing ever called this class, so the
 * audit trail had a table, a settings flag, a route and a UI page — and never
 * recorded a single row.
 */
class AuditService
{
    /**
     * @param array<string,mixed>|null $before
     * @param array<string,mixed>|null $after
     */
    public function log(
        string $action,
        string $modelType = 'System',
        ?int $modelId = null,
        ?array $before = null,
        ?array $after = null,
    ): ?AuditTrail {
        if (! $this->isEnabled()) {
            return null;
        }

        return AuditTrail::create([
            'model_type' => $modelType,
            'model_id' => $modelId,
            'action' => $action,
            'before' => $before,
            'after' => $after,
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Audit logging follows the `audit_trail` feature flag, which is cached —
     * 0.2.x ran a raw query on every single log() call.
     */
    public function isEnabled(): bool
    {
        return Setting::isEnabled(Feature::AuditTrail);
    }

    /**
     * @return Collection<int,AuditTrail>
     */
    public function getRecent(int $limit = 50): Collection
    {
        return AuditTrail::query()->latest('created_at')->limit($limit)->get();
    }

    /**
     * @return Collection<int,AuditTrail>
     */
    public function getByUser(int $userId, int $limit = 50): Collection
    {
        return AuditTrail::query()
            ->where('user_id', $userId)
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int,AuditTrail>
     */
    public function getByAction(string $action, int $limit = 50): Collection
    {
        return AuditTrail::query()
            ->where('action', $action)
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int,AuditTrail>
     */
    public function getForModel(string $modelType, int $modelId, int $limit = 50): Collection
    {
        return AuditTrail::query()
            ->where('model_type', $modelType)
            ->where('model_id', $modelId)
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int,AuditTrail>
     */
    public function getByDateRange(?string $from = null, ?string $to = null, int $limit = 50): Collection
    {
        return AuditTrail::query()
            ->when($from, fn (Builder $q) => $q->whereDate('created_at', '>=', Carbon::parse($from)))
            ->when($to, fn (Builder $q) => $q->whereDate('created_at', '<=', Carbon::parse($to)))
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Remove entries older than the given number of days.
     */
    public function prune(int $days): int
    {
        return AuditTrail::query()
            ->where('created_at', '<', Carbon::now()->subDays($days))
            ->delete();
    }
}
