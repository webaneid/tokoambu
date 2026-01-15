<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For MySQL/MariaDB
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
                'draft',
                'waiting_payment',
                'dp_paid',
                'paid',
                'packed',
                'shipped',
                'done',
                'cancelled',
                'cancelled_refund_pending',
                'refunded'
            ) DEFAULT 'draft'");
        }
        // For SQLite - recreate table with new column definition
        else if (DB::getDriverName() === 'sqlite') {
            // SQLite doesn't have native ENUM, Laravel uses TEXT with check constraint
            // We don't need to do anything special here, just document the new statuses
            // The validation will be handled at the application level
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // For MySQL/MariaDB
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
                'draft',
                'waiting_payment',
                'dp_paid',
                'paid',
                'packed',
                'shipped',
                'done',
                'cancelled'
            ) DEFAULT 'draft'");
        }
        // For SQLite - no action needed
    }
};
