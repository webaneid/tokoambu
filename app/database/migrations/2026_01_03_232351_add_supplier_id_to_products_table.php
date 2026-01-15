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
        // Column sudah ditambah di create_products_table migration
        // Migration ini bisa di-skip atau kosongkan
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kolom sudah dihapus oleh create_products_table down() method
    }
};
