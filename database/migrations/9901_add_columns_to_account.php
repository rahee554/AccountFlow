<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Historical no-op.
 *
 * This migration never added any columns. Its `down()` called
 * `$table->dropColumn('')`, which throws, so any rollback that reached it
 * failed. It is kept (rather than deleted) because installs from 0.2.x already
 * have it recorded in the migrations table; removing the file would not undo
 * that, and renaming it would make it run again.
 *
 * New schema changes go in a fresh, timestamped migration so they run on
 * existing installs too.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Intentionally empty.
    }

    public function down(): void
    {
        // Intentionally empty.
    }
};
