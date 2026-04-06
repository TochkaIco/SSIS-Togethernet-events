<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        Permission::create(['name' => 'manage kiosk']);

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->givePermissionTo('manage kiosk');
    }

    public function down(): void
    {
        Permission::where('name', 'manage kiosk')->delete();
    }
};
