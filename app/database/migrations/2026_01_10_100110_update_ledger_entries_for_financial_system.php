<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ledger_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('ledger_entries', 'entry_date')) {
                $table->date('entry_date')->nullable()->after('id');
            }
            if (!Schema::hasColumn('ledger_entries', 'category_id')) {
                $table->unsignedBigInteger('category_id')->nullable()->after('type')->index();
            }
            if (!Schema::hasColumn('ledger_entries', 'source_type')) {
                $table->string('source_type')->nullable()->after('reference_type')->index();
            }
            if (!Schema::hasColumn('ledger_entries', 'source_id')) {
                $table->unsignedBigInteger('source_id')->nullable()->after('source_type')->index();
            }
            if (!Schema::hasColumn('ledger_entries', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();
            }
        });

        DB::table('ledger_entries')->whereNull('entry_date')->update([
            'entry_date' => DB::raw('DATE(created_at)'),
        ]);

        // Drop legacy enum category if present (handle sqlite index first)
        if (Schema::hasColumn('ledger_entries', 'category')) {
            if (Schema::getConnection()->getDriverName() === 'sqlite') {
                DB::statement('DROP INDEX IF EXISTS ledger_entries_category_index');
            }

            Schema::table('ledger_entries', function (Blueprint $table) {
                $table->dropColumn('category');
            });
        }
    }

    public function down(): void
    {
        Schema::table('ledger_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('ledger_entries', 'category')) {
                $table->enum('category', [
                    'order_payment',
                    'purchase_expense',
                    'operational',
                    'other'
                ])->default('operational')->index();
            }

            $columnsToDrop = collect(['category_id', 'entry_date', 'source_type', 'source_id', 'created_by'])
                ->filter(fn ($col) => Schema::hasColumn('ledger_entries', $col))
                ->all();

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
