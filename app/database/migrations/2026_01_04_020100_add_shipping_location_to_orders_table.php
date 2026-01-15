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
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedInteger('shipping_province_id')->nullable()->after('status');
            $table->unsignedInteger('shipping_city_id')->nullable()->after('shipping_province_id');
            $table->unsignedInteger('shipping_district_id')->nullable()->after('shipping_city_id');
            $table->string('shipping_postal_code', 10)->nullable()->after('shipping_district_id');
            $table->text('shipping_address')->nullable()->after('shipping_postal_code');
            
            $table->foreign('shipping_province_id')->references('id')->on('provinces')->onDelete('set null');
            $table->foreign('shipping_city_id')->references('id')->on('cities')->onDelete('set null');
            $table->foreign('shipping_district_id')->references('id')->on('districts')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['shipping_province_id']);
            $table->dropForeign(['shipping_city_id']);
            $table->dropForeign(['shipping_district_id']);
            $table->dropColumn(['shipping_province_id', 'shipping_city_id', 'shipping_district_id', 'shipping_postal_code', 'shipping_address']);
        });
    }
};
