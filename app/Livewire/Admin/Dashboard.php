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
        $events = Event::orderBy('event_starts_at', 'desc')
            ->take(5)
            ->get()
            ->reverse();

        return [
            'labels' => $events->pluck('title')->toArray(),
            'registrations' => $events->map(fn ($e) => EventUser::where('event_id', $e->id)->where('in_waitinglist', false)->count())->toArray(),
            'attendance' => $events->map(fn ($e) => EventUser::where('event_id', $e->id)->where('has_arrived', true)->count())->toArray(),
        ];
    }

    #[Computed]
    public function monthlyRevenue(): array
    {
        $revenue = EventKioskPurchase::selectRaw('SUM(cost) as total, DATE_FORMAT(created_at, "%Y-%m") as month')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->take(6)
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
        $growth = User::selectRaw('COUNT(*) as count, DATE_FORMAT(created_at, "%Y-%m-%d") as date')
            ->where('created_at', '>=', now()->subYear())
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return [
            'labels' => $growth->pluck('date')->toArray(),
            'data' => $growth->pluck('count')->toArray(),
        ];
    }

    #[Computed]
    public function userClassDistribution(): array
    {
        $distribution = User::selectRaw('class, COUNT(*) as count')
            ->groupBy('class')
            ->get();

        return [
            'labels' => $distribution->pluck('class')->toArray(),
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
            ->reverse();

        return [
            'labels' => $meetings->pluck('title')->toArray(),
            'data' => $meetings->map(fn ($m) => MeetingAttendant::where('meeting_id', $m->id)->where('has_attended', true)->count())->toArray(),
        ];
    }

    #[Computed]
    public function meetingAttendanceYearly(): array
    {
        $attendance = MeetingAttendant::selectRaw('COUNT(*) as count, DATE_FORMAT(meetings.meeting_starts_at, "%Y-%m") as month')
            ->join('meetings', 'meetings.id', '=', 'meeting_attendants.meeting_id')
            ->where('meeting_attendants.has_attended', true)
            ->where('meetings.meeting_starts_at', '>=', now()->subYear())
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        return [
            'labels' => $attendance->pluck('month')->toArray(),
            'data' => $attendance->pluck('count')->toArray(),
        ];
    }

    #[Computed]
    public function systemStats(): array
    {
        $storagePath = storage_path('app/public');

        // On some systems like OpenShift, disk_free_space might return false if the path is not accessible or not a mount point
        $diskFree = @disk_free_space($storagePath);
        $diskTotal = @disk_total_space($storagePath);

        // Fallback to root if storage path fails
        if ($diskFree === false || $diskTotal === false) {
            $diskFree = @disk_free_space('/');
            $diskTotal = @disk_total_space('/');
        }

        if ($diskFree !== false && $diskTotal !== false && $diskTotal > 0) {
            $diskUsed = $diskTotal - $diskFree;
            $diskPercentage = (int) round(($diskUsed / $diskTotal) * 100);
            $diskLabel = $this->formatBytes($diskUsed).' / '.$this->formatBytes($diskTotal);
        } else {
            $diskUsed = 0;
            $diskPercentage = 0;
            $diskLabel = __('Unavailable');
        }

        $dbDriver = config('database.default');

        return [
            'env' => config('app.env'),
            'debug' => config('app.debug'),
            'laravel_version' => app()->version(),
            'failed_jobs' => DB::table('failed_jobs')->count(),
            'disk_percentage' => $diskPercentage,
            'disk_label' => $diskLabel,
            'timezone' => config('app.timezone'),
            'db_host' => config('database.connections.'.$dbDriver.'.host'),
        ];
    }

    private function formatBytes(float $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision).' '.$units[$pow];
    }

    public function render(): View
    {
        return view('livewire.admin.dashboard')
            ->layout('layouts.app', ['title' => __('Dashboard')]);
    }
}
