<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Meetings;

use App\Models\Meeting;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Livewire\Component;

class Index extends Component
{
    public function createMeeting()
    {
        if (! auth()->user()->hasRole('tog-member')) {
            abort(403);
        }

        $date = now()->format('Y-m-d');
        $meeting = Meeting::create([
            'title' => 'Togethernet möte '.$date,
            'meeting_starts_at' => now(),
        ]);

        return redirect()->route('admin.meetings.show', $meeting);
    }

    public function render(): Factory|\Illuminate\Contracts\View\View|View
    {
        return view('livewire.admin.meetings.index', [
            'meetings' => Meeting::all(),
        ])->layout('layouts.app', ['title' => 'Meetings']);
    }
}
