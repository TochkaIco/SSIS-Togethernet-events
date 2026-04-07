<?php

declare(strict_types=1);

namespace App\Livewire\Events;

use App\Actions\RegisterUserToEvent;
use App\Models\Event;
use App\Models\EventUser;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class EventShow extends Component
{
    public Event $event;

    public ?int $period = null;

    public ?int $eventIdToUnregister = null;

    #[Url]
    public string $tab = 'view';

    protected $queryString = ['tab'];

    public function mount(Event $event): void
    {
        $this->event = $event;
    }

    #[Computed]
    public function registration()
    {
        if (! Auth::check()) {
            return null;
        }

        return EventUser::where('event_id', $this->event->id)
            ->where('user_id', Auth::id())
            ->first();
    }

    public function eventEdit(): void
    {
        $this->authorize('edit articles');
        $this->dispatch('modal-show', name: 'edit-event-modal');
    }

    #[Computed]
    public function attendees()
    {
        return $this->event->participants()->get();
    }

    #[Computed]
    public function waitingList()
    {
        return $this->event->waitingList()->get();
    }

    public function moveToAttendees(int $userId): void
    {
        $this->event->users()->updateExistingPivot($userId, [
            'in_waitinglist' => false,
        ]);

        Flux::toast(text: 'User moved to attendees.', variant: 'success');
    }

    public function userIsRegistered($eventId): bool
    {
        return EventUser::where('event_id', $eventId)->where('user_id', auth()->id())->exists();
    }

    public function eventIsActive(): bool
    {
        return $this->event->display_starts_at <= now() && $this->event->event_ends_at >= now();
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
            Flux::toast(text: __('You have been unregistered from this event.'), heading: __('Success'), variant: 'success');
            $this->eventIdToUnregister = null;
            $this->modal('unregister-confirmation')->close();
        } else {
            Flux::toast(text: __('Failed to remove your registration.'), heading: __('Error'), variant: 'danger');
        }
    }

    public function registerUser($eventId, RegisterUserToEvent $action)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $event = Event::findOrFail($eventId);

        if ($event->one_hour_periods && $this->period === null) {
            Flux::toast(text: __('Please select an event period.'), heading: __('Error'), variant: 'danger');

            return;
        }

        $alreadyRegistered = EventUser::where('event_id', $eventId)
            ->where('user_id', Auth::id())
            ->exists();

        if ($alreadyRegistered) {
            Flux::toast(text: __('You are already registered for this event.'), heading: __('Error'), variant: 'danger');

            return;
        }

        $action->handle(Auth::user(), $event, $this->period);

        $registration = EventUser::where('event_id', $eventId)
            ->where('user_id', Auth::id())
            ->first();

        if ($registration === null) {
            Flux::toast(text: __('Registration failed. Please try again.'), heading: __('Error'), variant: 'danger');

            return;
        }

        if ($registration->in_waitinglist) {
            Flux::toast(text: __('You have been added to the waiting list.'), heading: __('Success'), variant: 'success');
        } else {
            Flux::toast(text: __('You have been registered for this event.'), heading: __('Success'), variant: 'success');
        }
    }

    public function render(): View
    {
        return view('livewire.events.show', [
            'event' => $this->event])
            ->layout('layouts.app', ['title' => __('Event Show')]);
    }
}
