<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'coupon_code')) {
                $table->string('coupon_code', 50)->nullable()->after('shipping_cost');
            }
            if (! Schema::hasColumn('orders', 'coupon_promotion_id')) {
                $table->foreignId('coupon_promotion_id')
                    ->nullable()
                    ->after('coupon_code')
                    ->constrained('promotions')
                    ->nullOnDelete();
            }
            if (! Schema::hasColumn('orders', 'coupon_discount_amount')) {
                $table->decimal('coupon_discount_amount', 12, 2)->default(0)->after('coupon_promotion_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'coupon_promotion_id')) {
                $table->dropForeign(['coupon_promotion_id']);
            }
            if (Schema::hasColumn('orders', 'coupon_discount_amount')) {
                $table->dropColumn('coupon_discount_amount');
            }
            if (Schema::hasColumn('orders', 'coupon_promotion_id')) {
                $table->dropColumn('coupon_promotion_id');
            }
            if (Schema::hasColumn('orders', 'coupon_code')) {
                $table->dropColumn('coupon_code');
            }
        });
    }
};
