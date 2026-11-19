<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Events\Tabs;

use App\Actions\ProcessWaitingList;
use App\EventType;
use App\Models\Event;
use App\Models\EventUser;
use App\Models\GlobalLog;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Participants extends Component
{
    use WithPagination;

    public Event $event;

    /**
     * Component properties for filtering and searching.
     */
    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $filterPaid = ''; // Options: '', '1', '0'

    public bool $onlyWorkers = false;

    public array $participantPeriods = [];

    public string $filterClassGroup = '';

    public ?int $seatBookingRegistrationId = null;

    public ?string $selectedSeatLocation = null;

    public ?int $registrationIdToRemove = null;

    /**
     * Keep the UI state in the URL for easy sharing/reloading.
     */
    protected $queryString = [
        'onlyWorkers' => ['except' => false],
    ];

    /**
     * Reset pagination when search or filters change.
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterPaid(): void
    {
        $this->resetPage();
    }

    public function updatingOnlyWorkers(): void
    {
        $this->resetPage();
    }

    public function updatingFilterClassGroup(): void
    {
        $this->resetPage();
    }

    /**
     * Toggle the 'has_paid' status on the registration.
     */
    public function togglePaid(int $registrationId): void
    {
        $this->authorize('manage participants');

        if ($this->event->hasFinishedOverAnHourAgo()) {
            Flux::toast(__('The event has already ended..'), variant: 'danger');

            return;
        }

        $registration = $this->event->registrations()->findOrFail($registrationId);

        $registration->update([
            'has_paid' => ! $registration->has_paid,
        ]);

        Flux::toast(__('Payment status updated.'), variant: 'success');
        GlobalLog::log('Participant\'s payment status updated', 'user', ['event_id' => $registration->event->id, 'event_title' => $registration->event->slug, 'target_user_id' => $registration->user_id, 'has_paid' => $registration->has_paid]);
    }

    /**
     * Toggle the 'has_arrived' status on the registration.
     */
    public function toggleArrived(int $registrationId): void
    {
        $this->authorize('manage participants');

        if ($this->event->hasFinishedOverAnHourAgo()) {
            Flux::toast(__('The event has already ended..'), variant: 'danger');

            return;
        }

        $registration = $this->event->registrations()->findOrFail($registrationId);

        $registration->update([
            'has_arrived' => ! $registration->has_arrived,
        ]);

        Flux::toast(__('Arrival status updated.'), variant: 'success');
        GlobalLog::log('Toggled arrival on participant', 'user', ['event_id' => $registration->event->id, 'event_title' => $registration->event->slug, 'target_user_id' => $registration->user_id, 'has_arrived' => $registration->has_arrived]);
    }

    /**
     * Toggle the 'is_without_seat_place' status on the registration.
     */
    public function toggleIsWithoutSeatPlace(int $registrationId): void
    {
        $this->authorize('manage participants');

        if ($this->event->hasFinishedOverAnHourAgo()) {
            Flux::toast(__('The event has already ended..'), variant: 'danger');

            return;
        }

        $registration = $this->event->registrations()->findOrFail($registrationId);

        $withoutSeatPlace = ! $registration->is_without_seat_place;

        $registration->update([
            'is_without_seat_place' => $withoutSeatPlace,
            // A participant without a seat place can no longer hold a seat on the chart.
            'seat_location' => $withoutSeatPlace ? null : $registration->seat_location,
        ]);

        Flux::toast(__('Seat place status updated.'), variant: 'success');
        GlobalLog::log('Disabled seat places for participant', 'user', ['event_id' => $registration->event->id, 'event_title' => $registration->event->slug, 'target_user_id' => $registration->user_id, 'seating is disabled' => $registration->is_without_seat_place, 'seat_location' => $registration->seat_location]);
    }

    /**
     * Open the seat booking modal for the given participant (LAN events only).
     */
    public function openSeatBookingModal(int $registrationId): void
    {
        $this->authorize('manage participants');

        if (! $this->event->isLan()) {
            Flux::toast(__('Seat booking is only available for LAN events.'), variant: 'danger');

            return;
        }

        if ($this->event->hasFinishedOverAnHourAgo()) {
            Flux::toast(__('The event has already ended.'), variant: 'danger');

            return;
        }

        $registration = $this->event->registrations()->findOrFail($registrationId);

        if (! $registration->canBookSeat()) {
            Flux::toast(__('This participant is not allowed to book a seat.'), variant: 'danger');

            return;
        }

        $this->seatBookingRegistrationId = $registrationId;
        $this->reset('selectedSeatLocation');
        $this->modal('seat-booking-modal')->show();
    }

    /**
     * Book a seat place for a participant on the LAN seating chart.
     */
    public function bookSeat(int $registrationId, string $seatLocation): void
    {
        $this->authorize('manage participants');

        if (! $this->event->isLan()) {
            Flux::toast(__('Seat booking is only available for LAN events.'), variant: 'danger');

            return;
        }

        if ($this->event->hasFinishedOverAnHourAgo()) {
            Flux::toast(__('The event has already ended.'), variant: 'danger');

            return;
        }

        $registration = $this->event->registrations()->findOrFail($registrationId);

        if (! $registration->canBookSeat()) {
            Flux::toast(__('This participant is not allowed to book a seat.'), variant: 'danger');

            return;
        }

        if ($registration->seat_location !== null && $registration->seat_location !== $seatLocation) {
            $registration->update([
                'seat_location' => null,
            ]);
        }

        $alreadyTaken = $this->event->registrations()
            ->where('seat_location', $seatLocation)
            ->where('id', '!=', $registration->id)
            ->exists();

        if ($alreadyTaken) {
            Flux::toast(__('This seat place is already taken by another participant.'), variant: 'danger');

            return;
        }

        $registration->update([
            'seat_location' => $seatLocation,
        ]);

        Flux::toast(__('Seat booked.'), variant: 'success');
        GlobalLog::log('Seat place booked for participant', 'user', ['event_id' => $registration->event->id, 'event_title' => $registration->event->slug, 'target_user_id' => $registration->user_id, 'seat_location' => $registration->seat_location]);
        $this->reset('selectedSeatLocation');
        $this->modal('seat-booking-modal')->close();
    }

    /**
     * Select a seat on the seating chart for the participant the seat booking modal is open for.
     *
     * This only marks the seat as selected in the UI; the seat is only booked
     * when the save button is pressed.
     */
    public function selectSeat(string $seatLocation): void
    {
        if ($this->seatBookingRegistrationId === null) {
            return;
        }

        $this->selectedSeatLocation = $seatLocation;
    }

    /**
     * Book the seat selected on the seating chart for the participant the seat booking modal is open for.
     */
    public function saveSeatBooking(): void
    {
        if ($this->selectedSeatLocation === null || $this->seatBookingRegistrationId === null) {
            return;
        }

        $this->bookSeat($this->seatBookingRegistrationId, $this->selectedSeatLocation);
    }

    /**
     * Open the confirmation modal before removing a participant from the event.
     */
    public function confirmRemoveParticipant(int $registrationId): void
    {
        $this->authorize('manage participants');

        $this->registrationIdToRemove = $registrationId;
        $this->modal('remove-participant-confirmation')->show();
    }

    /**
     * Remove a participant from the event. Only participants without a seat place can be removed.
     */
    public function removeParticipantFromEvent(int $registrationId, ProcessWaitingList $processAction): void
    {
        $this->authorize('manage participants');

        if ($this->event->hasFinishedOverAnHourAgo()) {
            Flux::toast(__('The event has already ended.'), variant: 'danger');

            return;
        }

        $registration = $this->event->registrations()->findOrFail($registrationId);

        if (! $registration->is_without_seat_place) {
            Flux::toast(__('Only participants without a seat place can be removed from the event.'), variant: 'danger');

            return;
        }

        $periodId = $registration->event_period_id;
        $targetUserId = $registration->user_id;

        $registration->delete();

        $processAction->handle($this->event, $periodId);

        Flux::toast(__('Participant has been removed from the event.'), variant: 'success');
        GlobalLog::log('Participant removed from event', 'user', ['event_id' => $registration->event->id, 'event_title' => $registration->event->slug, 'target_user_id' => $targetUserId]);

        $this->modal('remove-participant-confirmation')->close();
    }

    /**
     * The participant the seat booking modal is open for.
     */
    public function seatBookingParticipant(): ?EventUser
    {
        if ($this->seatBookingRegistrationId === null) {
            return null;
        }

        return $this->event->registrations()->with('user')->find($this->seatBookingRegistrationId);
    }

    /**
     * The participant the remove-from-event confirmation modal is open for.
     */
    public function participantToRemove(): ?EventUser
    {
        if ($this->registrationIdToRemove === null) {
            return null;
        }

        return $this->event->registrations()->with('user')->find($this->registrationIdToRemove);
    }

    /**
     * Toggle the 'is_disabled' status on the registration.
     */
    public function toggleDisabled(int $registrationId): void
    {
        $this->authorize('manage participants');

        if ($this->event->isFinished()) {
            Flux::toast(__('The event has already ended..'), variant: 'danger');

            return;
        }

        if ($this->event->event_type !== EventType::QR_TAG) {
            Flux::toast(__('You cannot disable a player outside qr-tag.'), variant: 'danger');

            return;
        }

        $registration = $this->event->registrations()->findOrFail($registrationId);

        $registration->toggleDisabled();

        Flux::toast(__('User status updated.'), variant: 'success');
        GlobalLog::log('Event participant has been enabled/disabled by an admin', 'user', ['event_id' => $registration->event->id, 'event_title' => $registration->event->slug, 'target_user_id' => $registration->user_id, 'is_disabled' => $registration->is_disabled]);
    }

    /**
     * Move a participant to the waiting list.
     */
    public function moveToWaitingList(int $registrationId, ProcessWaitingList $processAction): void
    {
        $this->authorize('manage participants');

        if ($this->event->isFinished()) {
            Flux::toast(__('The event has already ended..'), variant: 'danger');

            return;
        }

        if ($this->event->event_type === EventType::QR_TAG) {
            Flux::toast(__('Waiting list is not supported for QR-Tag events.'), variant: 'danger');

            return;
        }

        $registration = $this->event->registrations()->findOrFail($registrationId);
        $periodId = $registration->event_period_id;

        $registration->update([
            'in_waitinglist' => true,
            'created_at' => now(),
        ]);

        $processAction->handle($this->event, $periodId);

        Flux::toast(__('Moved to waiting list.'), variant: 'success');
        GlobalLog::log('Moved participant to waiting list', 'user', ['event_id' => $registration->event->id, 'event_title' => $registration->event->slug, 'target_user_id' => $registration->user_id]);
    }

    public function participantHasASeat(int $registrationId): bool
    {
        $registration = $this->event->registrations()->findOrFail($registrationId);

        return ! $registration->is_without_seat_place;
    }

    public function participantIsWorking(int $registrationId): bool
    {
        $registration = $this->event->registrations()->findOrFail($registrationId);

        return (bool) $registration->is_working;
    }

    public function viewUserProfile(int $registrationId)
    {
        $this->authorize('manage participants');

        $registration = $this->event->registrations()->with('user')->findOrFail($registrationId);

        return redirect()->route('admin.event.participant.profile', [$this->event, $registration->user->id]);
    }

    public function updateParticipantWorkingStatus(int $registrationId): void
    {
        $this->authorize('manage participants');

        if ($this->event->hasFinishedOverAnHourAgo()) {
            Flux::toast(__('The event has already ended..'), variant: 'danger');

            return;
        }

        $registration = $this->event->registrations()->findOrFail($registrationId);
        $registration->update([
            'is_working' => ! $registration->is_working,
        ]);

        Flux::toast(__('Worker status updated.'), variant: 'success');
        GlobalLog::log('Worker status updated for event participant', 'user', ['event_id' => $registration->event->id, 'event_title' => $registration->event->slug, 'target_user_id' => $registration->user_id, 'is_working' => $registration->is_working]);
    }

    public function changePeriod(int $registrationId, ProcessWaitingList $processAction): void
    {
        $this->authorize('manage participants');

        if ($this->event->isFinished()) {
            Flux::toast(__('The event has already ended..'), variant: 'danger');

            return;
        }

        $periodId = $this->participantPeriods[$registrationId] ?? null;

        if ($periodId === null) {
            return;
        }

        $registration = $this->event->registrations()->findOrFail($registrationId);
        $oldPeriodId = $registration->event_period_id;

        $registration->update([
            'event_period_id' => (int) $periodId,
        ]);

        if ($oldPeriodId !== (int) $periodId) {
            $processAction->handle($this->event, $oldPeriodId);
        }

        Flux::toast(__('Period updated.'), variant: 'success');
        GlobalLog::log('Event period updated for a participant', 'user', ['event_id' => $registration->event->id, 'event_title' => $registration->event->slug, 'target_user_id' => $registration->user_id, 'selected_period' => $registration->event_period_id]);
    }

    /**
     * Render the component with filtered results.
     */
    public function render(): View
    {
        $query = $this->event->participants()
            ->with('user')
            ->whereHas('user', function (Builder $q) {
                $q->when($this->search, function (Builder $q) {
                    $q->where(function (Builder $sub) {
                        $sub->where('name', 'like', '%'.$this->search.'%')
                            ->orWhere('email', 'like', '%'.$this->search.'%');
                    });
                })
                    ->when($this->filterClassGroup, function ($q) {
                        $q->where('class', 'like', $this->filterClassGroup.'%');
                    });
            })
            ->when($this->filterPaid !== '', function (Builder $q) {
                $q->where('has_paid', (bool) $this->filterPaid);
            })
            ->when($this->onlyWorkers, function (Builder $q) {
                $q->where('is_working', true);
            });

        $participants = $query->paginate(10);

        foreach ($participants as $participant) {
            if (! isset($this->participantPeriods[$participant->id])) {
                $this->participantPeriods[$participant->id] = $participant->event_period_id;
            }
        }

        return view('livewire.admin.events.tabs.participants', [
            'participants' => $participants,
            'allClassGroups' => [
                'Personal',
                'Alumni',
                'TE'.now()->subMonths(6)->addDays(20)->format('y'),
                'TE'.now()->subMonths(6)->addDays(20)->subYear()->format('y'),
                'TE'.now()->subMonths(6)->addDays(20)->subYears(2)->format('y'),
            ],
        ]);
    }
}
