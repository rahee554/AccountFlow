<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Makes transfers actually move money.
 *
 * A transfer used to write one row to `ac_transfers` and nothing else. Account
 * balances are derived from `ac_transactions`, so the money never moved — and
 * `recalculateAll()`, which rebuilds balances from the ledger, could not see
 * the transfer at all. Creating a transfer had no effect on any balance.
 *
 * Transfers are now posted as a linked pair of ledger entries: an expense on
 * the source account and an income on the destination. `transfer_id` ties each
 * entry back to its transfer, which is also what lets profit & loss exclude
 * them — a transfer is not revenue or a cost, it is cash changing hands.
 *
 * Additive and guarded: nothing is dropped, and every column is added only
 * when missing. Named 99xx so it sorts after `9900_create_accounts_tables`
 * (Laravel orders migrations by filename).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ac_transactions')) {
            Schema::table('ac_transactions', function (Blueprint $table) {
                if (! Schema::hasColumn('ac_transactions', 'transfer_id')) {
                    $table->unsignedBigInteger('transfer_id')
                        ->nullable()
                        ->comment('The transfer this entry is one leg of');
                }
            });

            $this->addIndex('ac_transactions', ['transfer_id'], 'ac_transactions_transfer_idx');
        }

        if (Schema::hasTable('ac_transfers')) {
            Schema::table('ac_transfers', function (Blueprint $table) {
                if (! Schema::hasColumn('ac_transfers', 'from_trx_id')) {
                    $table->unsignedBigInteger('from_trx_id')
                        ->nullable()
                        ->comment('Ledger entry debiting the source account');
                }

                if (! Schema::hasColumn('ac_transfers', 'to_trx_id')) {
                    $table->unsignedBigInteger('to_trx_id')
                        ->nullable()
                        ->comment('Ledger entry crediting the destination account');
                }
            });

            // `created_by` was NOT NULL, which meant only an authenticated
            // request could record a transfer — the scheduler, an importer or
            // an artisan command had no user and hit a constraint violation.
            // Widening a column to nullable cannot lose data.
            Schema::table('ac_transfers', function (Blueprint $table) {
                $table->unsignedBigInteger('created_by')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // The columns are deliberately not dropped: they carry the only link
        // between a transfer and its ledger entries. Removing them would orphan
        // the postings and silently change every balance.
        $this->dropIndex('ac_transactions', 'ac_transactions_transfer_idx');
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
            return false;
        }
    }
};
