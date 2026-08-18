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
        Schema::create('pant_alerts', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_complete')->default(false);
            $table->json('completed_by')->nullable(); // User id's of togethernet members that dealt with the pant
            $table->float('sek_received')->nullable(); // Number of sek that have been given to togethernet
            $table->foreignId('admin_user_id')->nullable()->constrained('users', 'id'); // User id of the admin that confirmed that the check has been received by the administration
            $table->string('receiver_swish')->default('unset'); // Swish account number of ekonomiansvarig in case the money has been digitalized
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pant_alerts');
    }
};
