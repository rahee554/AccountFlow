<?php

namespace ArtflowStudio\AccountFlow\App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $config = config('accountflow.admin_management', []);

        // If admin management is disabled, allow access
        if (!($config['enabled'] ?? true)) {
            return $next($request);
        }

        $user = auth()->user();

        // No authenticated user
        if (!$user) {
            return $this->handleUnauthorized($config, 'not-authenticated');
        }

        // Check if user is admin
        if (!$this->isUserAdmin($user, $config)) {
            return $this->handleUnauthorized($config, 'not-admin');
        }

        return $next($request);
    }

    /**
     * Check if the user is an admin
     */
    private function isUserAdmin($user, array $config): bool
    {
        $check = $config['check'] ?? 'isAdmin';

        // If check is callable
        if (is_callable($check)) {
            return $check($user);
        }

        // If check is a string method name
        if (is_string($check) && method_exists($user, $check)) {
            return (bool) $user->{$check}();
        }

        // Check for common admin properties/methods
        if (property_exists($user, 'is_admin') && $user->is_admin) {
            return true;
        }

        if (property_exists($user, 'role') && $user->role === 'admin') {
            return true;
        }

        return false;
    }

    /**
     * Handle unauthorized access
     */
    private function handleUnauthorized(array $config, string $reason): Response
    {
        $abortCode = $config['abort_code'] ?? 403;
        $message = match ($reason) {
            'not-authenticated' => 'Please login to manage features.',
            'not-admin' => 'Only administrators can manage features.',
            default => 'Access denied.',
        };

        // If config has redirect_to, redirect instead of abort
        if (isset($config['redirect_to']) && $config['redirect_to']) {
            return redirect()->route($config['redirect_to'])->with('error', $message);
        }

        abort($abortCode, $message);
    }
}
