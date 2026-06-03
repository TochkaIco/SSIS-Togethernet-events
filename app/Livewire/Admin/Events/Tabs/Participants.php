<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Events\Tabs;

use App\Actions\ProcessWaitingList;
use App\EventType;
use App\Models\Event;
use App\Models\GlobalLog;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class Participants extends Component
{
    use WithPagination;

    public Event $event;

    /**
     * Component properties for filtering and searching.
     */
    public string $search = '';

    public string $filterPaid = ''; // Options: '', '1', '0'

    public bool $onlyWorkers = false;

    public array $participantPeriods = [];

    public string $filterClassGroup = '';

    /**
     * Keep the UI state in the URL for easy sharing/reloading.
     */
    protected $queryString = [
        'search' => ['except' => ''],
        'filterPaid' => ['except' => ''],
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
        $this->authorize('manage users');

        $registration = $this->event->registrations()->findOrFail($registrationId);

        $registration->update([
            'has_paid' => ! $registration->has_paid,
        ]);

        Flux::toast(__('Payment status updated.'), variant: 'success');
    }

    /**
     * Toggle the 'has_arrived' status on the registration.
     */
    public function toggleArrived(int $registrationId): void
    {
        $this->authorize('manage users');

        $registration = $this->event->registrations()->findOrFail($registrationId);

        $registration->update([
            'has_arrived' => ! $registration->has_arrived,
        ]);

        Flux::toast(__('Arrival status updated.'), variant: 'success');
    }

    /**
     * Toggle the 'is_disabled' status on the registration.
     */
    public function toggleDisabled(int $registrationId): void
    {
        $this->authorize('manage users');

        if ($this->event->event_type !== EventType::QR_TAG) {
            Flux::toast(__('You cannot disable a player outside qr-tag.'), variant: 'danger');

            return;
        }

        $registration = $this->event->registrations()->findOrFail($registrationId);

        $registration->toggleDisabled();

        GlobalLog::log('Event participant has been enabled/disabled by an admin', 'user', ['target_user_id' => $registration->user_id]);
        Flux::toast(__('User status updated.'), variant: 'success');
    }

    /**
     * Move a participant to the waiting list.
     */
    public function moveToWaitingList(int $registrationId, ProcessWaitingList $processAction): void
    {
        $this->authorize('manage users');

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
    }

    public function participantIsWorking(int $registrationId): bool
    {
        $registration = $this->event->registrations()->findOrFail($registrationId);

        return (bool) $registration->is_working;
    }

    public function viewUserProfile(int $registrationId)
    {
        $this->authorize('manage users');

        $registration = $this->event->registrations()->with('user')->findOrFail($registrationId);

        return redirect()->route('admin.event.participant.profile', [$this->event, $registration->user->id]);
    }

    public function updateParticipantWorkingStatus(int $registrationId): void
    {
        $this->authorize('manage users');

        $registration = $this->event->registrations()->findOrFail($registrationId);
        $registration->update([
            'is_working' => ! $registration->is_working,
        ]);

        Flux::toast(__('Worker status updated.'), variant: 'success');
    }

    public function changePeriod(int $registrationId, ProcessWaitingList $processAction): void
    {
        $this->authorize('manage users');

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
    }

    /**
     * Render the component with filtered results.
     */
    public function render()
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
                'TE'.now()->subMonths(7)->format('y'),
                'TE'.now()->subMonths(7)->subYear()->format('y'),
                'TE'.now()->subMonths(7)->subYears(2)->format('y'),
            ],
        ]);
    }
}
