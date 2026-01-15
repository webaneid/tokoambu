<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('shipments', 'shipment_number')) {
            Schema::table('shipments', function (Blueprint $table) {
                $table->string('shipment_number')->nullable()->after('order_id');
            });
        }

        $duplicates = DB::table('shipments')
            ->select('order_id', DB::raw('COUNT(*) as c'))
            ->groupBy('order_id')
            ->having('c', '>', 1)
            ->get();

        foreach ($duplicates as $dup) {
            $rows = DB::table('shipments')
                ->where('order_id', $dup->order_id)
                ->orderByRaw("CASE status WHEN 'delivered' THEN 3 WHEN 'shipped' THEN 2 WHEN 'packed' THEN 1 ELSE 0 END DESC")
                ->orderByDesc('updated_at')
                ->get(['id']);

            $keepId = $rows->first()?->id;
            if (!$keepId) {
                continue;
            }

            $deleteIds = $rows->pluck('id')->filter(fn($id) => $id !== $keepId);
            if ($deleteIds->isNotEmpty()) {
                DB::table('shipments')->whereIn('id', $deleteIds->all())->delete();
            }
        }

        $missingNumbers = DB::table('shipments')
            ->whereNull('shipment_number')
            ->get(['id', 'created_at']);

        foreach ($missingNumbers as $row) {
            $date = $row->created_at ? date('Ymd', strtotime($row->created_at)) : date('Ymd');
            DB::table('shipments')
                ->where('id', $row->id)
                ->update(['shipment_number' => 'SHP-' . $date . '-' . $row->id]);
        }

        Schema::table('shipments', function (Blueprint $table) {
            $table->unique('shipment_number');
            $table->unique('order_id');
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropUnique(['shipment_number']);
            $table->dropUnique(['order_id']);
            $table->dropColumn('shipment_number');
        });
    }
};
