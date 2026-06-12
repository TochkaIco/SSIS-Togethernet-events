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

        $attendanceCount = $this->event->participants()
            ->where('has_arrived', true)
            ->count();

        $userIds = $this->event->participants()
            ->pluck('user_id');

        $classData = User::whereIn('id', $userIds)
            ->selectRaw('class, COUNT(*) as count')
            ->groupBy('class')
            ->get();

        return [
            'registrations' => $registrationsCount,
            'attendance' => $attendanceCount,
            'attendance_rate' => $registrationsCount > 0 ? (int) round(($attendanceCount / $registrationsCount) * 100) : 0,
            'class_distribution' => [
                'labels' => $classData->pluck('class')->map(fn ($c) => $c ?? __('Unknown'))->toArray(),
                'data' => $classData->pluck('count')->toArray(),
                'colors' => $classData->map(fn ($item) => '#'.substr(md5($item->class ?? 'Unknown'), 0, 6))->toArray(),
            ],
        ];
    }

    public function render()
    {
        return view('livewire.admin.events.tabs.view-details');
    }
}
