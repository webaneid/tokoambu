<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->foreignId('payment_id')->nullable()->after('purchase_id')->constrained('payments')->nullOnDelete();
            $table->foreignId('purchase_payment_id')->nullable()->after('payment_id')->constrained('purchase_payments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropConstrainedForeignId('purchase_payment_id');
            $table->dropConstrainedForeignId('payment_id');
        });
    }
};
