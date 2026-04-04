<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Event;
use App\Models\EventUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RegisterUserToEvent
{
    /**
     * Handle the registration of a user to an event with priority logic.
     *
     * @throws \Throwable
     */
    public function handle(User $user, Event $event): ?EventUser
    {
        return DB::transaction(function () use ($user, $event) {
            // Lock the event to prevent concurrent registrations from causing race conditions
            $event = Event::where('id', $event->id)->lockForUpdate()->first();

            if ($event === null) {
                return null;
            }

            // 1. Check if already registered
            $alreadyRegistered = EventUser::where('event_id', $event->id)
                ->where('user_id', $user->id)
                ->first();

            if ($alreadyRegistered) {
                return $alreadyRegistered;
            }

            // 2. Get user's priority score
            $userScore = $user->registrationPriorityFor($event);

            // 3. Check if there are seats left
            if ($event->hasSeatsLeft()) {
                return EventUser::create([
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'in_waitinglist' => false,
                ]);
            }

            // 4. If full, try to bump someone with a lower score
            // Find a participant with a lower score.
            // We pick the one with the lowest score among all participants,
            // and then the one who registered last among those.
            /** @var Collection<int, User> $participants */
            $participants = $event->participants()->get();

            $candidateToBump = $participants
                ->map(fn (User $u) => [
                    'user' => $u,
                    'score' => $u->registrationPriorityFor($event),
                    'registered_at' => $u->pivot->created_at,
                ])
                ->filter(fn (array $p) => $p['score'] < $userScore)
                ->sortBy([
                    ['score', 'asc'],
                    ['registered_at', 'desc'],
                ])
                ->first();

            if ($candidateToBump) {
                $bumpedUser = $candidateToBump['user'];
                $event->users()->updateExistingPivot($bumpedUser->id, ['in_waitinglist' => true]);

                // Register new user as participant
                return EventUser::create([
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'in_waitinglist' => false,
                ]);
            }

            // No one to bump, join waiting list
            return EventUser::create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'in_waitinglist' => true,
            ]);
        });
    }
}
