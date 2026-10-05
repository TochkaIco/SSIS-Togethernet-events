<?php

declare(strict_types=1);

namespace App\Livewire\Events;

use App\EventType;
use App\Models\Event;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

class TvView extends Component
{
    public Event $event;

    public function mount(Event $event): void
    {
        $this->event = $event;
    }

    #[Layout('layouts.tv')]
    public function render(): View
    {
        if ($this->event->event_starts_at <= now() && $this->event->event_type === EventType::QR_TAG) {
            return view('livewire.events.qr-tag-tv-view', [
                'leaderboard' => $this->event->qrTagLeaderboard(),
                'activeCount' => $this->event->qrTagActiveParticipantsCount(),
                'totalCount' => $this->event->participants()->count(),
            ]);
        }

        $daysLeft = (int) now()->startOfDay()->diffInDays($this->event->event_starts_at->startOfDay(), false);

        if ($daysLeft >= 0) {
            return view('livewire.events.countdown', [
                'daysLeft' => $daysLeft,
            ]);
        }

        $this->redirect(route('home'), navigate: true);

        return view('livewire.events.countdown', ['daysLeft' => 0]);
    }
}
