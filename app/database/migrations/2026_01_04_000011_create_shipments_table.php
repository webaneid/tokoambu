<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('recipient_name')->nullable();
            $table->text('recipient_address')->nullable();
            $table->string('courier')->nullable();
            $table->string('tracking_number')->nullable();
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->enum('status', ['pending', 'packed', 'shipped', 'delivered'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
