<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Event;
use Illuminate\Support\Facades\DB;

class ProcessWaitingList
{
    /**
     * Process the waiting list for a given event and period.
     * Moves users from waiting list to participants if seats are available.
     */
    public function handle(Event $event, ?int $periodId = null): void
    {
        DB::transaction(function () use ($event, $periodId) {
            // Lock the event
            $event = Event::where('id', $event->id)->lockForUpdate()->first();

            if ($event === null) {
                return;
            }

            if ($periodId) {
                $period = $event->periods()->find($periodId);
                $period?->promoteNext();
            } else {
                foreach ($event->periods as $period) {
                    $period->promoteNext();
                }
            }
        });
    }
}
