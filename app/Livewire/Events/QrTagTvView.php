<?php

declare(strict_types=1);

namespace App\Livewire\Events;

use App\Models\Event;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

class QrTagTvView extends Component
{
    public Event $event;

    public function mount(Event $event): void
    {
        $this->event = $event;
    }

    #[Layout('layouts.tv')]
    public function render(): View
    {
        return view('livewire.events.qr-tag-tv-view', [
            'leaderboard' => $this->event->qrTagLeaderboard(),
            'activeCount' => $this->event->qrTagActiveParticipantsCount(),
            'totalCount' => $this->event->participants()->count(),
        ]);
    }
}
