<?php

declare(strict_types=1);

namespace App\Livewire\Events;

use App\EventType;
use App\Models\Event;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
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
    public function render(): View|RedirectResponse|Redirector
    {
        if ($this->event->event_starts_at <= now() && $this->event->event_type === EventType::QR_TAG) {
            return view('livewire.events.qr-tag-tv-view', [
                'leaderboard' => $this->event->qrTagLeaderboard(),
                'activeCount' => $this->event->qrTagActiveParticipantsCount(),
                'totalCount' => $this->event->participants()->count(),
            ]);
        }
        if ($this->event->event_starts_at > now()) {
            $daysLeft = max(0, (int) ceil(now()->diffInSeconds($this->event->event_starts_at, false) / 86400));

            return view('livewire.events.countdown', [
                'daysLeft' => $daysLeft,
            ]);
        }

        return redirect(route('home'));
    }
}
