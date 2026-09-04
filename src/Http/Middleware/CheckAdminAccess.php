<?php

namespace ArtflowStudio\AccountFlow\Http\Middleware;

use ArtflowStudio\AccountFlow\Support\Authorization;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route middleware: accountflow.admin
 *
 * Restricts feature/settings management to administrators, as resolved by
 * `accountflow.admin_management.check`.
 */
class CheckAdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $config = config('accountflow.admin_management', []);

        if (! ($config['enabled'] ?? true)) {
            return $next($request);
        }

        if (Auth::guest()) {
            return $this->deny($config, 'Please sign in to manage AccountFlow.');
        }

        if (! Authorization::isAdmin()) {
            return $this->deny($config, 'Only administrators can manage AccountFlow.');
        }

        return $next($request);
    }

    private function deny(array $config, string $message): Response
    {
        $redirect = $config['redirect_to'] ?? null;

        // Only redirect to a route that actually exists — 0.2.x redirected to
        // a hardcoded 'dashboard' and threw RouteNotFoundException in any app
        // that did not happen to define one.
        if (is_string($redirect) && $redirect !== '' && Route::has($redirect)) {
            return redirect()->route($redirect)->with('error', $message);
        }

        abort((int) ($config['abort_code'] ?? 403), $message);
    }
}
