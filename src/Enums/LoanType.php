<?php

namespace ArtflowStudio\AccountFlow\Enums;

enum LoanType: int
{
    case Lent = 1;
    case Borrowed = 2;

    public function label(): string
    {
        return $this === self::Lent ? 'Lent' : 'Borrowed';
    }

    /**
     * Lending money leaves the business; borrowing brings money in.
     */
    public function transactionType(): TransactionType
    {
        return $this === self::Lent ? TransactionType::Expense : TransactionType::Income;
    }
}
