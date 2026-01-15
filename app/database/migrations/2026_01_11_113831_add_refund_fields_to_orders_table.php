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
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('refund_amount', 15, 2)->nullable()->after('paid_amount');
            $table->string('refund_method')->nullable()->after('refund_amount');
            $table->text('refund_notes')->nullable()->after('refund_method');
            $table->timestamp('refunded_at')->nullable()->after('refund_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['refund_amount', 'refund_method', 'refund_notes', 'refunded_at']);
        });
    }
};
