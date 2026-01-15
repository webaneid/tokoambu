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
        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedInteger('province_id')->nullable()->after('email');
            $table->unsignedInteger('city_id')->nullable()->after('province_id');
            $table->unsignedInteger('district_id')->nullable()->after('city_id');
            $table->string('postal_code', 10)->nullable()->after('district_id');
            $table->text('full_address')->nullable()->after('postal_code');
            
            $table->foreign('province_id')->references('id')->on('provinces')->onDelete('set null');
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('set null');
            $table->foreign('district_id')->references('id')->on('districts')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['province_id']);
            $table->dropForeign(['city_id']);
            $table->dropForeign(['district_id']);
            $table->dropColumn(['province_id', 'city_id', 'district_id', 'postal_code', 'full_address']);
        });
    }
};
