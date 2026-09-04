<?php

namespace ArtflowStudio\AccountFlow\Concerns;

use ArtflowStudio\AccountFlow\Enums\Ability;
use ArtflowStudio\AccountFlow\Support\Authorization;

/**
 * Authorization helpers for AccountFlow Livewire components.
 *
 * Route middleware only protects the initial page load; a Livewire action
 * arrives over `livewire/update` and never touches the route's middleware
 * stack. Components therefore authorize in `mount()` **and** again in every
 * method that writes.
 */
trait AuthorizesAccountFlow
{
    /**
     * Abort with 403 unless the current user has the ability.
     */
    public function authorizeAccountFlow(Ability $ability): void
    {
        abort_unless(
            Authorization::allows($ability),
            403,
            "You are not authorized to {$ability->label()}.",
        );
    }

    /**
     * Non-throwing check, for hiding UI a user cannot use.
     */
    public function canAccountFlow(Ability $ability): bool
    {
        return Authorization::allows($ability);
    }
}
