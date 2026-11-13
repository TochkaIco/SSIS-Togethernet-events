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
        Schema::table('event_kiosk_articles', function (Blueprint $table) {
            $table->decimal('cost')->change();
        });

        Schema::table('event_kiosk_purchases', function (Blueprint $table) {
            $table->decimal('cost')->change();
        });

        Schema::table('event_kiosk_purchase_items', function (Blueprint $table) {
            $table->decimal('cost')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_kiosk_articles', function (Blueprint $table) {
            $table->integer('cost')->change();
        });

        Schema::table('event_kiosk_purchases', function (Blueprint $table) {
            $table->integer('cost')->change();
        });

        Schema::table('event_kiosk_purchase_items', function (Blueprint $table) {
            $table->integer('cost')->change();
        });
    }
};
