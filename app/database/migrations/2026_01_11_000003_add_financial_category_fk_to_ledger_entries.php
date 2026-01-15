<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ledger_entries') && Schema::hasTable('financial_categories')) {
            Schema::table('ledger_entries', function (Blueprint $table) {
                // Drop existing foreign key if wrong target, ignore errors
                try {
                    $table->dropForeign(['category_id']);
                } catch (\Throwable $e) {
                    // ignore
                }

                try {
                    $table->foreign('category_id')
                        ->references('id')
                        ->on('financial_categories')
                        ->nullOnDelete();
                } catch (\Throwable $e) {
                    // ignore if already exists
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ledger_entries')) {
            Schema::table('ledger_entries', function (Blueprint $table) {
                try {
                    $table->dropForeign(['category_id']);
                } catch (\Throwable $e) {
                    // ignore
                }
            });
        }
    }
};
