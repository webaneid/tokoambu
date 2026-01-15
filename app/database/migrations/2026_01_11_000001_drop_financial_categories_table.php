<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop FK to financial_categories if it exists
        Schema::table('ledger_entries', function (Blueprint $table) {
            if (Schema::hasColumn('ledger_entries', 'category_id')) {
                try {
                    $table->dropForeign(['category_id']);
                } catch (\Throwable $e) {
                    // ignore if constraint not present
                }
            }
        });

        Schema::dropIfExists('financial_categories');
    }

    public function down(): void
    {
        // No-op: financial_categories intentionally removed
    }
};
