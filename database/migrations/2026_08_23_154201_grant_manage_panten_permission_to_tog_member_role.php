<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $member = Role::firstOrCreate(['name' => 'tog-member', 'guard_name' => 'web']);
        $member->givePermissionTo('manage panten');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $member = Role::firstOrCreate(['name' => 'tog-member', 'guard_name' => 'web']);
        $member->revokePermissionTo('manage panten');
    }
};
