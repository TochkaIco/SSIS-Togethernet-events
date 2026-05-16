<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Events;

use App\Models\Event;
use Flux\Flux;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

class EventShow extends Component
{
    public Event $event;

    #[Url]
    public string $tab = 'view';

    public function eventEdit(): void
    {
        $this->authorize('edit articles');

        Flux::modal('edit-event')->show();
    }

    public function clearEventImage(): void
    {
        $this->authorize('edit articles');

        if ($this->event->image_path) {
            Storage::disk('public')->delete($this->event->image_path);
            $this->event->update(['image_path' => null]);
            $this->event->refresh();
            Flux::toast(__('Image removed successfully.'), variant: 'success');
        } else {
            Flux::toast(__('No image to remove.'), variant: 'warning');
        }
    }

    #[Layout('layouts.app')]
    public function render(): Factory|\Illuminate\Contracts\View\View|View
    {
        return view('livewire.admin.events.show', [
            'event' => $this->event,
        ])->title($this->event->title);
    }
}
