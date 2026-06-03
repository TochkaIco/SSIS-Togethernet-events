<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Event;
use App\Models\QrTagLog;
use Illuminate\Support\Str;

class ShuffleQrTagTargets
{
    public function handle(Event $event, ?int $adminId = null, string $type = 'started', bool $respawnAll = false): void
    {
        $query = $event->participants()
            ->where('is_disabled', false);

        if (! $respawnAll) {
            $query = $query->whereNull('qr_tag_tagged_at');
        }

        $participants = $query->get()->shuffle();

        if ($participants->count() < 2) {
            return;
        }

        foreach ($participants as $index => $participant) {
            $nextIndex = ($index + 1) % $participants->count();
            $target = $participants[$nextIndex];

            $participant->update([
                'qr_tag_target_user_id' => $target->user_id,
                'qr_tag_token' => Str::random(32),
                'qr_tag_tagged_at' => null,
                'qr_tag_tagged_by_user_id' => null,
            ]);
        }

        QrTagLog::create([
            'event_id' => $event->id,
            'admin_id' => $adminId,
            'type' => $type,
        ]);
    }
}
