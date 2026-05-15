<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Events\Tabs;

use App\Actions\ShuffleQrTagTargets;
use App\Models\Event;
use Flux\Flux;
use Livewire\Component;

class QrTag extends Component
{
    public Event $event;

    public function mount(Event $event): void
    {
        $this->event = $event;
    }

    public function startShuffle(ShuffleQrTagTargets $action): void
    {
        $this->authorize('manage users');

        $action->handle($this->event);

        Flux::toast(__('Targets shuffled and tokens generated.'));
    }

    public function resetGame(): void
    {
        $this->authorize('manage users');

        $this->event->registrations()->update([
            'qr_tag_token' => null,
            'qr_tag_target_user_id' => null,
            'qr_tag_tagged_at' => null,
            'qr_tag_tagged_by_user_id' => null,
        ]);

        Flux::toast(__('Game reset.'));
    }

    public function render()
    {
        $participants = $this->event->participants()
            ->with(['user', 'targetUser', 'taggedBy'])
            ->get();

        return view('livewire.admin.events.tabs.qr-tag', [
            'participants' => $participants,
        ]);
    }
}
