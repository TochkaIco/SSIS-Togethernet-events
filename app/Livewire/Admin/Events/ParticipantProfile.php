<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Events;

use App\Models\Event;
use App\Models\EventUser;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ParticipantProfile extends Component
{
    #[Locked]
    public Event $event;

    public User $user;

    public function mount(Event $event, $userId): void
    {
        $this->authorize('manage users');

        $this->event = $event;
        $this->loadUser($userId);
    }

    /**
     * Logic separated so it can be called during mount
     * or refreshed if needed.
     */
    protected function loadUser($userId): void
    {
        $participant = EventUser::where('event_id', $this->event->id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $user = User::findOrFail($userId);
        $user->load(['roles', 'permissions']);

        $user->setRelation('pivot', $participant);

        $this->user = $user;
    }

    public function participantIsWorking(): bool
    {
        return (bool) ($this->user->pivot->is_working ?? false);
    }

    public function render(): View
    {
        return view('livewire.admin.events.participant.profile', [
            'lastActivity' => $this->user->last_activity_at,
        ]);
    }
}
