<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('media')) {
            return;
        }

        DB::statement("ALTER TABLE `media` MODIFY `type` VARCHAR(50) NOT NULL");
    }

    public function down(): void
    {
        if (!Schema::hasTable('media')) {
            return;
        }

        DB::statement("ALTER TABLE `media` MODIFY `type` ENUM('payment_proof','product_photo','shipment_proof','banner_image') NOT NULL");
    }
};
