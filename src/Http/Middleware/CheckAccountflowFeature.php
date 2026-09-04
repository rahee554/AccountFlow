<?php

namespace ArtflowStudio\AccountFlow\Http\Middleware;

use ArtflowStudio\AccountFlow\Facades\Accountflow;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAccountflowFeature
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        // Check if the feature is enabled
        if (! Accountflow::features()->isEnabled($feature)) {
            // Return 403 or redirect based on your preference
            abort(403, 'This feature is currently disabled.');
        }

        return $next($request);
    }
}
