<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Define and Create Permissions
        $permissions = [
            'create articles',
            'edit articles',
            'manage users',
            'delete users',
            'view articles',
            'configure pages',
            'dev',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // 2. Define and Create Roles
        $togMember = Role::firstOrCreate(['name' => 'tog-member', 'guard_name' => 'web']);
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $maintainer = Role::firstOrCreate(['name' => 'maintainer', 'guard_name' => 'web']);

        // 3. Assign Permissions to Roles

        // TOG Member: Only view access
        $togMember->syncPermissions(['view articles']);

        // Admin: Full access except 'dev'
        $admin->syncPermissions([
            'create articles',
            'edit articles',
            'manage users',
            'delete users',
            'view articles',
            'configure pages',
        ]);

        // Super-Admin & Maintainer: Absolute full access
        $allPermissions = Permission::all();
        $superAdmin->syncPermissions($allPermissions);
        $maintainer->syncPermissions($allPermissions);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Remove the specific roles and permissions created here
        Role::whereIn('name', ['tog-member', 'admin', 'super-admin', 'maintainer'])->delete();
        Permission::whereIn('name', [
            'create articles',
            'edit articles',
            'manage users',
            'delete users',
            'view articles',
            'configure pages',
            'dev',
        ])->delete();
    }
};
