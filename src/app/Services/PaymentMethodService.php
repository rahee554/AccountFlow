<?php

namespace ArtflowStudio\AccountFlow\App\Services;

use App\Models\AccountFlow\PaymentMethod;
use App\Models\AccountFlow\Account;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

/**
 * PaymentMethodService - Payment Method Management
 *
 * Handles all payment method operations including:
 * - Creating and updating payment methods
 * - Linking payment methods to accounts
 * - Status management (active/inactive)
 * - Payment method validation
 *
 * @example
 * // Create payment method
 * $method = PaymentMethodService::create([
 *     'name' => 'Stripe',
 *     'account_id' => 1
 * ]);
 *
 * // Get all active payment methods
 * $methods = PaymentMethodService::getActive();
 */
class PaymentMethodService
{
    /**
     * Create a new payment method
     *
     * @param array{
     *     name: string,
     *     account_id?: int|null,
     *     logo_icon?: string|null,
     *     info?: string|null,
     *     status?: int // 1=active, 2=inactive
     * } $data
     *
     * @return PaymentMethod
     *
     * @throws \Exception
     */
    public static function create(array $data): PaymentMethod
    {
        return DB::transaction(function () use ($data) {
            // Validate required fields
            if (empty($data['name'])) {
                throw new \Exception('Payment method name is required');
            }

            // Validate account if provided
            if (!empty($data['account_id'])) {
                $account = Account::find($data['account_id']);
                if (!$account) {
                    throw new \Exception("Account #{$data['account_id']} not found");
                }
            }

            // Prepare payment method data
            $methodData = [
                'name' => trim($data['name']),
                'account_id' => (int) ($data['account_id'] ?? null) ?: null,
                'logo_icon' => $data['logo_icon'] ?? null,
                'info' => $data['info'] ?? null,
                'status' => (int) ($data['status'] ?? 1), // 1 = active by default
            ];

            return PaymentMethod::create($methodData);
        });
    }

    /**
     * Update a payment method
     *
     * @param PaymentMethod $method
     * @param array $data
     *
     * @return PaymentMethod
     */
    public static function update(PaymentMethod $method, array $data): PaymentMethod
    {
        return DB::transaction(function () use ($method, $data) {
            $updateData = [];

            if (isset($data['name'])) {
                $updateData['name'] = trim($data['name']);
            }

            if (isset($data['account_id'])) {
                // Validate account if provided
                if (!empty($data['account_id'])) {
                    $account = Account::find($data['account_id']);
                    if (!$account) {
                        throw new \Exception("Account #{$data['account_id']} not found");
                    }
                }
                $updateData['account_id'] = (int) $data['account_id'];
            }

            if (isset($data['logo_icon'])) {
                $updateData['logo_icon'] = $data['logo_icon'];
            }

            if (isset($data['info'])) {
                $updateData['info'] = $data['info'];
            }

            if (isset($data['status'])) {
                $updateData['status'] = (int) $data['status'];
            }

            if (!empty($updateData)) {
                $method->update($updateData);
            }

            return $method->fresh();
        });
    }

    /**
     * Get all payment methods
     *
     * @return Collection
     */
    public static function getAll(): Collection
    {
        return PaymentMethod::with('account')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get active payment methods only
     *
     * @return Collection
     */
    public static function getActive(): Collection
    {
        return PaymentMethod::where('status', 1)
            ->with('account')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get payment methods by account
     *
     * @param int $accountId
     * @param bool $onlyActive
     *
     * @return Collection
     */
    public static function getByAccount(int $accountId, bool $onlyActive = true): Collection
    {
        $query = PaymentMethod::where('account_id', $accountId);

        if ($onlyActive) {
            $query->where('status', 1);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Activate a payment method
     *
     * @param PaymentMethod $method
     *
     * @return PaymentMethod
     */
    public static function activate(PaymentMethod $method): PaymentMethod
    {
        return self::update($method, ['status' => 1]);
    }

    /**
     * Deactivate a payment method
     *
     * @param PaymentMethod $method
     *
     * @return PaymentMethod
     */
    public static function deactivate(PaymentMethod $method): PaymentMethod
    {
        return self::update($method, ['status' => 2]);
    }

    /**
     * Link payment method to account
     *
     * @param PaymentMethod $method
     * @param int $accountId
     *
     * @return PaymentMethod
     */
    public static function linkToAccount(PaymentMethod $method, int $accountId): PaymentMethod
    {
        $account = Account::find($accountId);

        if (!$account) {
            throw new \Exception("Account #{$accountId} not found");
        }

        return self::update($method, ['account_id' => $accountId]);
    }

    /**
     * Unlink payment method from account
     *
     * @param PaymentMethod $method
     *
     * @return PaymentMethod
     */
    public static function unlinkFromAccount(PaymentMethod $method): PaymentMethod
    {
        return self::update($method, ['account_id' => null]);
    }

    /**
     * Delete a payment method
     * Only if it has no transactions
     *
     * @param PaymentMethod $method
     *
     * @return bool
     *
     * @throws \Exception
     */
    public static function delete(PaymentMethod $method): bool
    {
        return DB::transaction(function () use ($method) {
            // Check if has transactions
            if ($method->transactions()->exists()) {
                throw new \Exception('Cannot delete payment method with existing transactions');
            }

            return $method->delete();
        });
    }

    /**
     * Validate payment method
     *
     * @param int $methodId
     *
     * @return bool
     */
    public static function validate(int $methodId): bool
    {
        $method = PaymentMethod::find($methodId);

        return $method && $method->status === 1;
    }
}
