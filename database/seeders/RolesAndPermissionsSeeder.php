<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Prevent duplicate entry errors
        $permissions = [
            'create articles',
            'edit articles',
            'manage users',
            'view articles',
            'configure pages',
            'dev',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 2. Create roles and assign permissions
        Role::firstOrCreate(['name' => 'tog-member'])
            ->givePermissionTo('view articles');

        Role::firstOrCreate(['name' => 'admin'])
            ->syncPermissions(['create articles', 'edit articles', 'manage users', 'view articles', 'configure pages']);

        Role::firstOrCreate(['name' => 'super-admin'])
            ->syncPermissions(Permission::all());

        Role::firstOrCreate(['name' => 'maintainer'])
            ->syncPermissions(Permission::all());
    }
}
