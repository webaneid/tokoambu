<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $categoryId = DB::table('financial_categories')
            ->where('name', 'Pembelian Produk')
            ->where('type', 'expense')
            ->value('id');

        if (!$categoryId) {
            $now = now();
            $categoryId = DB::table('financial_categories')->insertGetId([
                'name' => 'Pembelian Produk',
                'type' => 'expense',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('ledger_entries')
            ->where('type', 'expense')
            ->where('source_type', 'purchase_payment')
            ->whereNull('category_id')
            ->update(['category_id' => $categoryId]);
    }

    public function down(): void
    {
        // No rollback needed
    }
};
