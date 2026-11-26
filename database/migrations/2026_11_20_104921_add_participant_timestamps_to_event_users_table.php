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
        Schema::table('event_users', function (Blueprint $table) {
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('arrived_at')->nullable();
            $table->timestamp('seat_booked_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_users', function (Blueprint $table) {
            $table->dropColumn(['paid_at', 'arrived_at', 'seat_booked_at']);
        });
    }
};
