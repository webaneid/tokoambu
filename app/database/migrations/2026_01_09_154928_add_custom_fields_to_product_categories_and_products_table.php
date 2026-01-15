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
        Schema::table('product_categories', function (Blueprint $table) {
            $table->json('custom_fields')->nullable()->after('description');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->json('custom_field_values')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropColumn('custom_fields');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('custom_field_values');
        });
    }
};
