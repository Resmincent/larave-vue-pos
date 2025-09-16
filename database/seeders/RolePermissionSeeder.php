<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Roles
        $admin     = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $cashier   = Role::firstOrCreate(['name' => 'Cashier', 'guard_name' => 'web']);
        $inventory = Role::firstOrCreate(['name' => 'Inventory', 'guard_name' => 'web']);
        $customer  = Role::firstOrCreate(['name' => 'Customer', 'guard_name' => 'web']);
        $supplier  = Role::firstOrCreate(['name' => 'Supplier', 'guard_name' => 'web']);

        // Permissions
        $permissions = [
            // Categories
            'categories.index',
            'categories.create',
            'categories.edit',
            'categories.delete',
            // Taxes
            'taxes.index',
            'taxes.create',
            'taxes.edit',
            'taxes.delete',
            // Products
            'products.index',
            'products.create',
            'products.edit',
            'products.delete',
            // Inventories
            'inventories.index',
            'inventories.adjust',
            // Customers
            'customers.index',
            'customers.create',
            'customers.edit',
            'customers.delete',
            // Suppliers
            'suppliers.index',
            'suppliers.create',
            'suppliers.edit',
            'suppliers.delete',
            // Payment Methods
            'payment-methods.index',
            'payment-methods.create',
            'payment-methods.edit',
            'payment-methods.delete',
            // Purchases
            'purchases.index',
            'purchases.create',
            'purchases.show',
            // Sales
            'sales.index',
            'sales.create',
            'sales.show',
            'sales.void',
            // Cash Sessions
            'cash-sessions.index',
            'cash-sessions.open',
            'cash-sessions.close',
            // Payments
            'payments.index',
            // Users
            'users.index',
            'users.create',
            'users.edit',
            'users.delete',
            // Roles
            'roles.index',
            'roles.create',
            'roles.edit',
            'roles.delete',
            // Permissions
            'permissions.index',
            'permissions.create',
            'permissions.edit',
            'permissions.delete',
            // Assign Permissions
            'assign.permissions.index',
            'assign.permissions.edit',
            'assign.permissions.update',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Mapping
        $admin->givePermissionTo(Permission::all());

        $cashier->givePermissionTo([
            'sales.index',
            'sales.create',
            'sales.show',
            'sales.void',
            'payments.index',
            'cash-sessions.index',
            'cash-sessions.open',
            'cash-sessions.close',
            'customers.index',
            'customers.create',
        ]);

        $inventory->givePermissionTo([
            'products.index',
            'products.create',
            'products.edit',
            'products.delete',
            'categories.index',
            'categories.create',
            'categories.edit',
            'categories.delete',
            'taxes.index',
            'taxes.create',
            'taxes.edit',
            'taxes.delete',
            'inventories.index',
            'inventories.adjust',
            'suppliers.index',
            'suppliers.create',
            'suppliers.edit',
            'suppliers.delete',
            'purchases.index',
            'purchases.create',
            'purchases.show',
        ]);

        $customer->givePermissionTo(['sales.index', 'sales.show']);
        $supplier->givePermissionTo(['purchases.index', 'purchases.show']);
    }
}
