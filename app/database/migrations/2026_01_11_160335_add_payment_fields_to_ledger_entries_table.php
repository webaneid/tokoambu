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
        Schema::table('ledger_entries', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('amount');
            $table->foreignId('payee_bank_account_id')->nullable()->after('payment_method')->constrained('bank_accounts')->nullOnDelete();
            $table->foreignId('payer_bank_account_id')->nullable()->after('payee_bank_account_id')->constrained('bank_accounts')->nullOnDelete();
            $table->foreignId('payment_media_id')->nullable()->after('payer_bank_account_id')->constrained('media')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ledger_entries', function (Blueprint $table) {
            $table->dropForeign(['payee_bank_account_id']);
            $table->dropForeign(['payer_bank_account_id']);
            $table->dropForeign(['payment_media_id']);
            $table->dropColumn(['payment_method', 'payee_bank_account_id', 'payer_bank_account_id', 'payment_media_id']);
        });
    }
};
