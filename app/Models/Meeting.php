<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

#[Fillable([
    'title',
    'description',
    'url_to_external_protocol',
    'meeting_starts_at',
    'meeting_ends_at',
])]
/**
 * @property Carbon $meeting_starts_at
 * @property Carbon $meeting_ends_at
 */
class Meeting extends Model
{
    use HasFactory;

    public function attendants(): HasMany
    {
        return $this->hasMany(MeetingAttendant::class);
    }

    protected function casts(): array
    {
        return [
            'meeting_starts_at' => 'datetime',
            'meeting_ends_at' => 'datetime',
        ];
    }
}
