<?php

declare(strict_types=1);

namespace App\Models;

use App\Jobs\SendDiscordQrtagNotification;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'event_id',
    'user_id',
    'target_user_id',
    'admin_id',
    'type',
    'data',
])]
class QrTagLog extends Model
{
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    protected static function booted(): void
    {
        static::created(function (QrTagLog $log) {
            $log->load(['user', 'targetUser', 'admin', 'event']);

            $message = match ($log->type) {
                'started' => "# Spelet har börjat i {$log->event->title}!",
                'tagged' => sprintf(
                    "%s kullade %s!\nNu är det %d spelare kvar.",
                    $log->user->name,
                    $log->targetUser->name,
                    $log->event->qrTagActiveParticipantsCount()
                ),
                'respawn' => "**Respawn:** {$log->user->name} har återvänt till spelet av en administratör!",
                'respawn_all' => "**Alla spelare har återuppstått!** Målen har blandats om i {$log->event->title}.",
                'reset' => "**Spelet har återställts** för {$log->event->title}.",
                'reshuffled' => '**Målen har blandats om automatiskt!** En loop uppstod och spelet behövde rättas till.',
                default => null,
            };

            if ($message) {
                SendDiscordQrtagNotification::dispatch($message);
            }
        });
    }
}
