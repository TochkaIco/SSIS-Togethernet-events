<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['event_id', 'items'])]
class EventChecklist extends Model
{
    protected $casts = [
        'items' => 'array', // JSON cast
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
