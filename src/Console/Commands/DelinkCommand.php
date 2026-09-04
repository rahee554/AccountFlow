<?php

namespace ArtflowStudio\AccountFlow\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DelinkCommand extends Command
{
    protected $signature = 'accountflow:delink
                            {--force : Skip confirmation prompts}
                            {--dry-run : Show what would be removed without actually removing}';

    protected $description = 'Remove AccountFlow links/copies from the app directories (never touches the package source)';

    /** Absolute path to the package src directory */
    private string $packageSrc;

    /** Absolute path to the project root */
    private string $projectRoot;

    public function handle(): int
    {
        $this->packageSrc = dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/src';
        $this->projectRoot = base_path();

        $isDryRun = (bool) $this->option('dry-run');
        $isForce = (bool) $this->option('force');

        if ($isDryRun) {
            $this->warn('[DRY RUN] No files will be deleted.');
        }

        $this->info('🔍 Scanning AccountFlow links...');
        $this->newLine();

        $removals = $this->collectRemovals();

        if (empty($removals)) {
            $this->info('Nothing to delink — no AccountFlow links or copies found.');

            return self::SUCCESS;
        }

        $this->table(['Type', 'Path'], array_map(
            fn ($r) => [$r['type'], $r['path']],
            $removals,
        ));

        if ($isDryRun) {
            $this->newLine();
            $this->info('[DRY RUN] '.count($removals).' item(s) would be removed.');

            return self::SUCCESS;
        }

        if (! $isForce && ! $this->confirm('Remove the '.count($removals).' item(s) listed above?')) {
            $this->info('Aborted.');

            return self::SUCCESS;
        }

        foreach ($removals as $removal) {
            $this->remove($removal);
        }

        $this->newLine();
        $this->info('✅ AccountFlow delinked successfully.');

        return self::SUCCESS;
    }

    /**
     * Build the full list of paths to remove, categorised by how they were linked.
     *
     * @return array<int, array{type: string, path: string, removal: string}>
     */
    private function collectRemovals(): array
    {
        $removals = [];

        // --- symlink / junction targets (created by accountflow:link) ---
        $symlinkTargets = [
            'app/Models/AccountFlow',
            'app/Http/Controllers/AccountFlow',
            'app/Livewire/AccountFlow',
        ];

        foreach ($symlinkTargets as $relative) {
            $target = $this->projectRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);

            // is_link() misses Windows NTFS junctions; @readlink() works for both symlinks and junctions.
            // The @ suppresses the warning when the path doesn't exist.
            if (is_link($target) || @readlink($target) !== false) {
                $removals[] = ['type' => 'symlink/junction', 'path' => $target, 'removal' => 'junction'];
            }
        }

        // --- merged migration files (copied individually into database/migrations) ---
        $migrationSource = $this->packageSrc.DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'migrations';
        $migrationTarget = $this->projectRoot.DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'migrations';

        if (File::isDirectory($migrationSource) && File::isDirectory($migrationTarget)) {
            foreach (File::files($migrationSource) as $sourceFile) {
                $destPath = $migrationTarget.DIRECTORY_SEPARATOR.$sourceFile->getFilename();
                if (File::exists($destPath) && ! is_link($destPath)) {
                    $removals[] = ['type' => 'copied migration', 'path' => $destPath, 'removal' => 'file'];
                }
            }
        }

        // --- copied seeder files ---
        $seederSource = $this->packageSrc.DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'seeders';
        $seederTarget = $this->projectRoot.DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'seeders';

        if (File::isDirectory($seederSource) && File::isDirectory($seederTarget)) {
            foreach (File::files($seederSource) as $sourceFile) {
                $destPath = $seederTarget.DIRECTORY_SEPARATOR.$sourceFile->getFilename();
                if (File::exists($destPath) && ! is_link($destPath)) {
                    $removals[] = ['type' => 'copied seeder', 'path' => $destPath, 'removal' => 'file'];
                }
            }
        }

        return $removals;
    }

    /**
     * Remove a single entry.
     *
     * @param array{type: string, path: string, removal: string} $removal
     */
    private function remove(array $removal): void
    {
        $path = $removal['path'];

        // Safety guard — never touch anything inside the package source
        $normalizedPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $normalizedPackageSrc = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $this->packageSrc);

        if (str_starts_with($normalizedPath, $normalizedPackageSrc)) {
            $this->error("SKIPPED (inside package source): {$path}");

            return;
        }

        if ($removal['removal'] === 'junction') {
            // Use rmdir() for both PHP symlinks and Windows NTFS junctions.
            // unlink() removes file symlinks; rmdir() removes directory symlinks and junctions.
            // NEVER use File::deleteDirectory() — it would recurse into the junction target
            // and delete the package source files.
            if (@rmdir($path) || @unlink($path)) {
                $this->line("  ✓ Removed symlink/junction: {$path}");
            } else {
                $this->error("  ✗ Failed to remove: {$path}");
            }

            return;
        }

        if ($removal['removal'] === 'file') {
            if (@unlink($path)) {
                $this->line("  ✓ Removed file: {$path}");
            } else {
                $this->error("  ✗ Failed to remove: {$path}");
            }
        }
    }
}
