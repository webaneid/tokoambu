<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->unique()->constrained('promotions')->cascadeOnDelete();
            $table->string('code')->unique();
            $table->integer('per_user_limit')->nullable();
            $table->integer('global_limit')->nullable();
            $table->decimal('min_order_amount', 12, 2)->nullable();
            $table->boolean('first_purchase_only')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
