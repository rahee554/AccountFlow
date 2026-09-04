<?php

namespace ArtflowStudio\AccountFlow\Services;

use ArtflowStudio\AccountFlow\Enums\LoanType;
use ArtflowStudio\AccountFlow\Enums\TransactionType;
use ArtflowStudio\AccountFlow\Exceptions\AccountFlowException;
use ArtflowStudio\AccountFlow\Models\Loan;
use ArtflowStudio\AccountFlow\Models\LoanTransaction;
use ArtflowStudio\AccountFlow\Models\Transaction;
use ArtflowStudio\AccountFlow\Support\UniqueId;
use Illuminate\Support\Facades\DB;

/**
 * Loans taken and given, and the money that moves with them.
 *
 * `CreateLoan` wrote a row to `ac_loans` and stopped there — receiving a
 * 500,000 loan left every balance untouched, so the cash never appeared
 * anywhere. Recording a loan now posts to the ledger:
 *
 *   Borrowed   → income  on the receiving account (cash in)
 *   Lent       → expense on the paying account   (cash out)
 *
 * Repayments post the opposite way, and `outstanding()` reports what is left.
 *
 * @example
 * $loan = AccountFlow::loans()->borrow(500000, $partnerId, ['name' => 'Bank loan']);
 * AccountFlow::loans()->repay($loan, 25000);
 * AccountFlow::loans()->outstanding($loan);
 */
class LoanService
{
    public function __construct(
        private readonly TransactionService $transactions = new TransactionService,
    ) {}

    /**
     * Money borrowed — cash comes in.
     *
     * @param array<string,mixed> $attributes
     *
     * @throws AccountFlowException
     */
    public function borrow(float $amount, int $partnerId, array $attributes = []): Loan
    {
        return $this->open($amount, $partnerId, LoanType::Borrowed, $attributes);
    }

    /**
     * Money lent out — cash goes out.
     *
     * @param array<string,mixed> $attributes
     *
     * @throws AccountFlowException
     */
    public function lend(float $amount, int $partnerId, array $attributes = []): Loan
    {
        return $this->open($amount, $partnerId, LoanType::Lent, $attributes);
    }

    /**
     * Record a repayment.
     *
     * On a borrowed loan the money goes out; on a loan you gave, it comes back in.
     *
     * @param array<string,mixed> $attributes
     *
     * @throws AccountFlowException
     */
    public function repay(Loan $loan, float $amount, array $attributes = []): LoanTransaction
    {
        if ($amount <= 0) {
            throw new AccountFlowException('A repayment must be greater than 0.');
        }

        $outstanding = $this->outstanding($loan);

        if ($amount > $outstanding + 0.001) {
            throw new AccountFlowException(sprintf(
                'Repaying %s would exceed the %s still outstanding on loan #%d.',
                number_format($amount, 2),
                number_format($outstanding, 2),
                $loan->id,
            ));
        }

        // Repaying reverses the direction the loan money originally moved.
        $direction = $loan->type() === LoanType::Borrowed
            ? TransactionType::Expense
            : TransactionType::Income;

        return DB::transaction(function () use ($loan, $amount, $direction, $attributes): LoanTransaction {
            $transaction = $this->transactions->create([
                'type' => $direction,
                'amount' => $amount,
                'account_id' => $attributes['account_id'] ?? null,
                'category_id' => $attributes['category_id'] ?? null,
                'payment_method' => $attributes['payment_method'] ?? null,
                'date' => $attributes['date'] ?? null,
                'description' => $attributes['description'] ?? "Loan repayment: {$loan->name}",
            ]);

            $loanTransaction = LoanTransaction::create([
                'unique_id' => UniqueId::for(LoanTransaction::class),
                'loan_id' => $loan->id,
                'trx_id' => $transaction->id,
            ]);

            $this->refreshStatus($loan);

            return $loanTransaction;
        });
    }

    /**
     * How much of a loan has been repaid.
     */
    public function repaid(Loan $loan): float
    {
        return (float) Transaction::query()
            ->whereIn('id', LoanTransaction::where('loan_id', $loan->id)->select('trx_id'))
            // The opening posting is not a repayment.
            ->whereKeyNot($loan->openingTransactionId() ?? 0)
            ->sum('amount');
    }

    /**
     * How much is still owed.
     */
    public function outstanding(Loan $loan): float
    {
        return max(0.0, (float) $loan->amount - $this->repaid($loan));
    }

    /**
     * @return array<string,mixed>
     */
    public function summary(Loan $loan): array
    {
        $repaid = $this->repaid($loan);
        $principal = (float) $loan->amount;

        return [
            'loan_id' => (int) $loan->id,
            'name' => $loan->name,
            'type' => $loan->type()?->label(),
            'principal' => $principal,
            'repaid' => $repaid,
            'outstanding' => max(0.0, $principal - $repaid),
            'percentage_repaid' => $principal > 0 ? round(($repaid / $principal) * 100, 2) : 0.0,
            'is_settled' => $repaid >= $principal - 0.001,
        ];
    }

    /**
     * @param array<string,mixed> $attributes
     *
     * @throws AccountFlowException
     */
    private function open(float $amount, int $partnerId, LoanType $type, array $attributes): Loan
    {
        if ($amount <= 0) {
            throw new AccountFlowException('A loan amount must be greater than 0.');
        }

        return DB::transaction(function () use ($amount, $partnerId, $type, $attributes): Loan {
            $loan = Loan::create([
                'unique_id' => UniqueId::for(Loan::class),
                'name' => $attributes['name'] ?? ($type === LoanType::Borrowed ? 'Loan received' : 'Loan given'),
                'description' => $attributes['description'] ?? null,
                'amount' => $amount,
                'loan_type' => $type->value,
                'loan_partner_id' => $partnerId,
                'roi' => $attributes['roi'] ?? null,
                'installments' => $attributes['installments'] ?? null,
                'installment_type' => $attributes['installment_type'] ?? null,
                // 3 = not returned, per the status comment on ac_loans.
                'status' => 3,
                'date' => $attributes['date'] ?? now()->toDateString(),
                'due_date' => $attributes['due_date'] ?? null,
            ]);

            // Borrowing brings cash in; lending sends it out.
            $transaction = $this->transactions->create([
                'type' => $type->transactionType(),
                'amount' => $amount,
                'account_id' => $attributes['account_id'] ?? null,
                'category_id' => $attributes['category_id'] ?? null,
                'payment_method' => $attributes['payment_method'] ?? null,
                'date' => $attributes['date'] ?? null,
                'description' => $attributes['description'] ?? $loan->name,
            ]);

            LoanTransaction::create([
                'unique_id' => UniqueId::for(LoanTransaction::class),
                'loan_id' => $loan->id,
                'trx_id' => $transaction->id,
            ]);

            return $loan->fresh();
        });
    }

    /**
     * Move a loan between not-returned / partially-returned / returned.
     */
    private function refreshStatus(Loan $loan): void
    {
        $repaid = $this->repaid($loan);
        $principal = (float) $loan->amount;

        $status = match (true) {
            $repaid >= $principal - 0.001 => 1,   // returned
            $repaid > 0 => 2,                     // partially returned
            default => 3,                         // not returned
        };

        $loan->forceFill(['status' => $status])->save();
    }
}
