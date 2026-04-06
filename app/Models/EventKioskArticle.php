<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventKioskArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image_url',
        'cost',
        'amount',
        'category_id',
        'kiosk_id',
    ];

    protected $casts = [
        'cost' => 'integer',
        'amount' => 'integer',
    ];

    /**
     * @return BelongsTo<EventKiosk, $this>
     */
    public function kiosk(): BelongsTo
    {
        return $this->belongsTo(EventKiosk::class, 'kiosk_id');
    }

    /**
     * @return BelongsTo<EventKioskCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(EventKioskCategory::class, 'category_id');
    }
}
