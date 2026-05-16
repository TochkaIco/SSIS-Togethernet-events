<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Permission::create(['name' => 'manage qr-tag']);

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $maintainer = Role::firstOrCreate(['name' => 'maintainer', 'guard_name' => 'web']);

        $admin->givePermissionTo('manage qr-tag');
        $superAdmin->syncPermissions(Permission::all());
        $maintainer->syncPermissions(Permission::all());
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Permission::where('name', 'manage qr-tag')->delete();
    }
};
