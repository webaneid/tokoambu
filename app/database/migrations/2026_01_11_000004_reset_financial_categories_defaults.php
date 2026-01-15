<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('financial_categories')->delete();

        $baseRows = [
            ['name' => 'Order', 'type' => 'income', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Pembelian Produk', 'type' => 'expense', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Ongkos Kirim', 'type' => 'expense', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Biaya Transfer', 'type' => 'expense', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Refund', 'type' => 'expense', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ];

        if (Schema::hasColumn('financial_categories', 'is_default')) {
            $baseRows = array_map(function ($row) {
                $row['is_default'] = true;
                return $row;
            }, $baseRows);
        }

        DB::table('financial_categories')->insert($baseRows);
    }

    public function down(): void
    {
        // no-op rollback
    }
};
