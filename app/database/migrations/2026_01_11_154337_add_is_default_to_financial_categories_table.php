<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('financial_categories', function (Blueprint $table) {
            $table->boolean('is_default')->default(false)->after('is_active');
        });

        // Mark existing default categories as is_default = true
        DB::table('financial_categories')
            ->whereIn('name', ['Order', 'Pembelian Produk', 'Ongkos Kirim', 'Biaya Transfer', 'Refund'])
            ->update(['is_default' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_categories', function (Blueprint $table) {
            $table->dropColumn('is_default');
        });
    }
};
