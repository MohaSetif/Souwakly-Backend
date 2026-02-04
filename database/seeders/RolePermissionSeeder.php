<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            'manage products',
            'view orders',
            'view analytics',
            'manage sellers',
            'manage users',
            'manage affiliates',
            'create orders',
            'view products',
            'generate referral links',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions

        // Admin - full access
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $adminRole->givePermissionTo(Permission::all());

        // Merchant - manage own products, view own orders, view analytics
        $merchantRole = Role::firstOrCreate(['name' => 'Merchant']);
        $merchantRole->givePermissionTo([
            'manage products',
            'view orders',
            'view analytics',
            'manage affiliates',
        ]);

        // Affiliate - view products, create orders, view own orders, generate referral links
        $affiliateRole = Role::firstOrCreate(['name' => 'Affiliate']);
        $affiliateRole->givePermissionTo([
            'view products',
            'create orders',
            'view orders',
            'generate referral links',
        ]);
    }
}
