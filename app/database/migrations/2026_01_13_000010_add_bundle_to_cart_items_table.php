<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique('unique_cart_product_variant');

            $table->foreignId('bundle_id')
                ->nullable()
                ->after('product_variant_id')
                ->constrained('bundles')
                ->cascadeOnDelete();

            $table->unique(
                ['cart_id', 'product_id', 'product_variant_id', 'bundle_id'],
                'unique_cart_item_line'
            );
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique('unique_cart_item_line');

            $table->dropConstrainedForeignId('bundle_id');

            $table->unique(['cart_id', 'product_id', 'product_variant_id'], 'unique_cart_product_variant');
        });
    }
};
