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
        Schema::table('pant_alerts', function (Blueprint $table) {
            $table->string('receipt_path')->after('receiver_swish')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pant_alerts', function (Blueprint $table) {
            $table->dropColumn('receipt_path');
        });
    }
};
