<?php

use App\Models\Event;
use App\Models\EventKiosk;
use App\Models\EventKioskArticle;
use App\Models\EventKioskCategory;
use App\Models\EventKioskPurchase;
use App\Models\User;
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
        Schema::create('event_kiosks', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Event::class, 'event_id')->unique()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('event_kiosk_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(EventKiosk::class, 'kiosk_id')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(EventKioskCategory::class, 'category_id');
            $table->string('name');
            $table->text('image_url')->nullable();
            $table->integer('cost');
            $table->integer('amount');
            $table->timestamps();
        });

        Schema::create('event_kiosk_purchases', function (
            Blueprint $table,
        ) {
            $table->id();
            $table->foreignIdFor(EventKiosk::class, 'kiosk_id')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'operator_id');
            $table->integer('cost');
            $table->timestamps();
        });

        Schema::create('event_kiosk_purchase_items', function (
            Blueprint $table,
        ) {
            $table->id();
            $table->foreignIdFor(EventKioskPurchase::class, 'purchase_id');
            $table->foreignIdFor(EventKioskArticle::class, 'article_id');
            $table->integer('amount');
            $table->integer('cost');
            $table->timestamps();
        });

        Schema::create('event_kiosk_categories', function (
            Blueprint $table,
        ) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_kiosks');
        Schema::dropIfExists('event_kiosk_articles');
        Schema::dropIfExists('event_kiosk_purchases');
        Schema::dropIfExists('event_kiosk_purchase_items');
        Schema::dropIfExists('event_kiosk_categories');
    }
};
