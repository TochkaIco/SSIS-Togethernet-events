<?php

declare(strict_types=1);

namespace App\Livewire\Events;

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

    #[Url]
    public string $tab = 'view';

    protected $queryString = ['tab'];

    public function mount(Event $event): void
    {
        $this->event = $event;
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

    public function unregisterUser($eventId)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        if ($this->userIsRegistered($eventId)) {
            EventUser::where('event_id', $eventId)->where('user_id', Auth::id())->delete();
            Flux::toast(text: 'You have been unregistered from this event.', heading: 'Success', variant: 'success');
        } else {
            Flux::toast(text: 'Failed to remove your registration.', heading: 'Error', variant: 'danger');
        }
    }

    public function registerUser($eventId)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }
        $alreadyRegistered = EventUser::where('event_id', $eventId)
            ->where('user_id', Auth::id())
            ->exists();
        if ($alreadyRegistered) {
            Flux::toast(text: 'You are already registered for this event.', heading: 'Error', variant: 'danger');

            return;
        }

        EventUser::create([
            'event_id' => $eventId,
            'user_id' => Auth::id(),
        ]);

        Flux::toast(text: 'You have been registered for this event.', heading: 'Success', variant: 'success');
    }

    public function render(): View
    {
        return view('livewire.events.show', [
            'event' => $this->event])
            ->layout('layouts.app', ['title' => __('Event Show')]);
    }
}
