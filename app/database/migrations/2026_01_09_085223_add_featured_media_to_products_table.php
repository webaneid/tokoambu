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
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('featured_media_id')->nullable();
            $table->foreign('featured_media_id')->references('id')->on('media')->onDelete('set null');
        });

        // Add gallery_order field to media table for drag & drop ordering
        Schema::table('media', function (Blueprint $table) {
            $table->integer('gallery_order')->default(0)->after('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['featured_media_id']);
            $table->dropColumn('featured_media_id');
        });

        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn('gallery_order');
        });
    }
};
