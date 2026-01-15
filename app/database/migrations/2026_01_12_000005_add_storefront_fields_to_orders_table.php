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
            // Add source column (admin vs storefront)
            $table->enum('source', ['admin', 'storefront'])->default('admin')->after('status');

            // Add customer_user_id (nullable, for storefront orders)
            $table->unsignedBigInteger('customer_user_id')->nullable()->after('customer_id');

            // Add ip_address for fraud detection
            $table->ipAddress()->nullable()->after('customer_user_id');

            // Add foreign key for customer_user_id
            $table->foreign('customer_user_id')
                  ->references('id')
                  ->on('customer_users')
                  ->onDelete('set null');

            // Add index for customer_user_id
            $table->index('customer_user_id');
            $table->index('source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Drop foreign key
            $table->dropForeign(['customer_user_id']);

            // Drop columns
            $table->dropColumn(['source', 'customer_user_id', 'ip_address']);

            // Drop indexes
            $table->dropIndex(['customer_user_id']);
            $table->dropIndex(['source']);
        });
    }
};
