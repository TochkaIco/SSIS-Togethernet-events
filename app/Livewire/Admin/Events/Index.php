<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Events;

use App\Models\Event;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public $search = '';

    #[Url]
    public $filterType = '';

    #[Url]
    public $filterStatus = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function eventIsActive($event): bool
    {
        return $event->display_starts_at <= now() && $event->event_ends_at >= now();
    }

    #[Layout('layouts.app')]
    public function render(): Factory|\Illuminate\Contracts\View\View|View
    {
        $events = Event::query()
            ->when($this->search, fn ($q) => $q->search($this->search))
            ->when($this->filterType, fn ($q) => $q->ofType($this->filterType))
            ->when($this->filterStatus, function ($q) {
                match ($this->filterStatus) {
                    'upcoming' => $q->upcoming(),
                    'active' => $q->active(),
                    'finished' => $q->finished(),
                    default => $q,
                };
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.events.index', [
            'events' => $events,
        ])->title(__('Admin Events'));
    }
}
