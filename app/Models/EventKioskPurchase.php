<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['operator_id', 'kiosk_id', 'cost'])]

class EventKioskPurchase extends Model
{
    protected $casts = [
        'cost' => 'integer',
    ];

    /**
     * @return BelongsTo<EventKiosk, $this>
     */
    public function kiosk(): BelongsTo
    {
        return $this->belongsTo(EventKiosk::class, 'kiosk_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    /**
     * @return HasMany<EventKioskPurchaseItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(EventKioskPurchaseItem::class, 'purchase_id');
    }
}
