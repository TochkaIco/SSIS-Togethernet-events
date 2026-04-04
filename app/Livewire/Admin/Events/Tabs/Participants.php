<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Events\Tabs;

use App\Models\Event;
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

        /** @var \App\Models\User $user */
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

        /** @var \App\Models\User $user */
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
        /** @var \App\Models\User $participant */
        $participant = $this->event->users()->findOrFail($participantId);

        return (bool) $participant->pivot->is_working;
    }

    public function viewUserProfile(int $participantId)
    {
        $this->authorize('manage users');

        /** @var \App\Models\User $user */
        $user = $this->event->users()->findOrFail($participantId);

        return redirect()->route('admin.event.participant.profile', [$this->event, $user->id]);
    }

    public function updateParticipantWorkingStatus(int $participantId): void
    {
        $this->authorize('manage users');

        /** @var \App\Models\User $participant */
        $participant = $this->event->users()->findOrFail($participantId);
        $this->event->users()->updateExistingPivot($participantId, [
            'is_working' => ! $participant->pivot->is_working,
        ]);

        Flux::toast(__('Worker status updated.'));
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
                $q->wherePivot('has_paid', (bool) $this->filterPaid);
            })
            ->when($this->onlyWorkers, function (Builder $q) {
                $q->where('event_users.is_working', true);
            });

        return view('livewire.admin.events.tabs.participants', [
            'participants' => $query->paginate(10),
        ]);
    }
}
