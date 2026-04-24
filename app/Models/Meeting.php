<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'title',
    'description',
    'url_to_external_protocol',
    'meeting_starts_at',
    'meeting_ends_at',
])]
class Meeting extends Model
{
    use HasFactory;

    protected $casts = [
        'meeting_starts_at' => 'datetime',
        'meeting_ends_at' => 'datetime',
    ];

    public function attendants(): HasMany
    {
        return $this->hasMany(MeetingAttendant::class);
    }
}
