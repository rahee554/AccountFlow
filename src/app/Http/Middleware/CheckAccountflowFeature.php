<?php

namespace ArtflowStudio\AccountFlow\App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use ArtflowStudio\AccountFlow\Facades\Accountflow;

class CheckAccountflowFeature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        // Check if the feature is enabled
        if (!Accountflow::features()->isEnabled($feature)) {
            // Return 403 or redirect based on your preference
            abort(403, 'This feature is currently disabled.');
        }

        return $next($request);
    }
}
