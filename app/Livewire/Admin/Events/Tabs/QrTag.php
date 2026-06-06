<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Events\Tabs;

use App\Actions\ShuffleQrTagTargets;
use App\Models\Event;
use App\Models\GlobalLog;
use App\Models\QrTagLog;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class QrTag extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public $search = '';

    #[Url]
    public $filterRole = '';

    public $filterClassGroup = '';

    public Event $event;

    public ?int $selectedRegistrationId = null;

    public function mount(Event $event): void
    {
        $this->event = $event;
    }

    public function startShuffle(ShuffleQrTagTargets $action): void
    {
        $this->authorize('manage qr-tag');

        if ($this->event->isFinished()) {
            Flux::toast(__('The event has already ended..'), variant: 'danger');

            return;
        }

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

        if ($this->event->isFinished()) {
            Flux::toast(__('The event has already ended..'), variant: 'danger');

            return;
        }

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

    public function respawnPlayer(int $registrationId): void
    {
        $this->authorize('manage qr-tag');

        if ($this->event->isFinished()) {
            Flux::toast(__('The event has already ended..'), variant: 'danger');

            return;
        }

        $registration = $this->event->registrations()->findOrFail($registrationId);

        if ($registration->is_disabled) {
            Flux::toast(__('Cannot respawn a disabled player.'), variant: 'danger');

            return;
        }

        if (! $registration->qr_tag_tagged_at) {
            return;
        }

        // To respawn one player, we insert them into the cycle.
        // We find an active player (not tagged and not disabled) and put the respawned player after them.
        $activePlayers = $this->event->registrations()
            ->whereNull('qr_tag_tagged_at')
            ->where('is_disabled', false)
            ->where('user_id', '!=', $registration->user_id)
            ->get();

        if ($activePlayers->isEmpty()) {
            // If no active players, just treat it like a fresh start for this player
            // But they won't have a target until someone else is respawned or game is shuffled.
            Flux::toast(__('No active players found to attach to. Try a full respawn.'), variant: 'danger');

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
            'type' => 'respawn',
        ]);

        Flux::toast(__('Player respawned and inserted into the cycle.'), variant: 'success');
    }

    public function respawnAll(ShuffleQrTagTargets $action): void
    {
        $this->authorize('manage qr-tag');

        if ($this->event->isFinished()) {
            Flux::toast(__('The event has already ended..'), variant: 'danger');

            return;
        }

        // Respawn all just means reshuffling everyone who is registered.
        $action->handle($this->event, auth()->id(), 'respawn_all', true);

        Flux::toast(__('All players respawned and targets reshuffled.'), variant: 'success');
    }

    public function toggleDisabled(?int $registrationId = null): void
    {
        $this->authorize('manage qr-tag');

        if ($this->event->isFinished()) {
            Flux::toast(__('The event has already ended..'), variant: 'danger');

            return;
        }

        $id = $registrationId ?? $this->selectedRegistrationId;

        if (! $id) {
            return;
        }

        $registration = $this->event->registrations()->findOrFail($id);

        $registration->toggleDisabled();

        $this->selectedRegistrationId = null;

        GlobalLog::log('QR-Tag player has been enabled/disabled by an admin', 'user', ['target_user_id' => $registration->user_id]);
        Flux::toast(__('User status updated.'), variant: 'success');
    }

    public function render()
    {
        $participants = $this->event->participants()
            ->when($this->search || $this->filterRole || $this->filterClassGroup, function ($q) {
                $q->whereHas('user', function ($q) {
                    $q->when($this->search, function ($q) {
                        $q->where(function ($sub) {
                            $sub->where('name', 'like', '%'.$this->search.'%')
                                ->orWhere('email', 'like', '%'.$this->search.'%');
                        });
                    })
                        ->when($this->filterRole, function ($q) {
                            $q->whereHas('roles', function ($q) {
                                $q->where('name', $this->filterRole);
                            });
                        })
                        ->when($this->filterClassGroup, function ($q) {
                            $q->where('class', 'like', $this->filterClassGroup.'%');
                        });
                });
            })
            ->with(['user', 'targetUser', 'taggedBy', 'user.roles'])
            ->paginate(10);

        $logs = $this->event->qrTagLogs()
            ->with(['user', 'targetUser', 'admin'])
            ->take(20)
            ->get();

        return view('livewire.admin.events.tabs.qr-tag', [
            'participants' => $participants,
            'logs' => $logs,
            'allRoles' => Role::all(),
            'allPermissions' => Permission::all(),
            'validClasses' => (new User)->validClasses(),
            'allClassGroups' => [
                'Personal',
                'Alumni',
                'TE'.now()->subMonths(7)->format('y'),
                'TE'.now()->subMonths(7)->subYear()->format('y'),
                'TE'.now()->subMonths(7)->subYears(2)->format('y'),
            ],
        ]);
    }
}
