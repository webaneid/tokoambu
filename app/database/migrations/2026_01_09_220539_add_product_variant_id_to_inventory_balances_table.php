<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inventory_balances', function (Blueprint $table) {
            // Drop foreign keys first so we can remove the old unique index safely.
            $table->dropForeign(['product_id']);
            $table->dropForeign(['location_id']);

            // Drop existing unique constraint
            $table->dropUnique(['product_id', 'location_id']);

            // Add product_variant_id column
            $table->foreignId('product_variant_id')->nullable()->after('product_id')
                ->constrained('product_variants')->cascadeOnDelete();

            // Add new composite unique constraint including variant
            $table->unique(['product_id', 'product_variant_id', 'location_id'], 'unique_product_variant_location');

            // Restore foreign keys
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('location_id')->references('id')->on('locations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_balances', function (Blueprint $table) {
            // Drop new unique constraint
            $table->dropUnique('unique_product_variant_location');

            // Drop product_variant_id column
            $table->dropForeign(['product_variant_id']);
            $table->dropColumn('product_variant_id');

            // Restore original unique constraint
            $table->unique(['product_id', 'location_id']);
        });
    }
};
