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
            $table->string('image_path')->after('image_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_kiosk_articles', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }
};
