<?php

namespace ArtflowStudio\AccountFlow\Exceptions;

class InvalidTransactionException extends AccountFlowException
{
    public static function amountNotPositive(mixed $amount): self
    {
        return new self(sprintf(
            'Transaction amount must be greater than 0, got [%s].',
            is_scalar($amount) ? (string) $amount : gettype($amount),
        ));
    }

    public static function unknownType(mixed $type): self
    {
        return new self(sprintf(
            'Transaction type must be 1 (income) or 2 (expense), got [%s].',
            is_scalar($type) ? (string) $type : gettype($type),
        ));
    }

    public static function alreadyReversed(int $id): self
    {
        return new self("Transaction #{$id} has already been reversed.");
    }

    public static function cannotReverseAReversal(int $id): self
    {
        return new self("Transaction #{$id} is itself a reversal and cannot be reversed.");
    }
}
