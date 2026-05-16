<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Events\Tabs;

use App\Actions\ShuffleQrTagTargets;
use App\Models\Event;
use App\Models\QrTagLog;
use Flux\Flux;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class QrTag extends Component
{
    use WithPagination;

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

        Flux::toast(__('Targets shuffled and tokens generated.'), variant: 'success');
    }

    public function resetGame(): void
    {
        $this->authorize('manage qr-tag');

        $this->event->registrations()->update([
            'qr_tag_token' => null,
            'qr_tag_target_user_id' => null,
            'qr_tag_tagged_at' => null,
            'qr_tag_tagged_by_user_id' => null,
            'qr_tag_count' => 0,
            'has_arrived' => false,
        ]);

        QrTagLog::create([
            'event_id' => $this->event->id,
            'admin_id' => auth()->id(),
            'type' => 'reset',
        ]);

        Flux::toast(__('Game reset.'), variant: 'success');
    }

    public function rebirthPlayer(int $registrationId): void
    {
        $this->authorize('manage qr-tag');

        $registration = $this->event->registrations()->findOrFail($registrationId);

        if ($registration->is_disabled) {
            Flux::toast(__('Cannot rebirth a disabled player.'), variant: 'danger');

            return;
        }

        if (! $registration->qr_tag_tagged_at) {
            return;
        }

        // To rebirth one player, we insert them into the cycle.
        // We find an active player (not tagged and not disabled) and put the rebirthed player after them.
        $activePlayers = $this->event->registrations()
            ->whereNull('qr_tag_tagged_at')
            ->where('is_disabled', false)
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
            'qr_tag_token' => Str::random(32),
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

        Flux::toast(__('Player rebirthed and inserted into the cycle.'), variant: 'success');
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

        Flux::toast(__('All players rebirthed and targets reshuffled.'), variant: 'success');
    }

    public function toggleDisabled(int $registrationId): void
    {
        $this->authorize('manage users');

        $registration = $this->event->registrations()->findOrFail($registrationId);

        $registration->toggleDisabled();

        Flux::toast(__('User status updated.'), variant: 'success');
    }

    public function render()
    {
        $participants = $this->event->participants()
            ->with(['user', 'targetUser', 'taggedBy'])
            ->paginate(10);

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
