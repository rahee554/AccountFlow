<?php

namespace ArtflowStudio\AccountFlow\Exceptions;

class RecordNotFoundException extends AccountFlowException
{
    public static function account(int|string $id): self
    {
        return new self("Account #{$id} was not found.");
    }

    public static function category(int|string $id): self
    {
        return new self("Category #{$id} was not found.");
    }

    public static function paymentMethod(int|string $id): self
    {
        return new self("Payment method #{$id} was not found or is inactive.");
    }
}
