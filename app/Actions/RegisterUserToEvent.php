<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Event;
use App\Models\EventPeriod;
use App\Models\EventUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RegisterUserToEvent
{
    /**
     * Handle the registration of a user to an event with priority logic.
     *
     * @throws \Throwable
     */
    public function handle(User $user, Event $event, ?int $periodId = null): ?EventUser
    {
        return DB::transaction(function () use ($user, $event, $periodId) {
            // Lock the event to prevent concurrent registrations from causing race conditions
            $event = Event::where('id', $event->id)->lockForUpdate()->first();

            if ($event === null || ! $event->canRegister()) {
                return null;
            }

            // 1. Check if already registered
            $alreadyRegistered = EventUser::where('event_id', $event->id)
                ->where('user_id', $user->id)
                ->first();

            if ($alreadyRegistered) {
                return $alreadyRegistered;
            }

            // 2. Check if external domains are allowed
            if (! $event->allowsUser($user)) {
                return null;
            }

            // 3. Determine target period
            $targetPeriod = $this->determineTargetPeriod($event, $periodId);

            if (! $targetPeriod instanceof EventPeriod) {
                return null;
            }

            // 4. Register using the period model
            $registration = $targetPeriod->register($user);

            if ($event->event_type === \App\EventType::QR_TAG && $event->isQrTagGameStarted()) {
                $registration->insertIntoCycle();
            }

            return $registration;
        });
    }

    private function determineTargetPeriod(Event $event, ?int $periodId): ?EventPeriod
    {
        if ($periodId) {
            return $event->periods()->find($periodId);
        }

        if ($event->one_hour_periods) {
            // Find first period with seats left
            foreach ($event->periods()->where('type', 'period')->get() as $p) {
                if ($p->hasSeatsLeft()) {
                    return $p;
                }
            }

            // All periods full, join waiting list of the first period
            return $event->periods()->where('type', 'period')->first();
        }

        // Single period event
        return $event->periods()->where('type', 'period')->first();
    }
}
