<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Event;
use App\Models\EventUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RegisterUserToEvent
{
    /**
     * Handle the registration of a user to an event with priority logic.
     */
    public function handle(User $user, Event $event): void
    {
        DB::transaction(function () use ($user, $event) {
            // Lock the event to prevent concurrent registrations from causing race conditions
            $event = Event::where('id', $event->id)->lockForUpdate()->first();

            if ($event === null) {
                return;
            }

            // 1. Check if already registered
            $alreadyRegistered = EventUser::where('event_id', $event->id)
                ->where('user_id', $user->id)
                ->exists();

            if ($alreadyRegistered) {
                return;
            }

            // 2. Get user's priority score
            $userScore = $user->registrationPriorityFor($event);

            // 3. Check if there are seats left
            if ($event->hasSeatsLeft()) {
                EventUser::create([
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'in_waitinglist' => false,
                ]);

                return;
            }

            // 4. If full, try to bump someone with a lower score
            // Find a participant with a lower score.
            // We pick the one with the lowest score among all participants,
            // and then the one who registered last among those.
            $participants = $event->participants()->get();

            $candidateToBump = $participants
                ->map(fn ($u) => [
                    'user' => $u,
                    'score' => $u->registrationPriorityFor($event),
                    'registered_at' => $u->pivot->created_at,
                ])
                ->filter(fn ($p) => $p['score'] < $userScore)
                ->sortBy([
                    ['score', 'asc'],
                    ['registered_at', 'desc'],
                ])
                ->first();

            if ($candidateToBump) {
                // Bump them!
                $bumpedUser = $candidateToBump['user'];
                $event->users()->updateExistingPivot($bumpedUser->id, ['in_waitinglist' => true]);

                // Register new user as participant
                EventUser::create([
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'in_waitinglist' => false,
                ]);
            } else {
                // No one to bump, join waiting list
                EventUser::create([
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'in_waitinglist' => true,
                ]);
            }
        });
    }
}
