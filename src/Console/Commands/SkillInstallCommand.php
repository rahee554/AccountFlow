<?php

namespace ArtflowStudio\AccountFlow\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SkillInstallCommand extends Command
{
    protected $signature = 'accountflow:skill-install';

    protected $description = 'Install the AccountFlow AI agent skill to .github/skills/accountflow-development/SKILL.md';

    public function handle(): int
    {
        $source = realpath(__DIR__.'/../../..').DIRECTORY_SEPARATOR.'SKILL.md';
        $destination = base_path('.github'.DIRECTORY_SEPARATOR.'skills'.DIRECTORY_SEPARATOR.'accountflow-development'.DIRECTORY_SEPARATOR.'SKILL.md');

        if (! File::exists($source)) {
            $this->error('SKILL.md not found in the AccountFlow package root.');
            $this->line("  Expected at: <comment>{$source}</comment>");

            return self::FAILURE;
        }

        $directory = dirname($destination);

        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
            $this->line("  <info>Created directory:</info> {$directory}");
        }

        File::copy($source, $destination);

        $this->info('AccountFlow skill installed successfully.');
        $this->line('');
        $this->line('  <options=bold>From:</> '.$source);
        $this->line('  <options=bold>To:</>   '.$destination);
        $this->line('');
        $this->line('  The skill is now available to VS Code GitHub Copilot as a context skill.');

        return self::SUCCESS;
    }
}
