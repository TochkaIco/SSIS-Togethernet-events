<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('global_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action_title');
            $table->string('action_type');
            $table->json('details')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        Permission::create(['name' => 'view global logs']);

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $maintainer = Role::firstOrCreate(['name' => 'maintainer', 'guard_name' => 'web']);

        $admin->givePermissionTo('view global logs');
        $superAdmin->syncPermissions(Permission::all());
        $maintainer->syncPermissions(Permission::all());
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('global_logs');

        Permission::where('name', 'view global logs')->delete();
    }
};
