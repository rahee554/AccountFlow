<?php

namespace ArtflowStudio\AccountFlow\App\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class AccountFlowLinkCommand extends Command
{
    protected $signature = 'accountflow:link
                            {--force : Remove existing targets before creating links}
                            {--dry-run : Preview what would be linked without making changes}';

    protected $description = 'Link AccountFlow package source into app directories (development mode â€” creates junctions/symlinks)';

    /**
     * App-relative target => package-src-relative source.
     * Views are NOT listed here â€” they are served directly from vendor via loadViewsFrom().
     *
     * @var array<string, string>
     */
    private const LINK_MAP = [
        'app/Models/AccountFlow'           => 'app/Models',
        'app/Livewire/AccountFlow'          => 'app/Livewire/AccountFlow',
        'app/Http/Controllers/AccountFlow'  => 'app/Http/Controllers/AccountFlow',
    ];

    public function handle(): int
    {
        // __FILE__ is src/app/Console/AccountFlowLinkCommand.php
        // Four levels up is the package root; append /src to get package source.
        $packageSrc  = realpath(dirname(dirname(dirname(dirname(__FILE__)))) . '/src') ?: '';
        $projectRoot = base_path();
        $isDryRun    = (bool) $this->option('dry-run');
        $isForce     = (bool) $this->option('force');

        if (! $packageSrc) {
            $this->error('Could not resolve package src directory.');

            return self::FAILURE;
        }

        if ($isDryRun) {
            $this->warn('[DRY RUN] No changes will be made.');
            $this->newLine();
        }

        $this->components->info('Linking AccountFlow into app directories...');
        $this->newLine();

        $hasErrors = false;

        foreach (self::LINK_MAP as $relativeTarget => $relativeSource) {
            $source = $packageSrc . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativeSource);
            $target = $projectRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativeTarget);

            if (! File::exists($source)) {
                $this->components->error("Source not found: {$relativeSource}");
                $hasErrors = true;
                continue;
            }

            if ($isDryRun) {
                $this->line("  <fg=cyan>â†’</> {$relativeTarget}");
                continue;
            }

            // Handle existing target
            if (File::exists($target) || $this->isLinkOrJunction($target)) {
                if (! $isForce) {
                    if (! $this->confirm("  Target exists: <comment>{$relativeTarget}</comment>. Overwrite?")) {
                        $this->line("  Skipped.");
                        continue;
                    }
                }

                $this->removeTarget($target);
            }

            // Ensure parent directory exists
            $parentDir = dirname($target);
            if (! File::exists($parentDir)) {
                File::makeDirectory($parentDir, 0755, true);
            }

            try {
                $this->createPlatformLink($source, $target, $relativeTarget);
            } catch (\Throwable $e) {
                $this->warn("  Symlink/junction failed ({$e->getMessage()}). Copying instead...");
                File::copyDirectory($source, $target);
                $this->line("  âœ“ Copied (fallback): {$relativeTarget}");
            }
        }

        if ($isDryRun) {
            return self::SUCCESS;
        }

        $this->newLine();

        if ($hasErrors) {
            $this->components->warn('Completed with errors. Check output above.');

            return self::FAILURE;
        }

        $this->components->info('AccountFlow linked successfully.');
        $this->line('  Edit files in <comment>vendor/artflow-studio/accountflow/src/</comment> and changes reflect instantly.');
        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * Detect whether a path is a symlink OR a Windows NTFS junction.
     * PHP's is_link() only works for Unix symlinks and does NOT detect NTFS junctions.
     * readlink() returns the target path for both, so we use that as the reliable check.
     */
    private function isLinkOrJunction(string $path): bool
    {
        return is_link($path) || (file_exists($path) && readlink($path) !== false);
    }

    /**
     * Safely remove a target path.
     *
     * IMPORTANT: For junctions and directory symlinks, always use rmdir() â€” never
     * File::deleteDirectory() which recurses into the target and deletes the package source.
     */
    private function removeTarget(string $path): void
    {
        if ($this->isLinkOrJunction($path)) {
            // rmdir() detaches the junction/symlink on all platforms without touching its contents.
            // Fall back to unlink() for file symlinks if rmdir() rejects the path.
            if (! @rmdir($path)) {
                @unlink($path);
            }

            return;
        }

        if (File::isDirectory($path)) {
            File::deleteDirectory($path);
        } elseif (file_exists($path)) {
            @unlink($path);
        }
    }

    /**
     * Create a directory junction (Windows) or symlink (Unix/Mac).
     * On Windows, tries PHP symlink() first (requires Developer Mode or SE privilege),
     * then falls back to mklink /J (NTFS junction â€” no elevated privileges required).
     *
     * @throws \RuntimeException when all methods fail
     */
    private function createPlatformLink(string $source, string $target, string $label): void
    {
        $realSource = realpath($source) ?: $source;

        if (PHP_OS_FAMILY === 'Windows') {
            // Attempt PHP symlink (works with Developer Mode enabled or SeCreateSymbolicLinkPrivilege)
            if (@symlink($realSource, $target)) {
                $this->line("  âœ“ Linked (symlink): {$label}");

                return;
            }

            // Fallback: NTFS directory junction â€” no elevated privileges required
            $cmd     = sprintf('cmd /C mklink /J "%s" "%s"', $target, $realSource);
            $process = Process::fromShellCommandline($cmd);
            $process->run();

            if ($process->isSuccessful()) {
                $this->line("  âœ“ Linked (junction): {$label}");

                return;
            }

            throw new \RuntimeException(trim($process->getErrorOutput() . ' ' . $process->getOutput()));
        }

        // Unix / macOS
        if (! @symlink($realSource, $target)) {
            $err = error_get_last();
            throw new \RuntimeException($err['message'] ?? 'symlink() failed');
        }

        $this->line("  âœ“ Linked: {$label}");
    }
}

