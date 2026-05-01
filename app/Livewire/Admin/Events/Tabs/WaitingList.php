<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Events\Tabs;

use App\Models\Event;
use Flux\Flux;
use Livewire\Component;

class WaitingList extends Component
{
    public Event $event;

    public function moveToParticipants($registrationId): void
    {
        $this->authorize('manage users');

        $registration = $this->event->registrations()->findOrFail($registrationId);
        $registration->update([
            'in_waitinglist' => false,
        ]);

        Flux::toast(__('User moved to participants.'));
    }

    public function viewUserProfile($registrationId)
    {
        $this->authorize('manage users');

        $registration = $this->event->registrations()->with('user')->findOrFail($registrationId);

        return redirect()->route('admin.event.participant.profile', [$this->event, $registration->user->id]);
    }

    public function render()
    {
        return view('livewire.admin.events.tabs.waiting-list', [
            'waitingList' => $this->event->waitingList()
                ->with('user')
                ->get(),
        ]);
    }
}
