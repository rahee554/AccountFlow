<?php

namespace ArtflowStudio\AccountFlow\Support;

use Illuminate\Support\Facades\Route;

/**
 * "Is this nav item the page I am on?" — answered in one place so the
 * sidebar and any future horizontal menubar never disagree about which page
 * is open.
 */
class NavState
{
    /**
     * Whether a leaf item points at the current page.
     *
     * `active` is an optional wildcard mask for items whose highlight rule is
     * wider than the route they link to. Falls back to the route itself.
     */
    public static function isActive(array $item): bool
    {
        if (! empty($item['children']) || empty($item['route'])) {
            return false;
        }

        return request()->routeIs($item['active'] ?? $item['route']);
    }

    /**
     * Whether a group contains the current page, at any depth.
     */
    public static function hasActiveChild(array $item): bool
    {
        if (empty($item['children'])) {
            return false;
        }

        foreach (self::routes($item) as $route) {
            if (request()->routeIs($route)) {
                return true;
            }
        }

        return false;
    }

    /**
     * A menu entry's href, or '#' when the route is not defined.
     *
     * route() throws on an unknown name, which would turn one stale menu
     * entry into a 500 on every page that renders the shell. Degrading to
     * '#' keeps the app up; the caller decides whether to flag it.
     */
    public static function url(?array $item): string
    {
        $route = $item['route'] ?? null;

        return $route && Route::has($route) ? route($route) : '#';
    }

    /** The route name behind a dead link, for debug builds only. */
    public static function missingRoute(?array $item): ?string
    {
        $route = $item['route'] ?? null;

        if (! $route || Route::has($route) || ! config('app.debug')) {
            return null;
        }

        return $route;
    }

    /** Every route beneath an item, at any depth, masks preferred. */
    private static function routes(array $node): array
    {
        $routes = isset($node['route']) ? [$node['active'] ?? $node['route']] : [];

        foreach ($node['children'] ?? [] as $child) {
            $routes = array_merge($routes, self::routes($child));
        }

        return $routes;
    }
}
