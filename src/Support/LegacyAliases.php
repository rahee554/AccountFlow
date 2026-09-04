<?php

namespace ArtflowStudio\AccountFlow\Support;

/**
 * Backward-compatibility shim for the 0.2.x class layout.
 *
 * Before 0.3.0 the package declared its models, Livewire components and
 * controllers in the *host application's* `App\` namespace and loaded them
 * through a hand-rolled autoloader. Those classes now live under
 * `ArtflowStudio\AccountFlow\`, so this registers lazy aliases for the old
 * names.
 *
 * This is deliberately an autoloader rather than a list of eager
 * `class_alias()` calls: an eager list would force every model and component
 * class to load on every request. The resolver only fires when a legacy name
 * is actually requested, and it is appended (never prepended) so a host
 * application's own `App\` classes always win.
 *
 * Disable with `accountflow.legacy_aliases => false` once your app has been
 * updated to the current namespaces.
 */
final class LegacyAliases
{
    /**
     * Legacy namespace prefix => current namespace prefix.
     *
     * @var array<string,string>
     */
    private const PREFIXES = [
        'App\\Models\\AccountFlow\\' => 'ArtflowStudio\\AccountFlow\\Models\\',
        'App\\Livewire\\AccountFlow\\' => 'ArtflowStudio\\AccountFlow\\Livewire\\',
        'App\\Http\\Controllers\\AccountFlow\\' => 'ArtflowStudio\\AccountFlow\\Http\\Controllers\\',
        'ArtflowStudio\\AccountFlow\\App\\Services\\' => 'ArtflowStudio\\AccountFlow\\Services\\',
        'ArtflowStudio\\AccountFlow\\App\\Http\\Middleware\\' => 'ArtflowStudio\\AccountFlow\\Http\\Middleware\\',
        'ArtflowStudio\\AccountFlow\\App\\Console\\' => 'ArtflowStudio\\AccountFlow\\Console\\',
        'ArtflowStudio\\AccountFlow\\App\\Models\\' => 'ArtflowStudio\\AccountFlow\\Models\\',
        'ArtflowStudio\\AccountFlow\\App\\Livewire\\AccountFlow\\' => 'ArtflowStudio\\AccountFlow\\Livewire\\',
    ];

    private static bool $registered = false;

    /**
     * Register the lazy alias autoloader. Safe to call more than once.
     */
    public static function register(): void
    {
        if (self::$registered) {
            return;
        }

        self::$registered = true;

        // Registered without $prepend, so it runs after Composer's autoloader:
        // a host application's own App\ classes always win.
        spl_autoload_register(static function (string $class): void {
            $target = self::resolve($class);

            if ($target !== null && class_exists($target)) {
                class_alias($target, $class);
            }
        });
    }

    /**
     * Map a legacy class name onto its current equivalent.
     */
    public static function resolve(string $class): ?string
    {
        foreach (self::PREFIXES as $legacy => $current) {
            if (str_starts_with($class, $legacy)) {
                return $current.substr($class, strlen($legacy));
            }
        }

        return null;
    }
}
