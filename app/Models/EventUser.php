<?php

declare(strict_types=1);

namespace App\Models;

use App\Actions\ShuffleQrTagTargets;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Carbon\Carbon;
use Database\Factories\EventUserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $user_id
 * @property int $event_id
 * @property int $event_period_id
 * @property bool $in_waitinglist
 * @property bool $has_paid
 * @property bool $has_arrived
 * @property bool $is_working
 * @property string|null $qr_tag_token
 * @property int|null $qr_tag_target_user_id
 * @property Carbon|null $qr_tag_tagged_at
 * @property int|null $qr_tag_tagged_by_user_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Table(name: 'event_users')]
#[Fillable([
    'user_id',
    'event_id',
    'event_period_id',
    'in_waitinglist',
    'is_disabled',
    'has_paid',
    'has_arrived',
    'is_working',
    'qr_tag_token',
    'qr_tag_target_user_id',
    'qr_tag_tagged_at',
    'qr_tag_tagged_by_user_id',
    'qr_tag_count',
])]
class EventUser extends Model
{
    /** @use HasFactory<EventUserFactory> */
    use HasFactory;

    public $incrementing = true;

    protected function casts(): array
    {
        return [
            'in_waitinglist' => 'boolean',
            'is_disabled' => 'boolean',
            'has_paid' => 'boolean',
            'has_arrived' => 'boolean',
            'event_id' => 'integer',
            'user_id' => 'integer',
            'event_period_id' => 'integer',
            'is_working' => 'boolean',
            'qr_tag_target_user_id' => 'integer',
            'qr_tag_tagged_by_user_id' => 'integer',
            'qr_tag_tagged_at' => 'datetime',
            'qr_tag_count' => 'integer',
        ];
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
        return $this->belongsTo(User::class, 'qr_tag_target_user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function taggedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'qr_tag_tagged_by_user_id');
    }

    /**
     * @return BelongsTo<EventPeriod, $this>
     */
    public function eventPeriod(): BelongsTo
    {
        return $this->belongsTo(EventPeriod::class, 'event_period_id');
    }

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Generate the QR code SVG for the tagging token.
     */
    public function qrTagQrCodeSvg(): string
    {
        if (! $this->qr_tag_token) {
            return '';
        }

        $url = route('qr_tag.confirm', ['token' => $this->qr_tag_token]);

        $svg = (new Writer(
            new ImageRenderer(
                new RendererStyle(256, 1, null, null, Fill::uniformColor(new Rgb(255, 255, 255), new Rgb(0, 0, 0))),
                new SvgImageBackEnd
            )
        ))->writeString($url);

        return trim(substr($svg, strpos($svg, "\n") + 1));
    }

    public function qrTagRank(): int
    {
        if (! $this->event_id) {
            return 0;
        }

        return self::where('event_id', $this->event_id)
            ->where('is_disabled', false)
            ->participants()
            ->where('qr_tag_count', '>', $this->qr_tag_count)
            ->count() + 1;
    }

    /**
     * @param  Builder<EventUser>  $query
     */
    public function scopeParticipants(Builder $query): void
    {
        $query->where(function ($query) {
            $query->where('in_waitinglist', false)
                ->orWhereNull('in_waitinglist');
        });
    }

    /**
     * @param  Builder<EventUser>  $query
     */
    public function scopeWaitingList(Builder $query): void
    {
        $query->where('in_waitinglist', true);
    }

    /**
     * Toggle the disabled status of the player and repair/restore the game cycle.
     */
    public function toggleDisabled(): void
    {
        if ($this->is_disabled) {
            $this->enable();
        } else {
            $this->disable();
        }
    }

    /**
     * Disable the player and repair the game cycle by connecting their hunter to their target.
     */
    public function disable(): void
    {
        if ($this->is_disabled) {
            return;
        }

        DB::transaction(function () {
            $this->update(['is_disabled' => true]);

            if ($this->event->isQrTagGameStarted() && ! $this->qr_tag_tagged_at) {
                // Find who was targeting this player
                $hunter = self::where('event_id', $this->event_id)
                    ->where('qr_tag_target_user_id', $this->user_id)
                    ->first();

                $targetId = $this->qr_tag_target_user_id;

                if ($hunter) {
                    $hunter->update(['qr_tag_target_user_id' => $targetId]);

                    // If the hunter is now targeting themselves, re-shuffle or clear target
                    if ($targetId === $hunter->user_id) {
                        $activeCount = $this->event->qrTagActiveParticipantsCount();

                        if ($activeCount > 1) {
                            app(ShuffleQrTagTargets::class)->handle($this->event, auth()->id(), 'reshuffled');
                        } else {
                            $hunter->update(['qr_tag_target_user_id' => null]);
                        }
                    }
                }

                $this->update(['qr_tag_target_user_id' => null]);
            }
        });
    }

    /**
     * Enable the player and restore them into the game cycle if active.
     */
    public function enable(): void
    {
        if (! $this->is_disabled) {
            return;
        }

        DB::transaction(function () {
            $this->update([
                'is_disabled' => false,
                'qr_tag_token' => Str::random(32),
            ]);

            if ($this->event->isQrTagGameStarted() && ! $this->qr_tag_tagged_at) {
                $this->insertIntoCycle();
            }
        });
    }

    /**
     * Insert the player into the active QR tag game cycle.
     */
    public function insertIntoCycle(): void
    {
        $activePlayers = $this->event->registrations()
            ->whereNull('qr_tag_tagged_at')
            ->where('is_disabled', false)
            ->where('user_id', '!=', $this->user_id)
            ->get();

        if ($activePlayers->isNotEmpty()) {
            $host = $activePlayers->random();
            $oldTargetId = $host->qr_tag_target_user_id;

            $this->update([
                'qr_tag_target_user_id' => $oldTargetId,
                'qr_tag_token' => $this->qr_tag_token ?? Str::random(32),
            ]);
            $host->update(['qr_tag_target_user_id' => $this->user_id]);
        }
    }
}
