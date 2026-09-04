<?php

namespace ArtflowStudio\AccountFlow\Http\Middleware;

use ArtflowStudio\AccountFlow\Enums\Ability;
use ArtflowStudio\AccountFlow\Support\Authorization;
use Closure;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route middleware: accountflow.can:<ability>
 *
 * Guards the initial page load. Livewire actions bypass route middleware, so
 * components authorize again through AuthorizesAccountFlow.
 */
class Authorize
{
    public function handle(Request $request, Closure $next, string $ability): Response
    {
        $case = Ability::tryFrom($ability);

        if ($case === null) {
            throw new InvalidArgumentException(
                "Unknown AccountFlow ability [{$ability}]. See ".Ability::class,
            );
        }

        abort_unless(Authorization::allows($case), 403, "You are not authorized to {$case->label()}.");

        return $next($request);
    }
}
