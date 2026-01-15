<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bundles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->unique()->constrained('promotions')->cascadeOnDelete();
            $table->enum('pricing_mode', ['fixed', 'percent_off', 'amount_off']);
            $table->decimal('bundle_price', 12, 2)->nullable();
            $table->decimal('discount_value', 12, 2)->nullable();
            $table->boolean('must_be_cheaper')->default(true);
            $table->enum('compare_basis', ['sum_items'])->default('sum_items');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bundles');
    }
};
