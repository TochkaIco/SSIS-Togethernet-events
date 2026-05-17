<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Event;
use App\Models\EventKioskPurchase;
use App\Models\EventUser;
use App\Models\Meeting;
use App\Models\MeetingAttendant;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Dashboard extends Component
{
    #[Computed]
    public function totalUsers(): int
    {
        return User::count();
    }

    #[Computed]
    public function activeEventsCount(): int
    {
        return Event::where('event_starts_at', '<=', now())
            ->where('event_ends_at', '>=', now())
            ->count();
    }

    #[Computed]
    public function upcomingEventsCount(): int
    {
        return Event::where('event_starts_at', '>', now())->count();
    }

    #[Computed]
    public function latestEvent(): ?Event
    {
        return Event::orderBy('event_starts_at', 'desc')->first();
    }

    #[Computed]
    public function latestEventStats(): array
    {
        $latest = $this->latestEvent();

        if (! $latest instanceof Event) {
            return ['registrations' => 0, 'attendance' => 0, 'attendance_rate' => 0, 'class_distribution' => []];
        }

        $registrationsCount = EventUser::where('event_id', $latest->id)
            ->where('in_waitinglist', false)
            ->count();

        $attendanceCount = EventUser::where('event_id', $latest->id)
            ->where('has_arrived', true)
            ->count();

        $userIds = EventUser::where('event_id', $latest->id)
            ->pluck('user_id');

        $classData = User::whereIn('id', $userIds)
            ->selectRaw('class, COUNT(*) as count')
            ->groupBy('class')
            ->get();

        return [
            'registrations' => $registrationsCount,
            'attendance' => $attendanceCount,
            'attendance_rate' => $registrationsCount > 0 ? round(($attendanceCount / $registrationsCount) * 100) : 0,
            'class_distribution' => [
                'labels' => $classData->pluck('class')->map(fn ($c) => $c ?? __('Unknown'))->toArray(),
                'data' => $classData->pluck('count')->toArray(),
                'colors' => $classData->map(fn ($item) => '#'.substr(md5($item->class ?? 'Unknown'), 0, 6))->toArray(),
            ],
        ];
    }

    #[Computed]
    public function totalRevenue(): int
    {
        return (int) EventKioskPurchase::sum('cost');
    }

    #[Computed]
    public function attendanceHistory(): array
    {
        // Only show events that have started, as attendance is 0 for future events
        $events = Event::where('event_starts_at', '<=', now())
            ->orderBy('event_starts_at', 'desc')
            ->take(10)
            ->get()
            ->reverse()
            ->values();

        return [
            'labels' => $events->pluck('title')->toArray(),
            'registrations' => $events->map(fn ($e) => EventUser::where('event_id', $e->id)->where('in_waitinglist', false)->count())->toArray(),
            'attendance' => $events->map(fn ($e) => EventUser::where('event_id', $e->id)->where('has_arrived', true)->count())->toArray(),
            'rates' => $events->map(function ($e): float|int {
                $regs = EventUser::where('event_id', $e->id)->where('in_waitinglist', false)->count();
                $att = EventUser::where('event_id', $e->id)->where('has_arrived', true)->count();

                return $regs > 0 ? round(($att / $regs) * 100) : 0;
            })->toArray(),
        ];
    }

    #[Computed]
    public function monthlyRevenue(): array
    {
        $revenue = EventKioskPurchase::selectRaw('SUM(cost) as total, DATE_FORMAT(created_at, "%Y-%m") as month')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->take(12)
            ->get()
            ->reverse();

        return [
            'labels' => $revenue->pluck('month')->toArray(),
            'data' => $revenue->pluck('total')->toArray(),
        ];
    }

    #[Computed]
    public function userGrowth(): array
    {
        $growthData = User::selectRaw('COUNT(*) as count, DATE_FORMAT(created_at, "%Y-%m-%d") as date')
            ->where('created_at', '>=', now()->subMonths(3))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->pluck('count', 'date');

        $period = now()->subMonths(3)->daysUntil(now());

        $labels = [];
        $data = [];
        $currentTotal = User::where('created_at', '<', now()->subMonths(3))->count();

        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            $count = $growthData->get($formattedDate, 0);
            $currentTotal += $count;

            $labels[] = $formattedDate;
            $data[] = $currentTotal;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    #[Computed]
    public function userClassDistribution(): array
    {
        $distribution = User::selectRaw('class, COUNT(*) as count')
            ->groupBy('class')
            ->get();

        return [
            'labels' => $distribution->pluck('class')->map(fn ($c) => $c ?? __('Unknown'))->toArray(),
            'data' => $distribution->pluck('count')->toArray(),
            'colors' => $distribution->map(fn ($item) => '#'.substr(md5($item->class ?? 'Unknown'), 0, 6))->toArray(),
        ];
    }

    #[Computed]
    public function meetingAttendanceHistory(): array
    {
        $meetings = Meeting::orderBy('meeting_starts_at', 'desc')
            ->take(10)
            ->get()
            ->reverse()
            ->values();

        return [
            'labels' => $meetings->pluck('title')->toArray(),
            'data' => $meetings->map(fn ($m) => MeetingAttendant::where('meeting_id', $m->id)->where('has_attended', true)->count())->toArray(),
            'rates' => $meetings->map(function ($m): float|int {
                $attended = MeetingAttendant::where('meeting_id', $m->id)->where('has_attended', true)->count();

                // Approximate members at that time by counting current members who were created before/on that date
                $totalAtTime = User::whereHas('roles', function ($q) {
                    $q->where('name', 'tog-member');
                })
                    ->where('created_at', '<=', $m->meeting_starts_at)
                    ->count();

                return $totalAtTime > 0 ? round(($attended / $totalAtTime) * 100) : 0;
            })->toArray(),
        ];
    }

    #[Computed]
    public function meetingAttendanceYearly(): array
    {
        // Calculate average attendance per meeting per month
        $meetings = Meeting::where('meeting_starts_at', '>=', now()->subYear())
            ->withCount(['attendants as attended_count' => function ($query) {
                $query->where('has_attended', true);
            }])
            ->get();

        $monthlyData = $meetings->groupBy(fn ($m) => $m->meeting_starts_at->format('Y-m'))
            ->map(fn ($group) => round($group->avg('attended_count'), 1));

        return [
            'labels' => $monthlyData->keys()->toArray(),
            'data' => $monthlyData->values()->toArray(),
        ];
    }

    #[Computed]
    public function meetingDurationHistory(): array
    {
        $meetings = Meeting::whereNotNull('meeting_starts_at')
            ->whereNotNull('meeting_ends_at')
            ->orderBy('meeting_starts_at', 'desc')
            ->take(10)
            ->get()
            ->reverse()
            ->values();

        return [
            'labels' => $meetings->pluck('title')->toArray(),
            'data' => $meetings->map(fn ($m) => $m->meeting_starts_at->diffInMinutes($m->meeting_ends_at))->toArray(),
        ];
    }

    #[Computed]
    public function meetingDurationYearly(): array
    {
        $meetings = Meeting::whereNotNull('meeting_starts_at')
            ->whereNotNull('meeting_ends_at')
            ->where('meeting_starts_at', '>=', now()->subYear())
            ->get();

        $monthlyData = $meetings->groupBy(fn ($m) => $m->meeting_starts_at->format('Y-m'))
            ->map(fn ($group) => round($group->avg(fn ($m) => $m->meeting_starts_at->diffInMinutes($m->meeting_ends_at))));

        return [
            'labels' => $monthlyData->keys()->toArray(),
            'data' => $monthlyData->values()->toArray(),
        ];
    }

    #[Computed]
    public function systemStats(): array
    {
        return [
            'env' => config('app.env'),
            'debug' => config('app.debug'),
            'laravel_version' => app()->version(),
            'failed_jobs' => DB::table('failed_jobs')->count(),
            'timezone' => config('app.timezone'),
            'db_driver' => config('database.default'),
        ];
    }

    public function render(): View
    {
        return view('livewire.admin.dashboard')
            ->layout('layouts.app', ['title' => __('Dashboard')]);
    }
}
