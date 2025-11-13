<?php

namespace ArtflowStudio\AccountFlow\App\Console;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\File;

class AccountFlowLinkCommand extends Command
{
    protected $signature = 'accountflow:link {--force : Force the creation of the link}';
    protected $description = 'Link AccountFlow package files to the main project (creates symlinks or copies files)';

    public function handle()
    {
        $this->info('🔗 Linking AccountFlow package files...');

        // Get the package path (src directory)
        $packagePath = dirname(dirname(dirname(dirname(__FILE__)))) . '/src';
        $projectRoot = base_path();

        // Define paths to link
        $links = [
            'models' => [
                'source' => $packagePath . '/app/Models',
                'target' => $projectRoot . '/app/Models/AccountFlow',
                'type' => 'directory',
            ],
            'http-controllers' => [
                'source' => $packagePath . '/app/Http/Controllers/AccountFlow',
                'target' => $projectRoot . '/app/Http/Controllers/AccountFlow',
                'type' => 'directory',
            ],
            'livewire-components' => [
                'source' => $packagePath . '/app/Livewire/AccountFlow',
                'target' => $projectRoot . '/app/Livewire/AccountFlow',
                'type' => 'directory',
            ],
            'database' => [
                'source' => $packagePath . '/database/migrations',
                'target' => $projectRoot . '/database/migrations',
                'type' => 'directory',
                'merge' => true,  // Merge files (copy contents) instead of creating a symlink to a subfolder
            ],
            'seeders' => [
                'source' => $packagePath . '/database/seeders',
                'target' => $projectRoot . '/database/seeders',
                'type' => 'files',  // Copy only files, not the directory itself
                'copy_files_only' => true,
            ],
            'views' => [
                'source' => $packagePath . '/resources/views/vendor/artflow-studio/accountflow',
                'target' => $projectRoot . '/resources/views/vendor/artflow-studio/accountflow',
                'type' => 'directory',
            ],
            'assets' => [
                'source' => $packagePath . '/public/vendor/artflow-studio/accountflow',
                'target' => $projectRoot . '/public/vendor/artflow-studio/accountflow',
                'type' => 'directory',
            ],
        ];

        $force = $this->option('force');

        foreach ($links as $name => $link) {
            $this->createLink($link['source'], $link['target'], $force, $link['merge'] ?? false, $link['copy_files_only'] ?? false);
        }

        $this->info('✅ AccountFlow package files linked successfully!');
        $this->info('📝 Config file location: ' . config_path('accountflow.php'));
        $this->info('🚀 You can now edit files in the package and they will be reflected in your project.');
    }

    /**
     * Create a symlink or copy files
     *
     * @param string $source
     * @param string $target
     * @param bool $force
     * @param bool $merge  If true and target exists, copy contents into target instead of linking
     * @param bool $copyFilesOnly  If true, copy only files (not directory itself) to avoid nested links
     */
    private function createLink($source, $target, $force = false, $merge = false, $copyFilesOnly = false)
    {
        // Normalize paths
        $source = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $source);
        $target = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $target);

        if (!File::exists($source)) {
            $this->error("Source path not found: {$source}");
            return;
        }

        // If copyFilesOnly is requested, copy only individual files to the target directory
        // This prevents nested links and ensures files go into existing project folder
        if ($copyFilesOnly && is_dir($source)) {
            // Ensure target directory exists
            if (!File::exists($target)) {
                File::makeDirectory($target, 0755, true);
            }

            $sourceFiles = File::files($source);
            foreach ($sourceFiles as $file) {
                $fileName = $file->getFilename();
                $destPath = $target . DIRECTORY_SEPARATOR . $fileName;

                // Skip if file already exists, unless force is enabled
                if (File::exists($destPath) && !$force) {
                    $this->warn("File already exists: {$destPath}");
                    continue;
                }

                File::copy($file->getPathname(), $destPath);
            }

            $this->line("✓ Copied {" . count($sourceFiles) . "} file(s): {$source} → {$target}");
            return;
        }

        // If merge is requested and target exists, copy contents into target and return
        if ($merge && File::exists($target)) {
            $this->info("Merging files from {$source} into existing {$target}");
            $this->copyContents($source, $target);
            $this->line("✓ Merged: {$source} → {$target}");
            return;
        }

        // If target exists (file/dir/link) handle according to $force or user choice
        if (File::exists($target) || is_link($target)) {
            if (!$force) {
                $this->warn("Target already exists: {$target}");
                if ($this->confirm('Overwrite?')) {
                    $this->removeTarget($target);
                } else {
                    return;
                }
            } else {
                $this->removeTarget($target);
            }
        }

        // Ensure parent directory exists
        $targetDir = dirname($target);
        if (!File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        // If merge requested and target doesn't exist yet, create dir and copy contents
        if ($merge && !File::exists($target)) {
            File::makeDirectory($target, 0755, true);
            $this->copyContents($source, $target);
            $this->line("✓ Copied: {$source} → {$target}");
            return;
        }

        // Attempt to create a symlink/junction on the host OS, fallback to copying
        try {
            $this->createPlatformLink($source, $target);
        } catch (\Throwable $e) {
            $this->warn("Could not create symlink/junction, copying files instead: {$e->getMessage()}");
            if (is_dir($source)) {
                File::copyDirectory($source, $target);
            } else {
                File::copy($source, $target);
            }
            $this->line("✓ Copied: {$source} → {$target}");
        }
    }

    /**
     * Remove a target (file/dir/link) safely
     */
    private function removeTarget($target)
    {
        // If it's a link, unlink
        if (is_link($target)) {
            @unlink($target);
            return;
        }

        // If directory, delete directory, otherwise delete file
        if (File::isDirectory($target)) {
            File::deleteDirectory($target);
        } else {
            @unlink($target);
        }
    }

    /**
     * Copy contents of one directory into another (merging)
     */
    private function copyContents($source, $destination)
    {
        $sourceFiles = File::allFiles($source);
        foreach ($sourceFiles as $file) {
            $relative = ltrim(str_replace($source, '', $file->getPathname()), DIRECTORY_SEPARATOR);
            $destPath = $destination . DIRECTORY_SEPARATOR . $relative;
            $destDir = dirname($destPath);
            if (!File::exists($destDir)) {
                File::makeDirectory($destDir, 0755, true);
            }
            File::copy($file->getPathname(), $destPath);
        }
    }

    /**
     * Attempt to create a link/junction depending on the platform.
     * Throws on failure.
     */
    private function createPlatformLink($source, $target)
    {
        $isDir = is_dir($source);

        // Use realpath where possible
        $realSource = realpath($source) ?: $source;
        $realTarget = $target;

        if (PHP_OS_FAMILY === 'Windows') {
            // Try PHP symlink first (may require privileges or developer mode)
            try {
                if (@symlink($realSource, $realTarget)) {
                    $this->line("✓ Linked (symlink): {$target}");
                    return;
                }
            } catch (\Throwable $e) {
                // fall through to mklink attempt
            }

            // Try mklink/junction. Use /J (junction) for directories because it's more permissive.
            if ($isDir) {
                $cmd = sprintf('cmd /C mklink /J "%s" "%s"', $realTarget, $realSource);
            } else {
                // For files, mklink without flags
                $cmd = sprintf('cmd /C mklink "%s" "%s"', $realTarget, $realSource);
            }

            $process = Process::fromShellCommandline($cmd);
            $process->run();

            if ($process->isSuccessful()) {
                $this->line("✓ Linked (mklink): {$target}");
                return;
            }

            // As a last attempt try mklink /D (directory symlink) if /J failed for a directory
            if ($isDir) {
                $cmd2 = sprintf('cmd /C mklink /D "%s" "%s"', $realTarget, $realSource);
                $process2 = Process::fromShellCommandline($cmd2);
                $process2->run();
                if ($process2->isSuccessful()) {
                    $this->line("✓ Linked (mklink /D): {$target}");
                    return;
                }
            }

            // If none succeeded, throw with collected output
            $message = trim($process->getErrorOutput() . ' ' . $process->getOutput());
            throw new \RuntimeException('mklink failed: ' . $message);
        } else {
            // Unix-like: try native symlink
            if (!@symlink($realSource, $realTarget)) {
                $err = error_get_last();
                throw new \RuntimeException('symlink failed: ' . ($err['message'] ?? 'unknown error'));
            }
            $this->line("✓ Linked: {$target}");
            return;
        }
    }
}
