<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->json('tracking_payload')->nullable()->after('tracking_media_id');
            $table->string('tracking_status')->nullable()->after('tracking_payload');
            $table->timestamp('tracked_at')->nullable()->after('tracking_status');
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn(['tracking_payload', 'tracking_status', 'tracked_at']);
        });
    }
};
