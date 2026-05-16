<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Events\Tabs;

use App\Actions\ShuffleQrTagTargets;
use App\Models\Event;
use App\Models\QrTagLog;
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
        $this->authorize('manage qr-tag');

        if ($this->event->isQrTagGameStarted()) {
            Flux::toast(__('Game already started.'), variant: 'danger');

            return;
        }

        $action->handle($this->event, auth()->id());

        Flux::toast(__('Targets shuffled and tokens generated.'));
    }

    public function resetGame(): void
    {
        $this->authorize('manage qr-tag');

        $this->event->registrations()->update([
            'qr_tag_token' => null,
            'qr_tag_target_user_id' => null,
            'qr_tag_tagged_at' => null,
            'qr_tag_tagged_by_user_id' => null,
        ]);

        QrTagLog::create([
            'event_id' => $this->event->id,
            'admin_id' => auth()->id(),
            'type' => 'reset',
        ]);

        Flux::toast(__('Game reset.'));
    }

    public function rebirthPlayer(int $registrationId): void
    {
        $this->authorize('manage qr-tag');

        $registration = $this->event->registrations()->findOrFail($registrationId);

        if (! $registration->qr_tag_tagged_at) {
            return;
        }

        // To rebirth one player, we insert them into the cycle.
        // We find an active player (not tagged) and put the rebirthed player after them.
        $activePlayers = $this->event->registrations()
            ->whereNull('qr_tag_tagged_at')
            ->where('user_id', '!=', $registration->user_id)
            ->get();

        if ($activePlayers->isEmpty()) {
            // If no active players, just treat it like a fresh start for this player
            // But they won't have a target until someone else is rebirthed or game is shuffled.
            Flux::toast(__('No active players found to attach to. Try a full rebirth.'), variant: 'danger');

            return;
        }

        $host = $activePlayers->random();
        $oldTargetId = $host->qr_tag_target_user_id;

        $registration->update([
            'qr_tag_tagged_at' => null,
            'qr_tag_tagged_by_user_id' => null,
            'qr_tag_target_user_id' => $oldTargetId,
        ]);

        $host->update([
            'qr_tag_target_user_id' => $registration->user_id,
        ]);

        QrTagLog::create([
            'event_id' => $this->event->id,
            'user_id' => $registration->user_id,
            'admin_id' => auth()->id(),
            'type' => 'rebirth',
        ]);

        Flux::toast(__('Player rebirthed and inserted into the cycle.'));
    }

    public function rebirthAll(ShuffleQrTagTargets $action): void
    {
        $this->authorize('manage qr-tag');

        // Rebirth all just means reshuffling everyone who is registered.
        $action->handle($this->event, auth()->id());

        QrTagLog::create([
            'event_id' => $this->event->id,
            'admin_id' => auth()->id(),
            'type' => 'rebirth_all',
        ]);

        Flux::toast(__('All players rebirthed and targets reshuffled.'));
    }

    public function render()
    {
        $participants = $this->event->participants()
            ->with(['user', 'targetUser', 'taggedBy'])
            ->get();

        $logs = $this->event->qrTagLogs()
            ->with(['user', 'targetUser', 'admin'])
            ->take(20)
            ->get();

        return view('livewire.admin.events.tabs.qr-tag', [
            'participants' => $participants,
            'logs' => $logs,
        ]);
    }
}
