<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Event;
use App\Models\EventUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UnregisterUserFromEvent
{
    /**
     * Unregister a user from an event and process the waiting list.
     */
    public function handle(User $user, Event $event): void
    {
        DB::transaction(function () use ($user, $event) {
            $registration = EventUser::where('event_id', $event->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (! $registration) {
                return;
            }

            $periodId = $registration->event_period_id;

            $registration->delete();

            // Process waiting list for the period
            (new ProcessWaitingList)->handle($event, $periodId);
        });
    }
}
