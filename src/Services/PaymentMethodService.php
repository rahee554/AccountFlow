<?php

namespace ArtflowStudio\AccountFlow\Services;

use ArtflowStudio\AccountFlow\Models\Account;
use ArtflowStudio\AccountFlow\Models\PaymentMethod;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

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
 * $method = Accountflow::paymentMethods()->create([
 *     'name' => 'Stripe',
 *     'account_id' => 1
 * ]);
 *
 * // Get all active payment methods
 * $methods = Accountflow::paymentMethods()->getActive();
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
     * @throws Exception
     */
    public function create(array $data): PaymentMethod
    {
        return DB::transaction(function () use ($data) {
            // Validate required fields
            if (empty($data['name'])) {
                throw new Exception('Payment method name is required');
            }

            // Validate account if provided
            if (! empty($data['account_id'])) {
                $account = Account::find($data['account_id']);
                if (! $account) {
                    throw new Exception("Account #{$data['account_id']} not found");
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
     */
    public function update(PaymentMethod $method, array $data): PaymentMethod
    {
        return DB::transaction(function () use ($method, $data) {
            $updateData = [];

            if (isset($data['name'])) {
                $updateData['name'] = trim($data['name']);
            }

            if (isset($data['account_id'])) {
                // Validate account if provided
                if (! empty($data['account_id'])) {
                    $account = Account::find($data['account_id']);
                    if (! $account) {
                        throw new Exception("Account #{$data['account_id']} not found");
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

            if (! empty($updateData)) {
                $method->update($updateData);
            }

            return $method->fresh();
        });
    }

    /**
     * Get all payment methods
     */
    public function getAll(): Collection
    {
        return PaymentMethod::with('account')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get active payment methods only
     */
    public function getActive(): Collection
    {
        return PaymentMethod::where('status', 1)
            ->with('account')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get payment methods by account
     */
    public function getByAccount(int $accountId, bool $onlyActive = true): Collection
    {
        $query = PaymentMethod::where('account_id', $accountId);

        if ($onlyActive) {
            $query->where('status', 1);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Activate a payment method
     */
    public function activate(PaymentMethod $method): PaymentMethod
    {
        return $this->update($method, ['status' => 1]);
    }

    /**
     * Deactivate a payment method
     */
    public function deactivate(PaymentMethod $method): PaymentMethod
    {
        return $this->update($method, ['status' => 2]);
    }

    /**
     * Link payment method to account
     */
    public function linkToAccount(PaymentMethod $method, int $accountId): PaymentMethod
    {
        $account = Account::find($accountId);

        if (! $account) {
            throw new Exception("Account #{$accountId} not found");
        }

        return $this->update($method, ['account_id' => $accountId]);
    }

    /**
     * Unlink payment method from account
     */
    public function unlinkFromAccount(PaymentMethod $method): PaymentMethod
    {
        return $this->update($method, ['account_id' => null]);
    }

    /**
     * Delete a payment method
     * Only if it has no transactions
     *
     *
     *
     * @throws Exception
     */
    public function delete(PaymentMethod $method): bool
    {
        return DB::transaction(function () use ($method) {
            // Check if has transactions
            if ($method->transactions()->exists()) {
                throw new Exception('Cannot delete payment method with existing transactions');
            }

            return $method->delete();
        });
    }

    /**
     * Validate payment method
     */
    public function validate(int $methodId): bool
    {
        $method = PaymentMethod::find($methodId);

        return $method && $method->status === 1;
    }
}
