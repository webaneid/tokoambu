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
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_user_id')->nullable();
            $table->string('session_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('customer_user_id')
                  ->references('id')
                  ->on('customer_users')
                  ->onDelete('cascade');

            // Indexes
            $table->index('customer_user_id');
            $table->index('session_id');
            $table->unique(['customer_user_id', 'deleted_at'], 'unique_active_customer_cart');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
