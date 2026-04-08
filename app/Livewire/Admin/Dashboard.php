<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Event;
use App\Models\EventKioskPurchase;
use App\Models\EventUser;
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
            return [];
        }

        $registrationsCount = EventUser::where('event_id', $latest->id)
            ->where('in_waitinglist', false)
            ->count();

        $attendanceCount = EventUser::where('event_id', $latest->id)
            ->where('has_arrived', true)
            ->count();

        return [
            'registrations' => $registrationsCount,
            'attendance' => $attendanceCount,
            'attendance_rate' => $registrationsCount > 0
                ? round(($attendanceCount / $registrationsCount) * 100)
                : 0,
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
    public function systemStats(): array
    {
        $storagePath = storage_path('app/public');
        $diskFree = disk_free_space($storagePath);
        $diskTotal = disk_total_space($storagePath);
        $diskUsed = $diskTotal - $diskFree;
        $diskPercentage = round(($diskUsed / $diskTotal) * 100);
        $dbDriver = config('database.default');

        return [
            'env' => config('app.env'),
            'debug' => config('app.debug'),
            'laravel_version' => app()->version(),
            'failed_jobs' => DB::table('failed_jobs')->count(),
            'disk_percentage' => $diskPercentage,
            'disk_label' => $this->formatBytes($diskUsed).' / '.$this->formatBytes($diskTotal),
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
