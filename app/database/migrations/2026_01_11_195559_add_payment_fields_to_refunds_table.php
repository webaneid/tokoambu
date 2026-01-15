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
        Schema::table('refunds', function (Blueprint $table) {
            $table->enum('payment_method', ['cash', 'debit', 'credit_card', 'transfer', 'qris'])->nullable()->after('amount');
            $table->foreignId('customer_bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete()->after('payment_method');
            $table->foreignId('shop_bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete()->after('customer_bank_account_id');
            $table->foreignId('payment_media_id')->nullable()->constrained('media')->nullOnDelete()->after('shop_bank_account_id');
            $table->decimal('transfer_fee', 15, 2)->nullable()->after('payment_media_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->dropForeign(['customer_bank_account_id']);
            $table->dropForeign(['shop_bank_account_id']);
            $table->dropForeign(['payment_media_id']);
            $table->dropColumn(['payment_method', 'customer_bank_account_id', 'shop_bank_account_id', 'payment_media_id', 'transfer_fee']);
        });
    }
};
