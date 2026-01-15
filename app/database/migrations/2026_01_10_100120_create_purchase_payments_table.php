<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending')->index();
            $table->enum('method', ['cash', 'debit', 'credit_card', 'transfer', 'qris'])->default('transfer');
            $table->timestamp('paid_at')->nullable()->index();
            $table->foreignId('supplier_bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->foreignId('payer_bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('ledger_entry_id')->nullable()->constrained('ledger_entries')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_payments');
    }
};
