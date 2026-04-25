<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Meetings;

use App\Models\Meeting;
use App\Models\MeetingAttendant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Livewire\Component;

class Show extends Component
{
    public Meeting $meeting;

    public array $selectedClasses = [];

    public array $attendance = [];

    public function mount(Meeting $meeting): void
    {
        $this->meeting = $meeting;

        $attendedIds = MeetingAttendant::where('meeting_id', $this->meeting->id)
            ->where('has_attended', true)
            ->pluck('attendant_id')
            ->toArray();

        // Pre-initialize attendance for all members to ensure stable wire:model binding
        $this->attendance = User::role('tog-member')
            ->pluck('id')
            ->mapWithKeys(fn ($id) => [(string) $id => in_array($id, $attendedIds)])
            ->toArray();
    }

    public function endMeeting(): void
    {
        if (! auth()->user()->hasRole('tog-member')) {
            abort(403);
        }

        $this->meeting->update(['meeting_ends_at' => now()]);
        $this->meeting->refresh();
    }

    public function updatedAttendance($value, $key): void
    {
        $this->authorize('take attendance');

        MeetingAttendant::updateOrCreate(
            ['meeting_id' => $this->meeting->id, 'attendant_id' => $key],
            ['has_attended' => (bool) $value]
        );
    }

    public function toggleAttendance($userId): void
    {
        $this->authorize('take attendance');

        $this->attendance[(string) $userId] = ! ($this->attendance[(string) $userId] ?? false);

        $this->updatedAttendance($this->attendance[(string) $userId], (string) $userId);
    }

    public function deleteMeeting(): void
    {
        if (! auth()->user()->hasAnyRole(['admin', 'super-admin', 'maintainer'])) {
            abort(403);
        } elseif (Carbon::now()->greaterThan($this->meeting->meeting_starts_at->addMinutes(20))) {
            abort(403, 'This action is no longer available.');
        }

        $this->meeting->delete();
        $this->redirectRoute('admin.meetings.index');
    }

    public function confirmDelete(): void
    {
        if (! auth()->user()->hasAnyRole(['admin', 'super-admin', 'maintainer'])) {
            abort(403);
        }

        $this->modal('confirm-meeting-deletion')->show();
    }

    public function render(): Factory|\Illuminate\Contracts\View\View|View
    {
        $query = User::role('tog-member');

        if ($this->selectedClasses !== []) {
            $query->whereIn('class', $this->selectedClasses);
        }

        $users = $query->get();

        $allClasses = User::role('tog-member')
            ->whereNotNull('class')
            ->distinct()
            ->pluck('class')
            ->sort()
            ->values()
            ->toArray();

        $attendedUserIds = MeetingAttendant::where('meeting_id', $this->meeting->id)
            ->where('has_attended', true)
            ->pluck('attendant_id')
            ->toArray();

        return view('livewire.admin.meetings.show', [
            'users' => $users,
            'allClasses' => $allClasses,
            'attendedUserIds' => $attendedUserIds,
        ])->layout('layouts.app', ['title' => 'Meeting Details']);
    }
}
