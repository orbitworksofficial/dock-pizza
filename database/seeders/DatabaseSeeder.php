<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Run basic lookup/domain seeders
        $this->call([
            RolesAndPermissionsSeeder::class,
            StoreSeeder::class,
            MenuSeeder::class,
            DockPizzaMenuUpdateSeeder::class,
            SystemSeeder::class,
        ]);

        // 2. Create Default Users for Testing
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@pizzaviva.com'],
            [
                'name' => 'Super Admin',
                'phone' => '111-222-3333',
                'role' => UserRole::SUPER_ADMIN,
                'is_active' => true,
                'password' => Hash::make('password'),
            ]
        );
        $superAdmin->roles()->sync(\App\Models\Role::where('slug', 'super-admin')->pluck('id'));

        $admin = User::firstOrCreate(
            ['email' => 'admin@pizzaviva.com'],
            [
                'name' => 'Admin User',
                'phone' => '222-333-4444',
                'role' => UserRole::ADMIN,
                'is_active' => true,
                'password' => Hash::make('password'),
            ]
        );
        $admin->roles()->sync(\App\Models\Role::where('slug', 'admin')->pluck('id'));

        $manager = User::firstOrCreate(
            ['email' => 'manager@pizzaviva.com'],
            [
                'name' => 'Store Manager',
                'phone' => '333-444-5555',
                'role' => UserRole::STORE_MANAGER,
                'is_active' => true,
                'password' => Hash::make('password'),
            ]
        );
        $manager->roles()->sync(\App\Models\Role::where('slug', 'store-manager')->pluck('id'));

        $customer = User::firstOrCreate(
            ['email' => 'customer@pizzaviva.com'],
            [
                'name' => 'John Doe',
                'phone' => '444-555-6666',
                'role' => UserRole::CUSTOMER,
                'is_active' => true,
                'password' => Hash::make('password'),
            ]
        );
    }
}
