<?php

namespace ArtflowStudio\AccountFlow\Enums;

/**
 * Income or expense.
 *
 * Backed by the same integers 0.2.x wrote to `ac_transactions.type`, so no data
 * conversion is needed. `tryParse()` also accepts the legacy string forms
 * ('income', '1') that leaked into the column, which is why the old code was
 * littered with `type IN ("income","1",1)`.
 */
enum TransactionType: int
{
    case Income = 1;
    case Expense = 2;

    /**
     * Coerce whatever the caller passed into a case.
     */
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

    /**
     * +1 for income, -1 for expense — the multiplier applied to a balance.
     */
    public function sign(): int
    {
        return $this === self::Income ? 1 : -1;
    }

    public function opposite(): self
    {
        return $this === self::Income ? self::Expense : self::Income;
    }

    public function label(): string
    {
        return match ($this) {
            self::Income => 'Income',
            self::Expense => 'Expense',
        };
    }

    /**
     * Bootstrap-ish colour token used by the bundled views.
     */
    public function color(): string
    {
        return $this === self::Income ? 'success' : 'danger';
    }

    /**
     * @return array<int,string>
     */
    public static function options(): array
    {
        return [
            self::Income->value => self::Income->label(),
            self::Expense->value => self::Expense->label(),
        ];
    }
}
