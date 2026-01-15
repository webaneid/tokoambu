<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('sender_name')->nullable()->after('method');
            $table->string('sender_bank')->nullable()->after('sender_name');
            $table->foreignId('shop_bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete()->after('sender_bank');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['shop_bank_account_id']);
            $table->dropColumn(['sender_name', 'sender_bank', 'shop_bank_account_id']);
        });
    }
};
