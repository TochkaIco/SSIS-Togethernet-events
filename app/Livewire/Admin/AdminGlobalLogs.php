<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\GlobalLog;
use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class AdminGlobalLogs extends Component
{
    use WithPagination;

    public int $monthsToKeep = 2;

    public function clearOldLogs(): void
    {
        if (! auth()->user()->hasAnyRole(['admin', 'super-admin', 'maintainer'])) {
            abort(403);
        }

        $count = GlobalLog::where('created_at', '<', now()->subMonths($this->monthsToKeep))->count();

        if ($count === 0) {
            Flux::toast(__('No logs found to clear for the selected period.'), variant: 'warning');

            return;
        }

        GlobalLog::where('created_at', '<', now()->subMonths($this->monthsToKeep))->delete();

        GlobalLog::log('Old Logs Cleared', 'system', ['months_kept' => $this->monthsToKeep, 'logs_deleted' => $count]);

        $this->modal('confirm-log-clearing')->close();

        Flux::toast(__(':count old logs cleared.', ['count' => $count]), variant: 'success');
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        $logs = GlobalLog::with('user')
            ->latest()
            ->paginate(10);

        $targetUserIds = collect($logs->items())->map(function ($log) {
            return $log->details['target_user_id'] ?? $log->details['user_id'] ?? null;
        })->filter()->unique();

        $targetUsers = User::whereIn('id', $targetUserIds)->get()->keyBy('id');

        return view('livewire.admin.admin-global-logs', [
            'logs' => $logs,
            'targetUsers' => $targetUsers,
        ])->title(__('Global Logs'));
    }
}
