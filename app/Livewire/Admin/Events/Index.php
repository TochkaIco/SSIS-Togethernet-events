<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Events;

use App\Models\Event;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Livewire\Component;

class Index extends Component
{
    public function eventIsActive($event): bool
    {
        return $event->display_starts_at <= now() && $event->event_ends_at >= now();
    }

    public function render(): Factory|\Illuminate\Contracts\View\View|View
    {
        return view('livewire.admin.events.index', [
            'events' => Event::latest()->get(),
        ]);
    }
}
