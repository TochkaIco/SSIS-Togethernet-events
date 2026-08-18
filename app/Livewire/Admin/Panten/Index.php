<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Panten;

use App\Models\AppConfig;
use App\Models\GlobalLog;
use App\Models\PantAlert;
use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    use WithFileUploads, WithPagination;

    public bool $showCompleteModal = false;

    public string $completionMethod = 'swish';

    public string $sekReceived = '';

    public $receiptPhoto;

    public array $completedBy = [];

    public ?string $viewingPhotoPath = null;

    public function mount(): void
    {
        $this->authorize('manage panten');
    }

    public function activateAlert(): void
    {
        if (! auth()->user()->hasAnyRole(['admin', 'super-admin', 'maintainer'])) {
            abort(403);
        }

        $incompleteAlert = PantAlert::whereNull('admin_user_id')->first();
        if ($incompleteAlert) {
            Flux::toast(__('A pant alert is already active or pending confirmation.'), variant: 'warning');

            return;
        }

        $alert = PantAlert::create([
            'is_complete' => false,
            'receiver_swish' => AppConfig::get('pant_swish_number', 'unset'),
        ]);

        GlobalLog::log('Pant alert activated', 'panten', ['alert_id' => $alert->id]);

        Flux::toast(__('Pant alert activated successfully.'), variant: 'success');
    }

    public function openCompleteModal(): void
    {
        $this->authorize('manage panten');
        $this->reset(['sekReceived', 'receiptPhoto', 'completionMethod']);
        $this->completedBy = [auth()->id()];
        $this->showCompleteModal = true;
        $this->modal('complete-alert-modal')->show();
    }

    public function completeAlert(): void
    {
        $this->authorize('manage panten');

        $activeAlert = PantAlert::active()->first();
        if (! $activeAlert) {
            Flux::toast(__('No active pant alert found.'), variant: 'warning');

            return;
        }

        $this->validate([
            'completionMethod' => 'required|in:swish,check',
            'sekReceived' => 'required|numeric|min:0',
            'completedBy' => 'required|array|min:1',
            'receiptPhoto' => [
                $this->completionMethod === 'check' ? 'required' : 'nullable',
                'image',
                'max:10240',
            ],
        ]);

        $path = null;
        if ($this->completionMethod === 'check' && $this->receiptPhoto) {
            $path = $this->receiptPhoto->store('pant_receipts', 'public');
        }

        $userIds = $this->completedBy;
        if (! in_array(auth()->id(), $userIds)) {
            $userIds[] = auth()->id();
        }

        $activeAlert->update([
            'is_complete' => true,
            'completed_by' => $userIds,
            'sek_received' => (float) $this->sekReceived,
            'receipt_path' => $path,
        ]);

        GlobalLog::log('Pant alert completed', 'panten', [
            'alert_id' => $activeAlert->id,
            'sek' => $this->sekReceived,
            'method' => $this->completionMethod,
        ]);

        $this->showCompleteModal = false;
        $this->modal('complete-alert-modal')->close();
        $this->reset(['sekReceived', 'receiptPhoto', 'completedBy', 'completionMethod']);

        Flux::toast(__('Pant alert completed successfully.'), variant: 'success');
    }

    public ?int $confirmingReceiptId = null;

    public function confirmReceipt(int $alertId): void
    {
        if (! auth()->user()->hasAnyRole(['admin', 'super-admin', 'maintainer'])) {
            abort(403);
        }

        $alert = PantAlert::findOrFail($alertId);
        if ($alert->admin_user_id !== null) {
            Flux::toast(__('Receipt already confirmed.'), variant: 'warning');

            return;
        }

        $this->confirmingReceiptId = $alertId;
        $this->modal('confirm-receipt-modal')->show();
    }

    public function executeConfirmReceipt(): void
    {
        if (! auth()->user()->hasAnyRole(['admin', 'super-admin', 'maintainer'])) {
            abort(403);
        }

        if (! $this->confirmingReceiptId) {
            return;
        }

        $alert = PantAlert::findOrFail($this->confirmingReceiptId);
        if ($alert->admin_user_id !== null) {
            Flux::toast(__('Receipt already confirmed.'), variant: 'warning');
            $this->confirmingReceiptId = null;
            $this->modal('confirm-receipt-modal')->close();

            return;
        }

        $alert->update([
            'admin_user_id' => auth()->id(),
        ]);

        GlobalLog::log('Pant receipt confirmed', 'panten', ['alert_id' => $this->confirmingReceiptId]);

        $this->confirmingReceiptId = null;
        $this->modal('confirm-receipt-modal')->close();

        Flux::toast(__('Receipt confirmed successfully.'), variant: 'success');
    }

    public function viewPhoto(int $alertId): void
    {
        if (! auth()->user()->hasAnyRole(['admin', 'super-admin', 'maintainer'])) {
            abort(403);
        }

        $alert = PantAlert::findOrFail($alertId);
        $this->viewingPhotoPath = $alert->receipt_path;
        $this->modal('view-photo-modal')->show();
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        // 1. Calculate stats
        $completedAlerts = PantAlert::where('is_complete', true)->get();
        $totalSek = $completedAlerts->sum('sek_received');

        $thisYear = (int) now()->format('Y');
        $thisYearAlerts = $completedAlerts->filter(fn ($a) => $a->created_at && (int) $a->created_at->format('Y') === $thisYear);
        $yearlySek = $thisYearAlerts->sum('sek_received');
        $yearlyCount = $thisYearAlerts->count();

        // 2. Leaderboard
        $userCounts = [];
        foreach ($completedAlerts as $alert) {
            $userIds = $alert->completed_by ?? [];
            foreach ($userIds as $id) {
                $userCounts[$id] = ($userCounts[$id] ?? 0) + 1;
            }
        }
        arsort($userCounts);
        $leaderboard = [];
        $topUserIds = array_slice(array_keys($userCounts), 0, 5, true);
        if ($topUserIds !== []) {
            $users = User::whereIn('id', $topUserIds)->get()->keyBy('id');
            foreach ($userCounts as $userId => $count) {
                if ($users->has($userId)) {
                    $leaderboard[] = (object) [
                        'user' => $users->get($userId),
                        'count' => $count,
                    ];
                }
                if (count($leaderboard) >= 5) {
                    break;
                }
            }
        }

        $activeAlert = PantAlert::active()->first();
        $hasIncomplete = PantAlert::whereNull('admin_user_id')->exists();
        $pastAlerts = PantAlert::where('is_complete', true)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $usersList = User::notAnonymized()->orderBy('name')->get();

        return view('livewire.admin.panten.index', [
            'totalSek' => $totalSek,
            'yearlySek' => $yearlySek,
            'yearlyCount' => $yearlyCount,
            'leaderboard' => $leaderboard,
            'activeAlert' => $activeAlert,
            'pastAlerts' => $pastAlerts,
            'usersList' => $usersList,
            'hasIncomplete' => $hasIncomplete,
        ]);
    }
}
