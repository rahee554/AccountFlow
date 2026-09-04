<?php

namespace ArtflowStudio\AccountFlow\Tests;

use ArtflowStudio\AccountFlow\AccountFlowServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            AccountFlowServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        // SQLite in memory. This only became possible once the MySQL-only SQL
        // (double-quoted string literals, DATE_FORMAT) was replaced with
        // driver-portable expressions.
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        // Authorization has dedicated tests; the rest of the suite runs without it.
        $app['config']->set('accountflow.authorization.enabled', false);
    }

    protected function defineDatabaseMigrations(): void
    {
        // Several AccountFlow tables carry a foreign key to `users`, so a
        // minimal version has to exist. Declared here rather than pulled from
        // Laravel's stub migrations, so the suite does not depend on the host
        // application's user schema.
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->timestamps();
        });

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
