<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Meetings;

use App\Jobs\BackupMeetingToGoogleDrive;
use App\Models\Meeting;
use Flux\Flux;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Livewire\Component;

class Protocol extends Component
{
    public Meeting $meeting;

    public $title;

    public $description;

    public $meeting_starts_at;

    public $meeting_ends_at;

    public function mount(Meeting $meeting): void
    {
        $this->meeting = $meeting;
        $this->title = $meeting->title;
        $this->description = $meeting->description ?: '<p><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAIAAAAAVCAYAAACOleY7AAAAAXNSR0IB2cksfwAAAARnQU1BAACxjwv8YQUAAAAgY0hSTQAAeiYAAICEAAD6AAAAgOgAAHUwAADqYAAAOpgAABdwnLpRPAAAAAlwSFlzAAAuIwAALiMBeKU/dgAAAUpJREFUaN7tWUEOhCAMtMbXedqn7mm/1z2ZECIwLVWKdk5msetAZTpF4u/OixD0+dHiD+k8qOOeJ6I473UJvBrxArwcpzKYlgWnch8lIEpAwAKb6nU6MY4tpSjFHL8X4hlQLQbiEEVAVAHhk95HlWfU7rHi0lybrTfx+VieSCQGnNzZ4lrJYuu/tXxYyeMKLmECAx0lIN+t6U5Px/i78zGGxoCyXDIyrXHUBHN2TUo+sNm+gUuTq0oBcplHOgVNTGdypR3QVXzIEZcpS4C31s0Tn24u24TJH5kAT3xMuKgUIK/fyPcETUxjghpnbQW66TmXr43oJBBJmqQNrMSxYOJI4tEdwoApk5o47amkFZdqXLSB0QbK3b/kJLAWo5Q1GiDNWj53S754bYa62Ek/Oj0Kq4fkB8Y7yKEJj93/QgUITGgCzeUndv5w/AEDsq0U+d2ozAAAAABJRU5ErkJggg==" style="width: 20%; image-rendering: crisp-edges;"></p><p><strong style="background-color: transparent;">Mötesansvarig:</strong></p><p><strong style="background-color: transparent;">Sekreterare:</strong></p><p><strong style="background-color: transparent;">Justerare:</strong></p><p><br></p><p><strong style="background-color: transparent;">Övriga frågor:</strong></p><p><br></p><p><strong style="background-color: transparent;">Saker som ska vidareutvecklas</strong><span style="background-color: transparent;">:</span></p><p><br></p><p><strong style="background-color: transparent;">Uppdrag</strong><span style="background-color: transparent;">:</span></p><p><br></p><p><strong style="background-color: transparent;">Nästa möte</strong><span style="background-color: transparent;">:</span></p>';
        $this->meeting_starts_at = $meeting->meeting_starts_at->format('Y-m-d\TH:i');
        $this->meeting_ends_at = $meeting->meeting_ends_at ? $meeting->meeting_ends_at->format('Y-m-d\TH:i') : '';
    }

    public function save(): void
    {
        if (! auth()->user()->hasRole('tog-member')) {
            abort(403);
        }

        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:4000000',
            'meeting_starts_at' => 'required',
            'meeting_ends_at' => 'nullable|after_or_equal:meeting_starts_at',
        ]);

        $this->meeting->update([
            'title' => $this->title,
            'description' => $this->description,
            'meeting_starts_at' => $this->meeting_starts_at,
            'meeting_ends_at' => $this->meeting_ends_at ?: null,
        ]);

        BackupMeetingToGoogleDrive::dispatch($this->description, $this->title, $this->meeting_starts_at);

        Flux::toast(__('Protocol saved successfully, syncing with Google Drive...'), variant: 'success');
    }

    public function render(): Factory|\Illuminate\Contracts\View\View|View
    {
        return view('livewire.admin.meetings.protocol')
            ->layout('layouts.app', ['title' => 'Edit Protocol']);
    }
}
