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
            $table->string('qr_tag_token')->nullable()->unique();
            $table->foreignId('qr_tag_target_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('qr_tag_tagged_at')->nullable();
            $table->foreignId('qr_tag_tagged_by_user_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_users', function (Blueprint $table) {
            $table->dropForeign(['qr_tag_target_user_id']);
            $table->dropForeign(['qr_tag_tagged_by_user_id']);
            $table->dropColumn(['qr_tag_token', 'qr_tag_target_user_id', 'qr_tag_tagged_at', 'qr_tag_tagged_by_user_id']);
        });
    }
};
