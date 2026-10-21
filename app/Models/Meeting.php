<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

#[Fillable([
    'title',
    'description',
    'url_to_external_protocol',
    'meeting_starts_at',
    'meeting_ends_at',
])]
#[RouteKey('slug')]
/**
 * @property Carbon $meeting_starts_at
 * @property Carbon $meeting_ends_at
 */
class Meeting extends Model
{
    use HasFactory, HasSlug;

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    public function attendants(): HasMany
    {
        return $this->hasMany(MeetingAttendant::class);
    }

    public function canTakeAttendance(User $user): bool
    {
        return $user->hasPermissionTo('take attendance') && Carbon::parse($this->meeting_ends_at)->addHours(2) > now();
    }

    public function isUpdatableAttendance(): bool
    {
        return Carbon::parse($this->meeting_ends_at)->addHours(2) > now();
    }

    protected function casts(): array
    {
        return [
            'meeting_starts_at' => 'datetime',
            'meeting_ends_at' => 'datetime',
        ];
    }
}
