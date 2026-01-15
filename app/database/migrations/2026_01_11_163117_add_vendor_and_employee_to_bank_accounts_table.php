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
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->foreignId('vendor_id')->nullable()->after('supplier_id')->constrained('vendors')->nullOnDelete();
            $table->foreignId('employee_id')->nullable()->after('vendor_id')->constrained('employees')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
            $table->dropForeign(['employee_id']);
            $table->dropColumn(['vendor_id', 'employee_id']);
        });
    }
};
