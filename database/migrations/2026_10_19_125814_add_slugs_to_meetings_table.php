<?php

use App\Models\Meeting;
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
        if (Schema::hasColumn('meetings', 'slug')) {
            Schema::table('meetings', function (Blueprint $table) {
                $table->dropColumn('slug');
            });
        }

        Schema::table('meetings', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('title');
        });

        Meeting::all()->each(function ($meeting) {
            $meeting->generateSlug();
            $meeting->saveQuietly();
        });

        Schema::table('meetings', function (Blueprint $table) {
            $table->string('slug')->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
