<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Events;

use App\Models\Event;
use Flux\Flux;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
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
            Flux::toast('Image removed successfully');
        } else {
            Flux::toast('No image to remove');
        }
    }

    public function render(): Factory|\Illuminate\Contracts\View\View|View
    {
        return view('livewire.admin.events.show', [
            'event' => $this->event,
        ]);
    }
}
