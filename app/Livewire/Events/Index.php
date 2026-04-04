<?php

declare(strict_types=1);

namespace App\Livewire\Events;

use App\Actions\RegisterUserToEvent;
use App\Models\Event;
use App\Models\EventUser;
use Flux\Flux;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Index extends Component
{
    public ?int $eventIdToUnregister = null;

    #[Computed]
    public function events(): Collection
    {
        return Event::where('display_starts_at', '<=', now())
            ->latest()
            ->get();
    }

    public function eventIsActive($event): bool
    {
        return $event->display_starts_at <= now() && $event->event_ends_at >= now();
    }

    public function userIsRegistered($eventId): bool
    {
        return EventUser::where('event_id', $eventId)->where('user_id', auth()->id())->exists();
    }

    public function confirmUnregister(?int $eventId): void
    {
        $this->eventIdToUnregister = $eventId;
    }

    public function unregisterUser()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        if ($this->eventIdToUnregister && $this->userIsRegistered($this->eventIdToUnregister)) {
            EventUser::where('event_id', $this->eventIdToUnregister)->where('user_id', Auth::id())->delete();
            Flux::toast(text: 'You have been unregistered from this event.', heading: 'Success', variant: 'success');
            $this->eventIdToUnregister = null;
            $this->modal('unregister-confirmation')->close();
        } else {
            Flux::toast(text: 'Failed to remove your registration.', heading: 'Error', variant: 'danger');
        }
    }

    public function registerUser($eventId, RegisterUserToEvent $action)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $event = Event::findOrFail($eventId);

        $alreadyRegistered = EventUser::where('event_id', $eventId)
            ->where('user_id', Auth::id())
            ->exists();

        if ($alreadyRegistered) {
            Flux::toast(text: 'You are already registered for this event.', heading: 'Error', variant: 'danger');

            return;
        }

        $registration = $action->handle(Auth::user(), $event);

        if ($registration?->in_waitinglist) {
            Flux::toast(text: 'You have been added to the waiting list.', heading: 'Success', variant: 'success');
        } else {
            Flux::toast(text: 'You have been registered for this event.', heading: 'Success', variant: 'success');
        }
    }

    public function render(): Factory|\Illuminate\Contracts\View\View|View
    {
        return view('livewire.events.index', [
            'events' => $this->events]);
    }
}
