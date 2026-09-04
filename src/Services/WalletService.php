<?php

namespace ArtflowStudio\AccountFlow\Services;

use ArtflowStudio\AccountFlow\Exceptions\AccountFlowException;
use ArtflowStudio\AccountFlow\Models\UserTransfer;
use ArtflowStudio\AccountFlow\Models\UserWallet;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Money held by a person rather than in a business account.
 *
 * A wallet is a staff float or advance: cash the business has handed to someone
 * who has not spent or returned it yet.
 *
 * The whole module was inert before 0.3.0 — `ac_user_transfers` had no model,
 * the create screen had no save method, and nothing ever updated
 * `ac_user_wallets.balance`. You could open the page and achieve nothing.
 *
 * Two kinds of movement, and the difference matters:
 *
 *   topUp / settle   money crosses between the business and a person, so it
 *                    posts to the ledger and changes an account balance
 *   transfer         money passes between two people; the business still holds
 *                    the same total, so nothing posts to the ledger
 *
 * Wallet balances are written under a row lock, like account balances.
 *
 * @example
 * AccountFlow::wallets()->topUp($userId, 5000, account: 'Cash Account');
 * AccountFlow::wallets()->transfer($fromUserId, $toUserId, 1000);
 * AccountFlow::wallets()->settle($userId, 2000, account: 'Cash Account');
 * AccountFlow::wallets()->balance($userId);
 */
class WalletService
{
    public function __construct(
        private readonly MoneyService $money = new MoneyService,
    ) {}

    /**
     * The wallet for a user, created on first use.
     */
    public function forUser(int $userId): UserWallet
    {
        // `ac_user_wallets` has no unique index on user_id, so an install may
        // already hold duplicates. Take the first rather than assuming one.
        $wallet = UserWallet::query()->where('user_id', $userId)->orderBy('id')->first();

        return $wallet ?? UserWallet::create([
            'user_id' => $userId,
            'balance' => 0,
            'status' => UserWallet::STATUS_ACTIVE,
        ]);
    }

    public function balance(int $userId): float
    {
        return round((float) $this->forUser($userId)->balance, 2);
    }

    /**
     * Hand business money to a person.
     *
     * Cash leaves the business account and lands in the wallet.
     *
     * @throws AccountFlowException
     */
    public function topUp(int $userId, float $amount, string|int|null $account = null, ?string $description = null): UserWallet
    {
        $this->assertPositive($amount);

        return DB::transaction(function () use ($userId, $amount, $account, $description): UserWallet {
            $wallet = $this->assertUsable($userId);

            // Money genuinely leaves the business here, so it is an expense.
            $this->money->spent(
                $amount,
                $description ?? "Wallet top-up for user #{$userId}",
                account: $account,
            );

            return $this->adjust($wallet, $amount);
        });
    }

    /**
     * A person returns money to the business.
     *
     * @throws AccountFlowException
     */
    public function settle(int $userId, float $amount, string|int|null $account = null, ?string $description = null): UserWallet
    {
        $this->assertPositive($amount);

        return DB::transaction(function () use ($userId, $amount, $account, $description): UserWallet {
            $wallet = $this->assertUsable($userId);
            $this->assertSufficient($wallet, $amount);

            $this->money->received(
                $amount,
                $description ?? "Wallet settlement from user #{$userId}",
                account: $account,
            );

            return $this->adjust($wallet, -$amount);
        });
    }

    /**
     * Money passed from one person to another.
     *
     * The business still holds the same total, so nothing posts to the ledger —
     * only the two wallet balances move.
     *
     * @throws AccountFlowException
     */
    public function transfer(int $fromUserId, int $toUserId, float $amount, mixed $date = null): UserTransfer
    {
        $this->assertPositive($amount);

        if ($fromUserId === $toUserId) {
            throw new AccountFlowException('A wallet transfer needs two different people.');
        }

        return DB::transaction(function () use ($fromUserId, $toUserId, $amount, $date): UserTransfer {
            $from = $this->assertUsable($fromUserId);
            $to = $this->assertUsable($toUserId);

            $this->assertSufficient($from, $amount);

            $this->adjust($from, -$amount);
            $this->adjust($to, $amount);

            return UserTransfer::create([
                'amount' => $amount,
                'from' => $fromUserId,
                'to' => $toUserId,
                'date' => $date !== null ? Carbon::parse($date) : Carbon::now(),
            ]);
        });
    }

    /**
     * Stop a wallet being used without deleting it.
     */
    public function freeze(int $userId): UserWallet
    {
        $wallet = $this->forUser($userId);
        $wallet->forceFill(['status' => UserWallet::STATUS_FROZEN])->save();

        return $wallet;
    }

    public function unfreeze(int $userId): UserWallet
    {
        $wallet = $this->forUser($userId);
        $wallet->forceFill(['status' => UserWallet::STATUS_ACTIVE])->save();

        return $wallet;
    }

    /**
     * Total sitting in everyone's wallets — money the business has handed out
     * and not yet accounted for.
     */
    public function totalOutstanding(): float
    {
        return round((float) UserWallet::query()->sum('balance'), 2);
    }

    /**
     * @return array{balance: float, received: float, sent: float, status: string}
     */
    public function summary(int $userId): array
    {
        return [
            'balance' => $this->balance($userId),
            'received' => round((float) UserTransfer::where('to', $userId)->sum('amount'), 2),
            'sent' => round((float) UserTransfer::where('from', $userId)->sum('amount'), 2),
            'status' => $this->forUser($userId)->isFrozen() ? 'frozen' : 'active',
        ];
    }

    /**
     * Move a wallet balance under a row lock, the same discipline account
     * balances use.
     */
    private function adjust(UserWallet $wallet, float $delta): UserWallet
    {
        $locked = UserWallet::query()->lockForUpdate()->find($wallet->id);

        if ($locked === null) {
            throw new AccountFlowException("Wallet #{$wallet->id} disappeared mid-update.");
        }

        $locked->setAttribute('balance', (float) $locked->balance + $delta);
        $locked->save();

        return $locked;
    }

    /**
     * @throws AccountFlowException
     */
    private function assertUsable(int $userId): UserWallet
    {
        $wallet = $this->forUser($userId);

        if ($wallet->isFrozen()) {
            throw new AccountFlowException("The wallet for user #{$userId} is frozen.");
        }

        return $wallet;
    }

    /**
     * @throws AccountFlowException
     */
    private function assertSufficient(UserWallet $wallet, float $amount): void
    {
        if ((float) $wallet->balance < $amount && ! config('accountflow.allow_negative_balance', false)) {
            throw new AccountFlowException(sprintf(
                'That wallet holds %s, which is less than %s.',
                number_format((float) $wallet->balance, 2),
                number_format($amount, 2),
            ));
        }
    }

    /**
     * @throws AccountFlowException
     */
    private function assertPositive(float $amount): void
    {
        if ($amount <= 0) {
            throw new AccountFlowException('A wallet amount must be greater than 0.');
        }
    }
}
