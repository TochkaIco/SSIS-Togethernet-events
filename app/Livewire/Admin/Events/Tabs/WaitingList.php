<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Events\Tabs;

use App\Models\Event;
use Flux\Flux;
use Livewire\Component;

class WaitingList extends Component
{
    public Event $event;

    public function moveToParticipants($userId): void
    {
        $this->authorize('manage users');

        $this->event->users()->updateExistingPivot($userId, [
            'in_waitinglist' => false,
        ]);

        Flux::toast(__('User moved to participants.'));
    }

    public function viewUserProfile($userId)
    {
        $this->authorize('manage users');

        return redirect()->route('admin.event.participant.profile', [$this->event, $userId]);
    }

    public function render()
    {
        return view('livewire.admin.events.tabs.waiting-list', [
            'waitingList' => $this->event->users()
                ->where('event_users.in_waitinglist', true)
                ->get(),
        ]);
    }
}
