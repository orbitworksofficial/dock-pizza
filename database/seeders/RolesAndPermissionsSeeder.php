<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Define Permissions
        $permissions = [
            // Store Management
            ['name' => 'View Stores', 'slug' => 'view-stores', 'group' => 'Stores'],
            ['name' => 'Create Stores', 'slug' => 'create-stores', 'group' => 'Stores'],
            ['name' => 'Update Stores', 'slug' => 'update-stores', 'group' => 'Stores'],
            ['name' => 'Delete Stores', 'slug' => 'delete-stores', 'group' => 'Stores'],

            // Menu Management
            ['name' => 'View Menu', 'slug' => 'view-menu', 'group' => 'Menu'],
            ['name' => 'Create Menu Items', 'slug' => 'create-menu', 'group' => 'Menu'],
            ['name' => 'Update Menu Items', 'slug' => 'update-menu', 'group' => 'Menu'],
            ['name' => 'Delete Menu Items', 'slug' => 'delete-menu', 'group' => 'Menu'],

            // Order Management
            ['name' => 'View Orders', 'slug' => 'view-orders', 'group' => 'Orders'],
            ['name' => 'Update Orders', 'slug' => 'update-orders', 'group' => 'Orders'],
            ['name' => 'Cancel Orders', 'slug' => 'cancel-orders', 'group' => 'Orders'],

            // Customer Management
            ['name' => 'View Customers', 'slug' => 'view-customers', 'group' => 'Customers'],
            ['name' => 'Manage Customers', 'slug' => 'manage-customers', 'group' => 'Customers'],

            // Coupon Management
            ['name' => 'Manage Coupons', 'slug' => 'manage-coupons', 'group' => 'Coupons'],

            // CMS & System Settings
            ['name' => 'Manage CMS', 'slug' => 'manage-cms', 'group' => 'CMS'],
            ['name' => 'Manage Settings', 'slug' => 'manage-settings', 'group' => 'System'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['slug' => $perm['slug']], $perm);
        }

        // 2. Define Roles and Assign Permissions
        $superAdmin = Role::firstOrCreate(['slug' => 'super-admin'], [
            'name' => 'Super Admin',
            'description' => 'Unrestricted access to the entire platform.',
        ]);
        $superAdmin->permissions()->sync(Permission::all());

        $admin = Role::firstOrCreate(['slug' => 'admin'], [
            'name' => 'Admin',
            'description' => 'Full administrative access except global settings.',
        ]);
        $admin->permissions()->sync(
            Permission::whereNotIn('slug', ['delete-stores', 'manage-settings'])->get()
        );

        $storeManager = Role::firstOrCreate(['slug' => 'store-manager'], [
            'name' => 'Store Manager',
            'description' => 'Manage orders, menu availability, and store hours for assigned store.',
        ]);
        $storeManager->permissions()->sync(
            Permission::whereIn('group', ['Orders', 'Menu', 'Stores'])->get()
        );

        $staff = Role::firstOrCreate(['slug' => 'staff'], [
            'name' => 'Store Staff',
            'description' => 'View and update order status.',
        ]);
        $staff->permissions()->sync(
            Permission::whereIn('slug', ['view-orders', 'update-orders'])->get()
        );
    }
}
