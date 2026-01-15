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
        // Add reserved_qty to inventory_balances
        Schema::table('inventory_balances', function (Blueprint $table) {
            $table->decimal('reserved_qty', 10, 2)->default(0)->after('qty');
        });

        // Add preorder payment fields to orders
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('dp_amount', 15, 2)->nullable()->after('total_amount');
            $table->timestamp('dp_paid_at')->nullable()->after('dp_amount');
            $table->datetime('dp_payment_deadline')->nullable()->after('dp_paid_at');
            $table->datetime('final_payment_deadline')->nullable()->after('dp_payment_deadline');
        });

        // Update order status enum to include new preorder statuses
        // Note: We need to modify the status column to include: waiting_dp, dp_paid, product_ready
        Schema::table('orders', function (Blueprint $table) {
            // Drop the existing enum constraint and recreate with new values
            $table->string('status', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_balances', function (Blueprint $table) {
            $table->dropColumn('reserved_qty');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'dp_amount',
                'dp_paid_at',
                'dp_payment_deadline',
                'final_payment_deadline',
            ]);
        });

        // Revert status column back to original enum if needed
        Schema::table('orders', function (Blueprint $table) {
            $table->string('status', 50)->change();
        });
    }
};
