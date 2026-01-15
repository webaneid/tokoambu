<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('financial_categories')->delete();
        DB::table('financial_categories')->insert([
            // Income categories
            ['name' => 'Order', 'type' => 'income', 'is_active' => true, 'is_default' => true, 'created_at' => $now, 'updated_at' => $now],

            // Expense categories
            ['name' => 'Pembelian Produk', 'type' => 'expense', 'is_active' => true, 'is_default' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Ongkos Kirim', 'type' => 'expense', 'is_active' => true, 'is_default' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Biaya Transfer', 'type' => 'expense', 'is_active' => true, 'is_default' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Refund', 'type' => 'expense', 'is_active' => true, 'is_default' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        // no-op rollback
    }
};
