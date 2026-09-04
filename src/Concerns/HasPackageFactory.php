<?php

namespace ArtflowStudio\AccountFlow\Concerns;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Resolves factories from the package namespace.
 *
 * Laravel's default guess is `Database\Factories\{Model}Factory`, which lives
 * in the host application. A package's factories are not there, so the lookup
 * has to be pointed at `ArtflowStudio\AccountFlow\Database\Factories`.
 */
trait HasPackageFactory
{
    use HasFactory;

    protected static function newFactory(): ?Factory
    {
        $factory = Factory::resolveFactoryName(static::class);

        if (class_exists($factory)) {
            return $factory::new();
        }

        $packageFactory = 'ArtflowStudio\\AccountFlow\\Database\\Factories\\'
            .class_basename(static::class)
            .'Factory';

        return class_exists($packageFactory) ? $packageFactory::new() : null;
    }
}
