<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Meetings;

use App\Models\Meeting;
use Flux\Flux;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Livewire\Component;

class Protocol extends Component
{
    public Meeting $meeting;

    public $title;

    public $description = '';

    public $meeting_starts_at;

    public $meeting_ends_at;

    public function mount(Meeting $meeting): void
    {
        $this->meeting = $meeting;
        $this->title = $meeting->title;
        $this->description = $meeting->description;
        $this->meeting_starts_at = $meeting->meeting_starts_at->format('Y-m-d\TH:i');
        $this->meeting_ends_at = $meeting->meeting_ends_at ? $meeting->meeting_ends_at->format('Y-m-d\TH:i') : '';
    }

    public function save()
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

        Flux::toast(__('Protocol saved successfully.'), variant: 'success');

        return redirect()->route('admin.meetings.show', $this->meeting);
    }

    public function render(): Factory|\Illuminate\Contracts\View\View|View
    {
        return view('livewire.admin.meetings.protocol')
            ->layout('layouts.app', ['title' => 'Edit Protocol']);
    }
}
