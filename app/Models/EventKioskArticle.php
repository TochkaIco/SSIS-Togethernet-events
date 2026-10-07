<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'name',
    'image_url',
    'image_path',
    'cost',
    'amount',
    'category_id',
    'kiosk_id',
])]
class EventKioskArticle extends Model
{
    use HasFactory;

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

    protected function casts(): array
    {
        return [
            'cost' => 'integer',
            'amount' => 'integer',
        ];
    }
}
