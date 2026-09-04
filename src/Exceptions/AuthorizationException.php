<?php

namespace ArtflowStudio\AccountFlow\Exceptions;

use ArtflowStudio\AccountFlow\Enums\Ability;

class AuthorizationException extends AccountFlowException
{
    public static function forAbility(Ability $ability): self
    {
        return new self("Not authorized to {$ability->label()} [{$ability->value}].");
    }
}
