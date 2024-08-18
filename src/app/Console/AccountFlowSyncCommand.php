<?php

namespace ArtflowStudio\AccountFlow\App\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\Finder;

class AccountFlowSyncCommand extends Command
{
    protected $signature = 'accountflow:sync {--check : Only check for changes, no sync} {--force : Sync all without prompting} {--quiet : Minimize output}';
    protected $description = '🔄 Sync AccountFlow files with interactive selection (package ↔ app bidirectional)';

    protected $packagePath;
    protected $projectPath;
    protected $syncMappings = [];
    protected $changedFiles = [];

    public function handle()
    {
        $this->renderHeader();

        $this->setupPaths();
        $this->buildSyncMappings();
        $this->detectChanges();

        if (empty($this->changedFiles)) {
            $this->info('✅ Perfect! Everything is already in sync.');
            return 0;
        }

        $this->displayChangeSummary();

        if ($this->option('check')) {
            return 0;
        }

        return $this->interactiveSyncProcess();
    }

    protected function renderHeader()
    {
        $this->newLine();
        $this->line('<fg=cyan>╔════════════════════════════════════════════════════════════╗</>');
        $this->line('<fg=cyan>║</>  <fg=green;options=bold>🔄 AccountFlow File Synchronization System</><fg=cyan>  ║</>');
        $this->line('<fg=cyan>╚════════════════════════════════════════════════════════════╝</>');
        $this->newLine();
    }

    protected function setupPaths()
    {
        $this->packagePath = dirname(dirname(dirname(dirname(__FILE__)))) . '/src';
        $this->projectPath = base_path();

        if (!File::exists($this->packagePath)) {
            $this->error("❌ Package path not found: {$this->packagePath}");
            exit(1);
        }
    }

    protected function buildSyncMappings()
    {
        $this->syncMappings = [
            'models' => [
                'source' => $this->packagePath . '/app/Models',
                'target' => $this->projectPath . '/app/Models/AccountFlow',
                'recursive' => true,
                'icon' => '📦',
            ],
            'controllers' => [
                'source' => $this->packagePath . '/app/Http/Controllers/AccountFlow',
                'target' => $this->projectPath . '/app/Http/Controllers/AccountFlow',
                'recursive' => true,
                'icon' => '🎛️ ',
            ],
            'livewire' => [
                'source' => $this->packagePath . '/app/Livewire/AccountFlow',
                'target' => $this->projectPath . '/app/Livewire/AccountFlow',
                'recursive' => true,
                'icon' => '⚡',
            ],
            'views' => [
                'source' => $this->packagePath . '/resources/views/vendor/artflow-studio/accountflow',
                'target' => $this->projectPath . '/resources/views/vendor/artflow-studio/accountflow',
                'recursive' => true,
                'icon' => '🎨',
            ],
            'config' => [
                'source' => $this->packagePath . '/config/accountflow.php',
                'target' => $this->projectPath . '/config/accountflow.php',
                'recursive' => false,
                'icon' => '⚙️ ',
            ],
            'assets' => [
                'source' => $this->packagePath . '/public/vendor/artflow-studio/accountflow',
                'target' => $this->projectPath . '/public/vendor/artflow-studio/accountflow',
                'recursive' => true,
                'icon' => '📁',
            ],
        ];
    }

    protected function detectChanges()
    {
        foreach ($this->syncMappings as $group => $mapping) {
            if (!$mapping['recursive']) {
                $this->checkFile($group, $mapping);
            } else {
                $this->checkDirectory($group, $mapping);
            }
        }
    }

    protected function checkFile($group, $mapping)
    {
        $source = $mapping['source'];
        $target = $mapping['target'];

        if (!File::exists($source) && !File::exists($target)) {
            return;
        }

        $sourceHash = File::exists($source) ? md5_file($source) : null;
        $targetHash = File::exists($target) ? md5_file($target) : null;

        if ($sourceHash !== $targetHash) {
            $sourceTime = File::exists($source) ? filemtime($source) : 0;
            $targetTime = File::exists($target) ? filemtime($target) : 0;

            $direction = $sourceTime >= $targetTime ? 'source_to_target' : 'target_to_source';

            $this->changedFiles[] = [
                'group' => $group,
                'type' => $this->determineChangeType($sourceHash, $targetHash),
                'file' => basename($source),
                'source_path' => $source,
                'target_path' => $target,
                'direction' => $direction,
            ];
        }
    }

    protected function checkDirectory($group, $mapping)
    {
        $source = $mapping['source'];
        $target = $mapping['target'];

        if (!File::exists($source) && !File::exists($target)) {
            return;
        }

        if (File::exists($source)) {
            $this->compareDirectories($source, $target, $group, 'source_to_target');
        }

        if (File::exists($target)) {
            $this->compareDirectories($target, $source, $group, 'target_to_source');
        }
    }

    protected function compareDirectories($primaryDir, $secondaryDir, $group, $primaryDirection)
    {
        $finder = new Finder();
        $finder->files()->in($primaryDir)->ignoreDotFiles(true);

        foreach ($finder as $file) {
            $relativePath = $file->getRelativePathname();
            $primaryPath = $primaryDir . DIRECTORY_SEPARATOR . $relativePath;
            $secondaryPath = $secondaryDir . DIRECTORY_SEPARATOR . $relativePath;

            $primaryHash = md5_file($primaryPath);
            $secondaryHash = File::exists($secondaryPath) ? md5_file($secondaryPath) : null;

            if ($primaryHash !== $secondaryHash) {
                $primaryTime = filemtime($primaryPath);
                $secondaryTime = File::exists($secondaryPath) ? filemtime($secondaryPath) : 0;

                $direction = $primaryTime >= $secondaryTime ? $primaryDirection : ('target_to_source' === $primaryDirection ? 'source_to_target' : 'target_to_source');

                $this->changedFiles[] = [
                    'group' => $group,
                    'type' => $this->determineChangeType($primaryHash, $secondaryHash),
                    'file' => $relativePath,
                    'source_path' => 'source_to_target' === $primaryDirection ? $primaryPath : $secondaryPath,
                    'target_path' => 'source_to_target' === $primaryDirection ? $secondaryPath : $primaryPath,
                    'direction' => $direction,
                ];
            }
        }
    }

    protected function determineChangeType($sourceHash, $targetHash)
    {
        if ($sourceHash === null) {
            return 'deleted_in_source';
        }
        if ($targetHash === null) {
            return 'new_in_source';
        }

        return 'updated';
    }

    protected function displayChangeSummary()
    {
        $this->newLine();
        $this->line('<fg=yellow;options=bold>📊 CHANGE SUMMARY</>');
        $this->line('<fg=gray>─────────────────────────────────────────────────────────</>');

        $grouped = [];
        foreach ($this->changedFiles as $idx => $change) {
            $key = $change['group'] . '_' . $change['direction'];
            if (!isset($grouped[$key])) {
                $grouped[$key] = [];
            }
            $grouped[$key][] = $idx;
        }

        $totalFiles = count($this->changedFiles);
        $groupCount = [];

        foreach ($grouped as $key => $indices) {
            [$group, $direction] = explode('_', $key, 2);
            $mapping = $this->syncMappings[$group] ?? [];
            $icon = $mapping['icon'] ?? '📄';
            $count = count($indices);

            if (!isset($groupCount[$group])) {
                $groupCount[$group] = 0;
            }
            $groupCount[$group] += $count;

            $directionLabel = 'source_to_target' === $direction ? '📦 → 📱' : '📱 → 📦';
            $this->line("  {$icon}  <fg=cyan>" . ucfirst($group) . "</> {$directionLabel} <fg=green>({$count} files)</>");
        }

        $this->newLine();
        $this->line("<fg=green;options=bold>Total Changes: {$totalFiles} files</>");
        $this->newLine();
    }

    protected function interactiveSyncProcess()
    {
        if ($this->option('force')) {
            return $this->performSync($this->changedFiles);
        }

        $this->newLine();
        $this->line('<fg=yellow;options=bold>📋 DETAILED FILE LIST</>');
        $this->line('<fg=gray>─────────────────────────────────────────────────────────</>');

        $grouped = [];
        foreach ($this->changedFiles as $idx => $change) {
            $key = $change['direction'];
            if (!isset($grouped[$key])) {
                $grouped[$key] = [];
            }
            $grouped[$key][$idx] = $change;
        }

        $fileIndex = 0;
        $directionLabels = [
            'source_to_target' => '📦 → 📱 (Package → App)',
            'target_to_source' => '📱 → 📦 (App → Package)',
        ];

        foreach ($grouped as $direction => $files) {
            $this->newLine();
            $this->line("<fg=blue;options=bold>{$directionLabels[$direction]}</>");
            $this->line('<fg=gray>' . str_repeat('─', 60) . '</>');

            foreach ($files as $idx => $change) {
                $mapping = $this->syncMappings[$change['group']] ?? [];
                $icon = $mapping['icon'] ?? '📄';
                $typeLabel = $this->getTypeLabel($change['type']);

                $this->line("  [<fg=yellow>{$fileIndex}</></>] {$icon} <fg=gray>{$change['file']}</> <fg=cyan>({$change['group']})</>");
                $this->line("      └─ <fg=green>{$typeLabel}</>");
                $fileIndex++;
            }
        }

        $this->newLine();
        $this->line('<fg=cyan>Enter file numbers to sync (comma-separated, or "all" for all files, "none" to cancel):</>');
        $this->line('<fg=gray>Example: 0,1,3 or all</>');

        $input = $this->ask('Your selection');

        if (strtolower($input) === 'none' || strtolower($input) === 'cancel') {
            $this->info('❌ Sync cancelled.');
            return 0;
        }

        if (strtolower($input) === 'all') {
            return $this->performSync($this->changedFiles);
        }

        $numbers = array_map('trim', explode(',', $input));
        $selectedIndices = [];

        foreach ($numbers as $num) {
            if (is_numeric($num) && isset($this->changedFiles[$num])) {
                $selectedIndices[] = $this->changedFiles[$num];
            }
        }

        if (empty($selectedIndices)) {
            $this->error('❌ No valid files selected.');
            return 1;
        }

        return $this->performSync($selectedIndices);
    }

    protected function performSync($files)
    {
        $this->newLine();
        $this->line('<fg=cyan;options=bold>⏳ Syncing files...</>');
        $this->line('<fg=gray>' . str_repeat('─', 60) . '</>');

        $successful = 0;
        $failed = 0;

        foreach ($files as $change) {
            $source = $change['direction'] === 'source_to_target'
                ? $change['source_path']
                : $change['target_path'];
            $target = $change['direction'] === 'source_to_target'
                ? $change['target_path']
                : $change['source_path'];

            $direction = $change['direction'] === 'source_to_target' ? '📦 → 📱' : '📱 → 📦';

            if ($change['type'] === 'deleted_in_source') {
                if (File::exists($target)) {
                    File::delete($target);
                    $this->line("  ✓ <fg=green>Deleted:</> {$change['file']} {$direction}");
                    $successful++;
                } else {
                    $this->line("  ⚠ <fg=yellow>Skipped:</> {$change['file']} (already deleted)");
                }
            } else {
                if (!File::exists($source)) {
                    $this->line("  ✗ <fg=red>Failed:</> {$change['file']} (source not found)");
                    $failed++;
                    continue;
                }

                try {
                    File::ensureDirectoryExists(dirname($target));

                    if (is_dir($source)) {
                        File::ensureDirectoryExists($target);
                        File::copyDirectory($source, $target);
                    } else {
                        File::copy($source, $target);
                    }

                    $this->line("  ✓ <fg=green>Synced:</> {$change['file']} {$direction}");
                    $successful++;
                } catch (\Exception $e) {
                    $this->line("  ✗ <fg=red>Failed:</> {$change['file']} ({$e->getMessage()})");
                    $failed++;
                }
            }
        }

        $this->newLine();
        $this->line('<fg=gray>' . str_repeat('─', 60) . '</>');
        $this->info("✅ Sync complete! Success: <fg=green>{$successful}</>, Failed: <fg=red>{$failed}</>");
        $this->newLine();

        return $failed === 0 ? 0 : 1;
    }

    protected function getTypeLabel($type)
    {
        $labels = [
            'updated' => '🔄 Updated',
            'new_in_source' => '✨ New',
            'deleted_in_source' => '🗑️  Deleted',
        ];

        return $labels[$type] ?? ucfirst(str_replace('_', ' ', $type));
    }
}
