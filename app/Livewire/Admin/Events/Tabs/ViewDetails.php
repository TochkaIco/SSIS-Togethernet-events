<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Events\Tabs;

use App\Models\Event;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ViewDetails extends Component
{
    public Event $event;

    #[Computed]
    public function stats(): array
    {
        $registrationsCount = $this->event->participants()->count();

        $userIds = $this->event->participants()
            ->pluck('user_id');

        $classData = User::whereIn('id', $userIds)
            ->selectRaw('class, COUNT(*) as count')
            ->groupBy('class')
            ->get();

        $registrationTimes = $this->event->participants()
            ->oldest('created_at')
            ->pluck('created_at')
            ->map(fn ($date) => $date->getTimestamp() * 1000)
            ->toArray();

        return [
            'registrations' => $registrationsCount,
            'class_distribution' => [
                'labels' => $classData->pluck('class')->map(fn ($c) => $c ?? __('Unknown'))->toArray(),
                'data' => $classData->pluck('count')->toArray(),
                'colors' => $classData->map(fn ($item) => '#'.substr(md5($item->class ?? 'Unknown'), 0, 6))->toArray(),
            ],
            'registration_timeline' => [
                'times' => $registrationTimes,
                'event_start' => $this->event->event_starts_at->getTimestamp() * 1000,
                'event_created' => $this->event->created_at->getTimestamp() * 1000,
                'display_starts_at' => $this->event->display_starts_at->getTimestamp() * 1000,
                'event_ends_at' => $this->event->event_ends_at->getTimestamp() * 1000,
            ],
        ];
    }

    public function render()
    {
        return view('livewire.admin.events.tabs.view-details');
    }
}
