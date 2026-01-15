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
        Schema::create('preorder_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('name'); // e.g., "Periode 1", "Batch Januari 2026"
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'closed', 'archived'])->default('active');
            $table->integer('target_quantity')->nullable(); // Optional: target qty for this period
            $table->timestamps();

            $table->index(['product_id', 'status']);
        });

        // Add preorder_period_id to orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('preorder_period_id')->nullable()->after('type')->constrained('preorder_periods')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['preorder_period_id']);
            $table->dropColumn('preorder_period_id');
        });

        Schema::dropIfExists('preorder_periods');
    }
};
