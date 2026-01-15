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
        Schema::create('footer_menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('label'); // Menu label (e.g., "Tentang Kami")
            $table->string('type')->default('page'); // 'page' or 'custom_url'
            $table->unsignedBigInteger('page_id')->nullable(); // FK to pages table
            $table->string('custom_url')->nullable(); // For custom URLs
            $table->integer('order')->default(0); // Display order
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('page_id')->references('id')->on('pages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('footer_menu_items');
    }
};
