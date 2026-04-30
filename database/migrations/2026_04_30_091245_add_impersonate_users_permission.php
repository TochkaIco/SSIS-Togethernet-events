<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        Permission::create(['name' => 'impersonate users']);

        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $maintainer = Role::firstOrCreate(['name' => 'maintainer', 'guard_name' => 'web']);

        $superAdmin->syncPermissions(Permission::all());
        $maintainer->syncPermissions(Permission::all());
    }

    public function down(): void
    {
        Permission::where('name', 'impersonate users')->delete();
    }
};
