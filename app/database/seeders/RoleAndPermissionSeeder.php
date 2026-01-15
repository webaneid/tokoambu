<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles (idempotent)
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $operator = Role::firstOrCreate(['name' => 'Operator']);
        $finance = Role::firstOrCreate(['name' => 'Finance']);
        $user = Role::firstOrCreate(['name' => 'User']);

        $permissions = [
            // Product
            'view_products', 'create_products', 'edit_products', 'delete_products',
            // Supplier
            'view_suppliers', 'create_suppliers', 'edit_suppliers', 'delete_suppliers',
            // Customer
            'view_customers', 'create_customers', 'edit_customers', 'delete_customers',
            // Order
            'view_orders', 'create_orders', 'edit_orders', 'delete_orders', 'update_order_status',
            // Payment
            'view_payments', 'create_payments', 'verify_payments',
            // Purchase
            'view_purchases', 'create_purchases', 'edit_purchases', 'delete_purchases',
            // Finance/Ledger
            'view_ledger', 'create_ledger_entry', 'view_reports',
            // Shipment
            'view_shipments', 'create_shipments', 'update_shipment_status',
            // Settings
            'view_settings', 'edit_settings',
            // Warehouse
            'warehouse_receiving', 'warehouse_transfer', 'warehouse_adjustment', 'warehouse_opname', 'warehouse_report', 'warehouse_dashboard',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Assign permissions to roles
        // Super Admin - all permissions
        $superAdmin->syncPermissions(Permission::all());

        // Operator
        $operator->givePermissionTo([
            'view_products', 'create_products', 'edit_products', 'delete_products',
            'view_suppliers', 'create_suppliers', 'edit_suppliers', 'delete_suppliers',
            'view_customers', 'create_customers', 'edit_customers', 'delete_customers',
            'view_orders', 'create_orders', 'edit_orders', 'delete_orders', 'update_order_status',
            'view_payments', 'create_payments',
            'view_purchases', 'create_purchases', 'edit_purchases', 'delete_purchases',
            'view_shipments', 'create_shipments', 'update_shipment_status',
            'warehouse_receiving', 'warehouse_transfer', 'warehouse_adjustment', 'warehouse_opname', 'warehouse_report', 'warehouse_dashboard',
        ]);

        // Finance
        $finance->givePermissionTo([
            'view_payments', 'create_payments', 'verify_payments',
            'view_ledger', 'create_ledger_entry',
            'view_orders',
            'view_reports',
        ]);

        // User (customer login)
        $user->givePermissionTo([
            'view_orders',
        ]);
    }
}
