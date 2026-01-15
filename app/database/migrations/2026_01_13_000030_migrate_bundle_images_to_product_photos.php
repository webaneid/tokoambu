<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('media')
            ->where('type', 'bundle_image')
            ->update(['type' => 'product_photo']);
    }

    public function down(): void
    {
        DB::table('media')
            ->where('type', 'product_photo')
            ->where('path', 'like', '%bundle_image%')
            ->update(['type' => 'bundle_image']);
    }
};
