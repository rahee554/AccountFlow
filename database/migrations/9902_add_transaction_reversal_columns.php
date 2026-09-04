<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Named 9902 rather than with a timestamp on purpose.
 *
 * Laravel orders migrations by filename, and this package's original migration
 * is called `9900_create_accounts_tables`. A conventional `2026_..._` name
 * sorts BEFORE `9900`, so on a fresh install it would run first, find no
 * tables, skip every guard, and leave the new columns missing. Later
 * migrations must keep the `99xx` prefix for as long as `9900` exists.
 *
 * Additive only. Every column and index is added just when missing, and
 * nothing is renamed, moved or dropped — existing rows are untouched.
 *
 * Adds:
 *   - reversal linkage on ac_transactions, so a reversal is a contra entry
 *     pointing at the row it reverses instead of an opposite-type transaction
 *     that pollutes profit & loss
 *   - the indexes the transaction list and reports actually filter on
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ac_transactions')) {
            Schema::table('ac_transactions', function (Blueprint $table) {
                if (! Schema::hasColumn('ac_transactions', 'reversal_of_id')) {
                    $table->unsignedBigInteger('reversal_of_id')
                        ->nullable()
                        ->after('id')
                        ->comment('The transaction this entry reverses');
                }

                if (! Schema::hasColumn('ac_transactions', 'reversed_at')) {
                    $table->timestamp('reversed_at')
                        ->nullable()
                        ->comment('Set when this transaction has been reversed');
                }
            });

            $this->addIndex('ac_transactions', ['account_id', 'date'], 'ac_transactions_account_date_idx');
            $this->addIndex('ac_transactions', ['type', 'date'], 'ac_transactions_type_date_idx');
            $this->addIndex('ac_transactions', ['category_id'], 'ac_transactions_category_idx');
        }

        if (Schema::hasTable('ac_audit_trail')) {
            $this->addIndex('ac_audit_trail', ['model_type', 'model_id'], 'ac_audit_trail_model_idx');
        }
    }

    public function down(): void
    {
        // Deliberately does not drop the columns: they may hold reversal
        // linkage for real transactions. Drop them by hand if you truly
        // intend to lose that history.
        $this->dropIndex('ac_transactions', 'ac_transactions_account_date_idx');
        $this->dropIndex('ac_transactions', 'ac_transactions_type_date_idx');
        $this->dropIndex('ac_transactions', 'ac_transactions_category_idx');
        $this->dropIndex('ac_audit_trail', 'ac_audit_trail_model_idx');
    }

    /**
     * @param list<string> $columns
     */
    private function addIndex(string $table, array $columns, string $name): void
    {
        foreach ($columns as $column) {
            if (! Schema::hasColumn($table, $column)) {
                return;
            }
        }

        if ($this->hasIndex($table, $name)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($columns, $name) {
            $blueprint->index($columns, $name);
        });
    }

    private function dropIndex(string $table, string $name): void
    {
        if (! Schema::hasTable($table) || ! $this->hasIndex($table, $name)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($name) {
            $blueprint->dropIndex($name);
        });
    }

    private function hasIndex(string $table, string $name): bool
    {
        try {
            return Schema::hasIndex($table, $name);
        } catch (Throwable) {
            // Older/alternative drivers may not support introspection; assume
            // absent and let the driver reject a genuine duplicate.
            return false;
        }
    }
};
