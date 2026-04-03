<?php

declare(strict_types=1);

namespace App\Models;

use App\EventType;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @method static create(mixed[] $data)
 */
#[Fillable([
    'title',
    'description',
    'event_type',
    'image_path',
    'display_starts_at',
    'event_starts_at',
    'event_ends_at',
    'links',
])]
class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    protected $casts = [
        'event_type' => EventType::class,
        'links' => AsArrayObject::class,
        'display_starts_at' => 'datetime',
        'event_starts_at' => 'datetime',
        'event_ends_at' => 'datetime',
    ];

    protected function formattedDescription(): Attribute
    {
        return Attribute::get(fn (mixed $value, array $attributes) => str($attributes['description'])->markdown());
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_users')
            ->withPivot(['is_working', 'in_waitinglist', 'has_paid', 'has_arrived'])
            ->withTimestamps();
    }

    public function participants()
    {
        return $this->users()->where(function ($query) {
            $query->where('event_users.in_waitinglist', false)
                ->orWhereNull('event_users.in_waitinglist');
        });
    }

    public function waitingList()
    {
        return $this->users()->wherePivot('in_waitinglist', true);
    }
}
