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
        // STEP 1: Add auth columns to customers table (check if exists first)
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'password')) {
                $table->string('password')->nullable()->after('email');
            }
            if (!Schema::hasColumn('customers', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('password');
            }
            if (!Schema::hasColumn('customers', 'remember_token')) {
                $table->rememberToken()->after('email_verified_at');
            }
        });

        // STEP 2: Add customer_id columns to tables FIRST
        if (!Schema::hasColumn('carts', 'customer_id')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->unsignedBigInteger('customer_id')->nullable()->after('id');
            });
        }

        if (!Schema::hasColumn('wishlists', 'customer_id')) {
            Schema::table('wishlists', function (Blueprint $table) {
                $table->unsignedBigInteger('customer_id')->nullable()->after('id');
            });
        }

        // STEP 3: Migrate data from customer_users to customers
        $customerUsers = DB::table('customer_users')->get();
        
        foreach ($customerUsers as $customerUser) {
            // Check if customer already exists by email
            $existingCustomer = DB::table('customers')
                ->where('email', $customerUser->email)
                ->first();

            if ($existingCustomer) {
                // Update existing customer with auth data
                DB::table('customers')
                    ->where('id', $existingCustomer->id)
                    ->update([
                        'password' => $customerUser->password,
                        'email_verified_at' => $customerUser->email_verified_at,
                        'remember_token' => $customerUser->remember_token,
                        'phone' => $customerUser->phone ?? $existingCustomer->phone,
                        'whatsapp_number' => $customerUser->whatsapp_number ?? $existingCustomer->whatsapp_number,
                        'updated_at' => now(),
                    ]);

                // Update foreign keys to point to existing customer
                $this->updateForeignKeys($customerUser->id, $existingCustomer->id);
            } else {
                // Create new customer from customer_user
                $newCustomerId = DB::table('customers')->insertGetId([
                    'name' => $customerUser->name,
                    'email' => $customerUser->email,
                    'phone' => $customerUser->phone,
                    'whatsapp_number' => $customerUser->whatsapp_number,
                    'password' => $customerUser->password,
                    'email_verified_at' => $customerUser->email_verified_at,
                    'remember_token' => $customerUser->remember_token,
                    'is_active' => true,
                    'created_at' => $customerUser->created_at,
                    'updated_at' => $customerUser->updated_at,
                ]);

                // Update foreign keys
                $this->updateForeignKeys($customerUser->id, $newCustomerId);
            }
        }

        // STEP 4: Drop foreign key constraints and indexes from customer_users
        Schema::table('carts', function (Blueprint $table) {
            $table->dropForeign(['customer_user_id']);
            $table->dropIndex(['customer_user_id']);
            $table->dropIndex('unique_active_customer_cart');
        });

        Schema::table('wishlists', function (Blueprint $table) {
            $table->dropForeign(['customer_user_id']);
            $table->dropIndex(['customer_user_id']);
            $table->dropIndex('unique_customer_wishlist_product');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['customer_user_id']);
            $table->dropIndex(['customer_user_id']);
        });

        // STEP 5: Drop old columns
        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn('customer_user_id');
        });

        Schema::table('wishlists', function (Blueprint $table) {
            $table->dropColumn('customer_user_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('customer_user_id');
        });

        // STEP 6: Add foreign key constraints and indexes to customers
        Schema::table('carts', function (Blueprint $table) {
            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->onDelete('cascade');
            $table->index('customer_id');
            $table->unique(['customer_id', 'deleted_at'], 'unique_active_customer_cart');
        });

        Schema::table('wishlists', function (Blueprint $table) {
            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->onDelete('cascade');
            $table->index('customer_id');
            $table->unique(['customer_id', 'product_id'], 'unique_customer_wishlist_product');
        });
    }

    /**
     * Update foreign keys in related tables
     */
    private function updateForeignKeys($oldCustomerUserId, $newCustomerId): void
    {
        // Update carts
        DB::table('carts')
            ->where('customer_user_id', $oldCustomerUserId)
            ->update(['customer_id' => $newCustomerId]);

        // Update wishlists
        DB::table('wishlists')
            ->where('customer_user_id', $oldCustomerUserId)
            ->update(['customer_id' => $newCustomerId]);

        // Update orders - merge to existing customer_id field
        DB::table('orders')
            ->where('customer_user_id', $oldCustomerUserId)
            ->update(['customer_id' => $newCustomerId]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove auth columns from customers
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['password', 'email_verified_at', 'remember_token']);
        });

        // Note: Data cannot be fully restored, this is one-way migration
        // Keep customer_users table as backup, don't drop it yet
    }
};
