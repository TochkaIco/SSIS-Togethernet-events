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
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('one_hour_periods')->default(false)->after('entry_fee');
            $table->integer('interval_length')->nullable()->after('one_hour_periods');
            $table->integer('one_hour_periods_number')->nullable()->after('interval_length');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('one_hour_periods');
            $table->dropColumn('interval_length');
            $table->dropColumn('one_hour_periods_number');
        });
    }
};
