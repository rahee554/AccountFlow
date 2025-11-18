<?php

namespace ArtflowStudio\AccountFlow\App\Http\Middleware;

use ArtflowStudio\AccountFlow\App\Services\FeatureService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFeatureEnabled
{
    protected FeatureService $featureService;

    public function __construct(FeatureService $featureService)
    {
        $this->featureService = $featureService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if ($this->featureService->isDisabled($feature)) {
            abort(403, 'This feature is currently disabled.');
        }

        return $next($request);
    }
}
