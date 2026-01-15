<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('location_id')->constrained('locations');
            $table->date('last_in_date')->nullable();
            $table->date('last_out_date')->nullable();
            $table->integer('dead_stock_days')->default(0);
            $table->string('status', 20)->default('active'); // active, slow_moving, dead_stock
            $table->timestamps();

            $table->unique(['product_id', 'location_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_analytics');
    }
};
