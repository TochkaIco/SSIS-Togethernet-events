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

    public function mount(Meeting $meeting): void
    {
        $this->meeting = $meeting;
    }

    public function endMeeting(): void
    {
        if (! auth()->user()->hasRole('tog-member')) {
            abort(403);
        }

        $this->meeting->update(['meeting_ends_at' => now()]);
        $this->meeting->refresh();
    }

    public function toggleAttendance($userId): void
    {
        $this->authorize('take attendance');

        $attendant = MeetingAttendant::where('meeting_id', $this->meeting->id)
            ->where('attendant_id', $userId)
            ->first();

        if ($attendant) {
            $attendant->update(['has_attended' => ! $attendant->has_attended]);
        } else {
            MeetingAttendant::create([
                'meeting_id' => $this->meeting->id,
                'attendant_id' => $userId,
                'has_attended' => true,
            ]);
        }
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
