<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->enum('payment_status', ['pending', 'paid'])->default('pending')->after('status');
            $table->enum('payment_method', ['cash', 'debit', 'credit_card', 'transfer', 'qris'])->nullable()->after('payment_status');
            $table->date('payment_date')->nullable()->after('payment_method');
            $table->decimal('paid_amount', 12, 2)->default(0)->after('total_amount');
            $table->foreignId('supplier_bank_account_id')->nullable()->after('paid_amount')->constrained('bank_accounts')->nullOnDelete();
            $table->foreignId('payer_bank_account_id')->nullable()->after('supplier_bank_account_id')->constrained('bank_accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['supplier_bank_account_id']);
            $table->dropForeign(['payer_bank_account_id']);
            $table->dropColumn([
                'payment_status',
                'payment_method',
                'payment_date',
                'paid_amount',
                'supplier_bank_account_id',
                'payer_bank_account_id',
            ]);
        });
    }
};
