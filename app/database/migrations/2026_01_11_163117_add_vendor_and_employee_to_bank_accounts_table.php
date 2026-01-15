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
        if (!Schema::hasColumn('bank_accounts', 'vendor_id')) {
            Schema::table('bank_accounts', function (Blueprint $table) {
                $table->unsignedBigInteger('vendor_id')->nullable()->after('supplier_id');
            });
        }

        if (!Schema::hasColumn('bank_accounts', 'employee_id')) {
            Schema::table('bank_accounts', function (Blueprint $table) {
                $table->unsignedBigInteger('employee_id')->nullable()->after('vendor_id');
            });
        }

        if (Schema::hasTable('vendors')) {
            Schema::table('bank_accounts', function (Blueprint $table) {
                $table->foreign('vendor_id')->references('id')->on('vendors')->nullOnDelete();
            });
        }

        if (Schema::hasTable('employees')) {
            Schema::table('bank_accounts', function (Blueprint $table) {
                $table->foreign('employee_id')->references('id')->on('employees')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('bank_accounts', 'vendor_id')) {
                $table->dropForeign(['vendor_id']);
            }
            if (Schema::hasColumn('bank_accounts', 'employee_id')) {
                $table->dropForeign(['employee_id']);
            }
        });

        Schema::table('bank_accounts', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('bank_accounts', 'vendor_id')) {
                $columns[] = 'vendor_id';
            }
            if (Schema::hasColumn('bank_accounts', 'employee_id')) {
                $columns[] = 'employee_id';
            }
            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
