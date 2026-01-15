<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('warehouses') || ! Schema::hasTable('locations')) {
            return;
        }

        // Tambahkan lokasi default MAIN untuk setiap gudang yang belum punya lokasi
        $warehouses = DB::table('warehouses')->get(['id']);
        foreach ($warehouses as $warehouse) {
            $exists = DB::table('locations')
                ->where('warehouse_id', $warehouse->id)
                ->exists();

            if (! $exists) {
                DB::table('locations')->insert([
                    'warehouse_id' => $warehouse->id,
                    'code' => 'MAIN',
                    'description' => 'Default location',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('warehouses') || ! Schema::hasTable('locations')) {
            return;
        }

        // Hapus lokasi MAIN yang kita buat (jika tidak ada lokasi lain di gudang tsb)
        $warehouses = DB::table('warehouses')->get(['id']);
        foreach ($warehouses as $warehouse) {
            $locations = DB::table('locations')->where('warehouse_id', $warehouse->id)->get();
            if ($locations->count() === 1 && ($locations->first()->code ?? null) === 'MAIN') {
                DB::table('locations')->where('id', $locations->first()->id)->delete();
            }
        }
    }
};
