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
        Schema::table('settings', function (Blueprint $table) {
            $table->unsignedBigInteger('logo_media_id')->nullable()->after('store_address');
            $table->unsignedBigInteger('favicon_media_id')->nullable()->after('logo_media_id');

            $table->foreign('logo_media_id')->references('id')->on('media')->onDelete('set null');
            $table->foreign('favicon_media_id')->references('id')->on('media')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropForeign(['logo_media_id']);
            $table->dropForeign(['favicon_media_id']);
            $table->dropColumn(['logo_media_id', 'favicon_media_id']);
        });
    }
};
