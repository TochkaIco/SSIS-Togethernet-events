<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Events\Tabs;

use App\Models\Event;
use App\Models\User;
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

    /**
     * Toggle the 'has_paid' status on the pivot table.
     */
    public function togglePaid(int $userId): void
    {
        $this->authorize('manage users');

        /** @var User $user */
        $user = $this->event->users()->findOrFail($userId);

        $this->event->users()->updateExistingPivot($userId, [
            'has_paid' => ! $user->pivot->has_paid,
        ]);

        Flux::toast(__('Payment status updated.'));
    }

    /**
     * Toggle the 'has_arrived' status on the pivot table.
     */
    public function toggleArrived(int $userId): void
    {
        $this->authorize('manage users');

        /** @var User $user */
        $user = $this->event->users()->findOrFail($userId);

        $this->event->users()->updateExistingPivot($userId, [
            'has_arrived' => ! $user->pivot->has_arrived,
        ]);

        Flux::toast(__('Arrival status updated.'));
    }

    /**
     * Move a participant to the waiting list by flipping the pivot flag.
     */
    public function moveToWaitingList(int $userId): void
    {
        $this->authorize('manage users');

        $this->event->users()->updateExistingPivot($userId, [
            'in_waitinglist' => true,
        ]);

        Flux::toast(__('Moved to waiting list.'));
    }

    public function participantIsWorking(int $participantId): bool
    {
        /** @var User $participant */
        $participant = $this->event->users()->findOrFail($participantId);

        return (bool) $participant->pivot->is_working;
    }

    public function viewUserProfile(int $participantId)
    {
        $this->authorize('manage users');

        /** @var User $user */
        $user = $this->event->users()->findOrFail($participantId);

        return redirect()->route('admin.event.participant.profile', [$this->event, $user->id]);
    }

    public function updateParticipantWorkingStatus(int $participantId): void
    {
        $this->authorize('manage users');

        /** @var User $participant */
        $participant = $this->event->users()->findOrFail($participantId);
        $this->event->users()->updateExistingPivot($participantId, [
            'is_working' => ! $participant->pivot->is_working,
        ]);

        Flux::toast(__('Worker status updated.'));
    }

    public function changePeriod(int $userId): void
    {
        $this->authorize('manage users');

        $period = $this->participantPeriods[$userId] ?? null;

        if ($period === null) {
            return;
        }

        $this->event->users()->updateExistingPivot($userId, [
            'period' => (int) $period,
        ]);

        Flux::toast(__('Period updated.'));
    }

    /**
     * Render the component with filtered results.
     */
    public function render()
    {
        $query = $this->event->users()
            ->where(function ($q) {
                $q->where('event_users.in_waitinglist', false)
                    ->orWhereNull('in_waitinglist');
            })
            ->when($this->search, function (Builder $q) {
                $q->where(function (Builder $sub) {
                    $sub->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filterPaid !== '', function (Builder $q) {
                $q->where('event_users.has_paid', (bool) $this->filterPaid);
            })
            ->when($this->onlyWorkers, function (Builder $q) {
                $q->where('event_users.is_working', true);
            })
            ->when($this->filterClassGroup, function ($q) {
                $q->where('class', 'like', $this->filterClassGroup.'%');
            });

        $participants = $query->paginate(10);

        foreach ($participants as $participant) {
            if (! isset($this->participantPeriods[$participant->id])) {
                $this->participantPeriods[$participant->id] = $participant->pivot->period;
            }
        }

        return view('livewire.admin.events.tabs.participants', [
            'participants' => $participants,
            'allClassGroups' => [
                'Personal',
                'TE'.now()->subMonths(6)->format('y'),
                'TE'.now()->subMonths(6)->subYear()->format('y'),
                'TE'.now()->subMonths(6)->subYears(2)->format('y'),
            ],
        ]);
    }
}
