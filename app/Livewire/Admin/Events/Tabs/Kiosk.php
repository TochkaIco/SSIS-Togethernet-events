<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Events\Tabs;

use App\Models\Event;
use Livewire\Component;

class Kiosk extends Component
{
    public Event $event;

    public function render()
    {
        return view('livewire.admin.events.tabs.kiosk');
    }
}
