<?php

use App\Models\EventKiosk;
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
        Schema::table('event_kiosk_categories', function (Blueprint $table) {
            $table->foreignIdFor(EventKiosk::class, 'kiosk_id')->after('id')->nullable()->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_kiosk_categories', function (Blueprint $table) {
            $table->dropForeignIdFor(EventKiosk::class, 'kiosk_id');
            $table->dropColumn('kiosk_id');
        });
    }
};
