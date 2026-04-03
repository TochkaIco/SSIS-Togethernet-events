<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Events;

use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ParticipantProfile extends Component
{
    public Event $event;

    public User $user;

    public function mount(Event $event, User $user): void
    {
        $this->authorize('manage users');
        $this->event = $event;
        $this->user = $this->event->users()->findOrFail($user->id)->load(['roles', 'permissions']);
    }

    public function participantIsWorking(): bool
    {
        return (bool) $this->user->pivot->is_working;
    }

    public function render(): View
    {
        $lastActivity = $this->user->sessions()->orderByDesc('last_activity')->first()?->last_activity;

        return view('livewire.admin.events.participant.profile', [
            'lastActivity' => $lastActivity ? Carbon::createFromTimestamp($lastActivity) : null,
        ]);
    }
}
