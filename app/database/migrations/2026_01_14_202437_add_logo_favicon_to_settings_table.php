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
            if (!Schema::hasColumn('settings', 'logo_media_id')) {
                if (Schema::hasColumn('settings', 'store_address')) {
                    $table->unsignedBigInteger('logo_media_id')->nullable()->after('store_address');
                } else {
                    $table->unsignedBigInteger('logo_media_id')->nullable();
                }
            }

            if (!Schema::hasColumn('settings', 'favicon_media_id')) {
                $table->unsignedBigInteger('favicon_media_id')->nullable()->after('logo_media_id');
            }
        });

        if (Schema::hasTable('media')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->foreign('logo_media_id')->references('id')->on('media')->onDelete('set null');
                $table->foreign('favicon_media_id')->references('id')->on('media')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'logo_media_id')) {
                $table->dropForeign(['logo_media_id']);
            }
            if (Schema::hasColumn('settings', 'favicon_media_id')) {
                $table->dropForeign(['favicon_media_id']);
            }
        });

        Schema::table('settings', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('settings', 'logo_media_id')) {
                $columns[] = 'logo_media_id';
            }
            if (Schema::hasColumn('settings', 'favicon_media_id')) {
                $columns[] = 'favicon_media_id';
            }
            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
