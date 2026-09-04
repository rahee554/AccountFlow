<?php

use Symfony\Component\Finder\Finder;

it('declares no class in the host application namespace', function () {
    $offenders = [];

    foreach (Finder::create()->files()->in(__DIR__.'/../../src')->name('*.php') as $file) {
        if (preg_match('/^namespace\s+(App\\[^;]*);/m', $file->getContents(), $m)) {
            $offenders[] = $file->getRelativePathname().' -> '.$m[1];
        }
    }

    expect($offenders)->toBe([]);
});

it('uses no MySQL-only double-quoted SQL literals', function () {
    $offenders = [];

    foreach (Finder::create()->files()->in(__DIR__.'/../../src')->name('*.php') as $file) {
        foreach (['selectRaw', 'whereRaw', 'DB::raw'] as $needle) {
            if (str_contains($file->getContents(), $needle)
                && preg_match('/(selectRaw|whereRaw|raw)\([^)]*IN \("/', $file->getContents())) {
                $offenders[] = $file->getRelativePathname();
                break;
            }
        }
    }

    expect(array_unique($offenders))->toBe([]);
});

it('registers only command classes that exist', function () {
    $reflection = new ReflectionClass(ArtflowStudio\AccountFlow\AccountFlowServiceProvider::class);

    foreach ($reflection->getConstant('COMMANDS') as $command) {
        expect(class_exists($command))->toBeTrue("{$command} is registered but does not exist");
    }
});
