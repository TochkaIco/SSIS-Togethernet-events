<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventKiosk extends Model
{
    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * @return HasMany<EventKioskCategory, $this>
     */
    public function categories(): HasMany
    {
        return $this->hasMany(EventKioskCategory::class, 'kiosk_id');
    }

    /**
     * @return HasMany<EventKioskArticle, $this>
     */
    public function articles(): HasMany
    {
        return $this->hasMany(EventKioskArticle::class, 'kiosk_id');
    }

    /**
     * @return HasMany<EventKioskPurchase, $this>
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(EventKioskPurchase::class, 'kiosk_id');
    }
}
