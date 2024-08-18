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
                'merge' => true,  // Merge files instead of creating subfolder
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
            $this->createLink($link['source'], $link['target'], $force);
        }

        $this->info('✅ AccountFlow package files linked successfully!');
        $this->info('📝 Config file location: ' . config_path('accountflow.php'));
        $this->info('🚀 You can now edit files in the package and they will be reflected in your project.');
    }

    /**
     * Create a symlink or copy files
     */
    private function createLink($source, $target, $force = false)
    {
        if (!File::exists($source)) {
            $this->error("Source path not found: {$source}");
            return;
        }

        // Check if target exists
        if (File::exists($target) || is_link($target)) {
            if (!$force) {
                $this->warn("Target already exists: {$target}");
                if ($this->confirm('Overwrite?')) {
                    File::deleteDirectory($target);
                    if (is_link($target)) {
                        unlink($target);
                    }
                } else {
                    return;
                }
            } else {
                File::deleteDirectory($target);
                if (is_link($target)) {
                    unlink($target);
                }
            }
        }

        $targetDir = dirname($target);
        if (!File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        // Try to create symlink on Windows/Unix
        try {
            if (PHP_OS_FAMILY === 'Windows') {
                // On Windows, try mklink or copy instead
                $this->createWindowsLink($source, $target);
            } else {
                // On Unix, create symlink
                symlink($source, $target);
                $this->line("✓ Linked: {$target}");
            }
        } catch (\Exception $e) {
            $this->warn("Could not create symlink, copying files instead: {$e->getMessage()}");
            File::copyDirectory($source, $target);
            $this->line("✓ Copied: {$source} → {$target}");
        }
    }

    /**
     * Create a link on Windows using mklink or copy as fallback
     */
    private function createWindowsLink($source, $target)
    {
        $source = str_replace('/', '\\', $source);
        $target = str_replace('/', '\\', $target);

        // Try mklink first (requires admin)
        $cmd = "mklink /D \"{$target}\" \"{$source}\"";
        
        $process = Process::fromShellCommandline($cmd);
        $process->run();

        if ($process->isSuccessful()) {
            $this->line("✓ Linked: {$target}");
        } else {
            // Fallback to copying
            File::copyDirectory($source, $target);
            $this->line("✓ Copied: {$source} → {$target}");
        }
    }
}

