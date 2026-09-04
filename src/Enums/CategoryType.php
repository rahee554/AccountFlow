<?php

namespace ArtflowStudio\AccountFlow\Enums;

enum CategoryType: int
{
    case Income = 1;
    case Expense = 2;

    public static function tryParse(self|int|string|null $value): ?self
    {
        if ($value instanceof self) {
            return $value;
        }

        if ($value === null) {
            return null;
        }

        if (is_int($value)) {
            return self::tryFrom($value);
        }

        return match (strtolower(trim($value))) {
            'income', '1' => self::Income,
            'expense', '2' => self::Expense,
            default => null,
        };
    }

    public function toTransactionType(): TransactionType
    {
        return $this === self::Income ? TransactionType::Income : TransactionType::Expense;
    }

    public function label(): string
    {
        return $this === self::Income ? 'Income' : 'Expense';
    }
}
