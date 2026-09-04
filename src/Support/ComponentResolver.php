<?php

namespace ArtflowStudio\AccountFlow\Support;

use Illuminate\Support\Str;
use Livewire\Livewire;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Maps AccountFlow Livewire component names to their package classes.
 *
 * Livewire's convention-based lookup only searches the host application's
 * `App\Livewire` namespace, so package components must be registered
 * explicitly. Registration is deterministic and does not depend on the
 * legacy-alias shim being enabled.
 *
 * Two prefixes resolve to the same class:
 *
 *   accountflow.transactions.transactions   (current)
 *   account-flow.transactions.transactions  (0.2.x, kept for compatibility)
 *
 * Name segments follow the directory structure:
 *
 *   accountflow.accounts.accounts-list  ->  Livewire\Accounts\AccountsList
 *   accountflow.accounts-dashboard      ->  Livewire\AccountsDashboard
 */
final class ComponentResolver
{
    private const NAMESPACE = 'ArtflowStudio\\AccountFlow\\Livewire\\';

    /**
     * Accepted name prefixes. Longest first so "account-flow." is never
     * partially matched by "accountflow.".
     *
     * @var list<string>
     */
    private const PREFIXES = ['account-flow.', 'accountflow.'];

    /**
     * Cached name => class map for this process.
     *
     * @var array<string,class-string>|null
     */
    private static ?array $map = null;

    /**
     * Fallback for anything not in the map.
     */
    public function __invoke(string $name): ?string
    {
        $class = self::classFor($name);

        return $class !== null && class_exists($class) ? $class : null;
    }

    /**
     * Register every component under both prefixes, plus a fallback resolver
     * for names the map does not cover.
     */
    public static function register(): void
    {
        foreach (self::map() as $name => $class) {
            Livewire::component($name, $class);
        }

        Livewire::resolveMissingComponent(new self);
    }

    /**
     * Build the component name => class map by walking src/Livewire.
     *
     * @return array<string,class-string>
     */
    public static function map(): array
    {
        if (self::$map !== null) {
            return self::$map;
        }

        $root = dirname(__DIR__).DIRECTORY_SEPARATOR.'Livewire';
        $map = [];

        if (! is_dir($root)) {
            return self::$map = $map;
        }

        /** @var SplFileInfo $file */
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS)) as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $relative = trim(substr($file->getPathname(), strlen($root)), DIRECTORY_SEPARATOR);
            $relative = str_replace('.php', '', $relative);
            $segments = preg_split('#[\\\\/]#', $relative) ?: [];

            $class = self::NAMESPACE.implode('\\', $segments);
            $name = implode('.', array_map(
                static fn (string $segment): string => Str::kebab($segment),
                $segments,
            ));

            foreach (self::PREFIXES as $prefix) {
                $map[$prefix.$name] = $class;
            }
        }

        return self::$map = $map;
    }

    /**
     * Derive a class name from a component name without touching the filesystem.
     */
    private static function classFor(string $name): ?string
    {
        foreach (self::PREFIXES as $prefix) {
            if (! str_starts_with($name, $prefix)) {
                continue;
            }

            return self::NAMESPACE.collect(explode('.', substr($name, strlen($prefix))))
                ->map(fn (string $segment): string => Str::studly($segment))
                ->implode('\\');
        }

        return null;
    }
}
