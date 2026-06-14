<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('elevkar_id')->nullable()->unique()->after('google_refresh_token');
            $table->text('elevkar_token')->nullable()->after('elevkar_id');
            $table->text('elevkar_refresh_token')->nullable()->after('elevkar_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('elevkar_id');
            $table->dropColumn('elevkar_token');
            $table->dropColumn('elevkar_refresh_token');
        });
    }
};
