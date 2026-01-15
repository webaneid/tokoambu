<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop FK to financial_categories if it exists (MySQL-safe).
        if (Schema::hasColumn('ledger_entries', 'category_id')) {
            $constraint = DB::selectOne(
                "SELECT CONSTRAINT_NAME
                 FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'ledger_entries'
                   AND COLUMN_NAME = 'category_id'
                   AND REFERENCED_TABLE_NAME IS NOT NULL
                 LIMIT 1"
            );

            if ($constraint && isset($constraint->CONSTRAINT_NAME)) {
                DB::statement("ALTER TABLE `ledger_entries` DROP FOREIGN KEY `{$constraint->CONSTRAINT_NAME}`");
            }
        }

        Schema::dropIfExists('financial_categories');
    }

    public function down(): void
    {
        // No-op: financial_categories intentionally removed
    }
};
