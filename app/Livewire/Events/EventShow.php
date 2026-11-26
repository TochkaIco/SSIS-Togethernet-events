<?php

declare(strict_types=1);

namespace App\Livewire\Events;

use App\Actions\RegisterUserToEvent;
use App\Actions\UnregisterUserFromEvent;
use App\EventType;
use App\Models\Event;
use App\Models\EventUser;
use App\Models\GlobalLog;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

class EventShow extends Component
{
    public Event $event;

    public ?int $period = null;

    public ?int $eventIdToUnregister = null;

    public ?string $selectedSeatLocation = null;

    #[Url]
    public string $tab = 'view';

    public string $qrTagGivenToken = '';

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
            ->with(['eventPeriod'])
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

    public function moveToAttendees(int $registrationId): void
    {
        $registration = $this->event->registrations()->findOrFail($registrationId);
        $registration->update([
            'in_waitinglist' => false,
        ]);

        Flux::toast(text: __('User moved to attendees.'), variant: 'success');
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

    public function unregisterUser(UnregisterUserFromEvent $action)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        if ($this->eventIdToUnregister && $this->userIsRegistered($this->eventIdToUnregister)) {
            $event = Event::findOrFail($this->eventIdToUnregister);

            if (! $event->canUnregister()) {
                Flux::toast(text: __('Registration changes are no longer allowed for this event.'), heading: __('Error'), variant: 'danger');
                $this->modal('unregister-confirmation')->close();

                return;
            }

            $action->handle(Auth::user(), $event);

            Flux::toast(text: __('You have been unregistered from this event.'), heading: __('Success'), variant: 'success');
            $this->eventIdToUnregister = null;
            $this->modal('unregister-confirmation')->close();
            GlobalLog::log('User unregistered from an event', 'user', ['event_id' => $event->id]);
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

        if (! $event->canRegister()) {
            Flux::toast(text: __('Registration is no longer allowed for this event.'), heading: __('Error'), variant: 'danger');

            return;
        }

        $alreadyRegistered = EventUser::where('event_id', $eventId)
            ->where('user_id', Auth::id())
            ->exists();

        if ($alreadyRegistered) {
            Flux::toast(text: __('You are already registered for this event.'), heading: __('Error'), variant: 'danger');

            return;
        }

        if (! $this->event->allowsUser(Auth::user())) {
            Flux::toast(text: __('This event is only open for accounts with an internal domain.'), heading: __('Error'), variant: 'danger');

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

        GlobalLog::log('User registered to an event', 'user', ['event_id' => $event->id]);
    }

    public function openSeatBookingModal()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        if (! $this->event->isLan()) {
            Flux::toast(text: __('Seat booking is only available for LAN events.'), heading: __('Error'), variant: 'danger');

            return;
        }

        $registration = $this->registration();

        if (! $registration instanceof EventUser) {
            Flux::toast(text: __('You are not registered for this event.'), heading: __('Error'), variant: 'danger');

            return;
        }

        if (! $registration->canBookSeat() || ! $this->event->seatBookingOpen()) {
            Flux::toast(text: __('You are not allowed to book a seat for this event.'), heading: __('Error'), variant: 'danger');

            return;
        }

        $this->reset('selectedSeatLocation');
        $this->modal('seat-booking-modal')->show();
    }

    public function bookSeat(string $seatLocation)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        if ($this->event->isFinished()) {
            Flux::toast(text: __('The event has already ended.'), heading: __('Error'), variant: 'danger');

            return;
        }

        $registration = $this->registration();

        if (! $registration instanceof EventUser || ! $registration->canBookSeat() || ! $this->event->seatBookingOpen()) {
            Flux::toast(text: __('You are not allowed to book a seat for this event.'), heading: __('Error'), variant: 'danger');

            return;
        }

        $isSeatChange = $registration->seat_location !== null && $registration->seat_location !== $seatLocation;

        $alreadyTakenByAnother = $this->event->registrations()
            ->where('seat_location', $seatLocation)
            ->where('id', '!=', $registration->id)
            ->exists();

        if ($alreadyTakenByAnother) {
            Flux::toast(text: __('This seat has already been taken.'), heading: __('Error'), variant: 'danger');

            return;
        }

        $previousSeatLocation = $registration->seat_location;

        $registration->update([
            'seat_location' => $seatLocation,
            'seat_booked_at' => now(),
        ]);

        Flux::toast(text: $isSeatChange ? __('Seat changed.') : __('Seat booked.'), heading: __('Success'), variant: 'success');
        $this->reset('selectedSeatLocation');
        $this->modal('seat-booking-modal')->close();
        GlobalLog::log('User booked a seat place for an event', 'user', ['event_id' => $this->event->id, 'seat_changed' => $isSeatChange, 'from' => $previousSeatLocation, 'to' => $seatLocation]);
    }

    /**
     * Select a seat on the seating chart for the authenticated user.
     *
     * This only marks the seat as selected in the UI; the seat is only booked
     * when the save button is pressed.
     */
    public function selectSeat(string $seatLocation): void
    {
        $this->selectedSeatLocation = $seatLocation;
    }

    /**
     * Book the seat selected on the seating chart for the authenticated user.
     */
    public function saveSeatBooking(): void
    {
        if ($this->selectedSeatLocation === null) {
            return;
        }

        $this->bookSeat($this->selectedSeatLocation);
    }

    public function openQrTagTokenModal()
    {
        // Validations

        if (! Auth::check()) {
            return redirect()->route('login');
        }

        if ($this->event->event_type !== EventType::QR_TAG) {
            Flux::toast(text: __('Not a qr-tag event'), heading: __('Error'), variant: 'danger');
        }

        if (! $this->registration() instanceof EventUser) {
            Flux::toast(text: __('You are not registered for this event.'), heading: __('Error'), variant: 'danger');
        }

        $this->modal('qr-tag-token-modal')->show();
    }

    public function openQrTagTokenEntryModal()
    {
        // Validations

        if (! Auth::check()) {
            return redirect()->route('login');
        }

        if ($this->event->event_type !== EventType::QR_TAG) {
            Flux::toast(text: __('Not a qr-tag event'), heading: __('Error'), variant: 'danger');
        }

        if (! $this->registration() instanceof EventUser) {
            Flux::toast(text: __('You are not registered for this event.'), heading: __('Error'), variant: 'danger');
        }

        $this->modal('qr-tag-token-entry-modal')->show();
    }

    public function tagQrTagTargetWithToken()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        if ($this->event->event_type !== EventType::QR_TAG) {
            Flux::toast(text: __('Not a qr-tag event'), heading: __('Error'), variant: 'danger');
        }

        if (! $this->registration() instanceof EventUser) {
            Flux::toast(text: __('You are not registered for this event.'), heading: __('Error'), variant: 'danger');
        }

        if ($this->qrTagGivenToken !== '' && $this->qrTagGivenToken !== '0') {
            if (strlen($this->qrTagGivenToken) !== 32) {
                Flux::toast(text: __('You have entered an incomplete token.'), heading: __('Error'), variant: 'danger');

                return null;
            }

            redirect(route('qr_tag.confirm', ['token' => $this->qrTagGivenToken]));
        }
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        if ($this->event->display_starts_at >= now() || $this->event->is_hidden_event) {
            abort(404);
        }

        return view('livewire.events.show', [
            'event' => $this->event,
        ])
            ->title($this->event->title)
            ->layoutData([
                'embedDescription' => Str::limit($this->event->descriptionWithoutTags(), 146, end: '...', preserveWords: true),
                'embedImagePath' => $this->event->meta_image_path ?? null,
            ]);
    }
}
