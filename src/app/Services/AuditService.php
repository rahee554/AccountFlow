<?php

namespace ArtflowStudio\AccountFlow\App\Services;

use App\Models\AccountFlow\AuditTrail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

/**
 * AuditService - Audit Trail Management
 *
 * Tracks and logs all transaction and change activities for compliance
 * and accountability purposes.
 *
 * @example
 * // Log an action
 * AuditService::log('transaction_created', [
 *     'transaction_id' => 123,
 *     'amount' => 1000
 * ]);
 *
 * // Get audit logs
 * $logs = AuditService::getRecent();
 */
class AuditService
{
    /**
     * Log an audit trail entry
     *
     * @param string $action
     * @param string $modelType
     * @param int|null $modelId
     * @param array|null $before
     * @param array|null $after
     *
     * @return AuditTrail|null
     */
    public static function log(
        string $action,
        string $modelType = 'System',
        ?int $modelId = null,
        ?array $before = null,
        ?array $after = null
    ): ?AuditTrail {
        // Check if audit trail is enabled
        if (!self::isEnabled()) {
            return null;
        }

        return DB::transaction(function () use ($action, $modelType, $modelId, $before, $after) {
            return AuditTrail::create([
                'model_type' => $modelType,
                'model_id' => $modelId,
                'action' => $action,
                'before' => $before,
                'after' => $after,
                'user_id' => Auth::id(),
            ]);
        });
    }

    /**
     * Check if audit trail is enabled
     */
    private static function isEnabled(): bool
    {
        try {
            $setting = DB::table('ac_settings')->where('key', 'audit_trail')->first();
            return $setting && $setting->value === 'enabled';
        } catch (\Exception $e) {
            return true; // Default to enabled if settings table doesn't exist
        }
    }

    /**
     * Get recent audit logs
     *
     * @param int $limit
     *
     * @return Collection
     */
    public static function getRecent(int $limit = 50): Collection
    {
        return AuditTrail::latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get audit logs for a specific user
     *
     * @param int $userId
     * @param int $limit
     *
     * @return Collection
     */
    public static function getByUser(int $userId, int $limit = 50): Collection
    {
        return AuditTrail::where('user_id', $userId)
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get audit logs for a specific action
     *
     * @param string $action
     * @param int $limit
     *
     * @return Collection
     */
    public static function getByAction(string $action, int $limit = 50): Collection
    {
        return AuditTrail::where('action', $action)
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get audit logs within date range
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @param int $limit
     *
     * @return Collection
     */
    public static function getByDateRange(
        ?string $startDate = null,
        ?string $endDate = null,
        int $limit = 50
    ): Collection {
        $query = AuditTrail::query();

        if ($startDate) {
            $query->whereDate('created_at', '>=', Carbon::parse($startDate));
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', Carbon::parse($endDate));
        }

        return $query->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Log transaction creation
     *
     * @param int $transactionId
     * @param array $data
     *
     * @return AuditTrail|null
     */
    public static function logTransactionCreated(int $transactionId, array $data): ?AuditTrail
    {
        return self::log(
            'created',
            'Transaction',
            $transactionId,
            null,
            $data
        );
    }

    /**
     * Log transaction update
     *
     * @param int $transactionId
     * @param array $before
     * @param array $after
     *
     * @return AuditTrail|null
     */
    public static function logTransactionUpdated(int $transactionId, array $before, array $after): ?AuditTrail
    {
        return self::log(
            'updated',
            'Transaction',
            $transactionId,
            $before,
            $after
        );
    }

    /**
     * Log transaction deletion
     *
     * @param int $transactionId
     * @param array $data
     *
     * @return AuditTrail|null
     */
    public static function logTransactionDeleted(int $transactionId, array $data): ?AuditTrail
    {
        return self::log(
            'deleted',
            'Transaction',
            $transactionId,
            $data,
            null
        );
    }

    /**
     * Log account creation
     *
     * @param int $accountId
     * @param array $data
     *
     * @return AuditTrail|null
     */
    public static function logAccountCreated(int $accountId, array $data): ?AuditTrail
    {
        return self::log(
            'created',
            'Account',
            $accountId,
            null,
            $data
        );
    }

    /**
     * Log budget creation
     *
     * @param int $budgetId
     * @param array $data
     *
     * @return AuditTrail|null
     */
    public static function logBudgetCreated(int $budgetId, array $data): ?AuditTrail
    {
        return self::log(
            'created',
            'Budget',
            $budgetId,
            null,
            $data
        );
    }

    /**
     * Get audit trail summary
     *
     * @return array
     */
    public static function getSummary(): array
    {
        $total = AuditTrail::count();
        $today = AuditTrail::whereDate('created_at', Carbon::today())->count();
        $thisMonth = AuditTrail::whereMonth('created_at', Carbon::now()->month)->count();

        $actionCounts = AuditTrail::selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->get()
            ->keyBy('action')
            ->map(fn ($item) => $item->count);

        return [
            'total_entries' => $total,
            'today' => $today,
            'this_month' => $thisMonth,
            'by_action' => $actionCounts->toArray(),
        ];
    }

    /**
     * Delete old audit logs
     *
     * @param int $daysOld
     *
     * @return int
     */
    public static function deleteOlderThan(int $daysOld = 90): int
    {
        $cutoffDate = Carbon::now()->subDays($daysOld);

        return DB::transaction(function () use ($cutoffDate) {
            return AuditTrail::where('created_at', '<', $cutoffDate)->delete();
        });
    }

    /**
     * Export audit logs as array
     *
     * @param string|null $startDate
     * @param string|null $endDate
     *
     * @return array
     */
    public static function export(?string $startDate = null, ?string $endDate = null): array
    {
        $logs = self::getByDateRange($startDate, $endDate, 10000);

        return $logs->map(function ($log) {
            return [
                'id' => $log->id,
                'model_type' => $log->model_type,
                'model_id' => $log->model_id,
                'action' => $log->action,
                'before' => $log->before,
                'after' => $log->after,
                'user_id' => $log->user_id,
                'created_at' => $log->created_at->toIso8601String(),
            ];
        })->toArray();
    }
}
