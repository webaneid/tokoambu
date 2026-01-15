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
        Schema::table('cart_items', function (Blueprint $table) {
            // Drop the old unique constraint
            $table->dropUnique('unique_cart_product');

            // Add product_variant_id column (nullable for simple products)
            $table->unsignedBigInteger('product_variant_id')->nullable()->after('product_id');

            // Add foreign key
            $table->foreign('product_variant_id')
                  ->references('id')
                  ->on('product_variants')
                  ->onDelete('cascade');

            // New unique constraint: cart can have same product with different variants
            $table->unique(['cart_id', 'product_id', 'product_variant_id'], 'unique_cart_product_variant');

            // Index for variant lookups
            $table->index('product_variant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            // Drop new constraint
            $table->dropUnique('unique_cart_product_variant');

            // Drop foreign key and column
            $table->dropForeign(['product_variant_id']);
            $table->dropColumn('product_variant_id');

            // Restore old unique constraint
            $table->unique(['cart_id', 'product_id'], 'unique_cart_product');
        });
    }
};
