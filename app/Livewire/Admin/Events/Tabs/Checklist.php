<?php

namespace App\Livewire\Admin\Events\Tabs;

use App\Models\Event;
use App\Models\EventChecklist;
use App\Models\GlobalLog;
use Livewire\Component;

class Checklist extends Component
{
    public Event $event;

    public ?EventChecklist $checklist = null;

    public string $itemsText = '';

    public array $items = [];

    public array $checked = [];

    public bool $editing = false;

    public function mount(Event $event): void
    {
        $this->authorize('manage event checklists');

        $this->event = $event;
        $this->checklist = $event->checklist;
        if ($this->checklist instanceof EventChecklist && is_array($this->checklist->items)) {
            // Populate items and checked states
            $this->items = [];
            $this->checked = [];
            foreach ($this->checklist->items as $index => $item) {
                $this->items[$index] = $item['text'] ?? '';
                $this->checked[$index] = $item['checked'] ?? false;
            }
            $this->itemsText = implode("\n", $this->items);
        } else {
            $this->items = [];
            $this->checked = [];
            $this->itemsText = '';
        }
    }

    public function updatedItemsText(): void
    {
        $this->parseItems();
    }

    protected function parseItems(): void
    {
        $lines = preg_split('/\r?\n/', $this->itemsText);
        $this->items = array_values(array_filter(array_map('trim', $lines)));
        $newChecked = [];
        foreach ($this->items as $index => $item) {
            $newChecked[$index] = $this->checked[$index] ?? false;
        }
        $this->checked = $newChecked;
    }

    public function editChecklist(): void
    {
        $this->editing = true;
        $this->itemsText = implode("\n", $this->items);
    }

    public function cancelEdit(): void
    {
        $this->editing = false;
        $this->itemsText = implode("\n", $this->items);
    }

    public function toggle(int $index): void
    {
        $this->checked[$index] = ! ($this->checked[$index] ?? false);
        $this->saveChecklist();
    }

    public function saveChecklist(): void
    {
        $data = [];
        foreach ($this->items as $index => $text) {
            $data[$index] = [
                'text' => $text,
                'checked' => $this->checked[$index] ?? false,
            ];
        }
        if ($this->checklist instanceof EventChecklist) {
            $this->checklist->update(['items' => $data]);
        } else {
            $this->checklist = $this->event->checklist()->create(['items' => $data]);
        }
        // Refresh items from saved data to ensure consistency
        $this->items = array_column($data, 'text');
        $this->checked = array_column($data, 'checked');
        $this->itemsText = implode("\n", $this->items);
        $this->editing = false;

        GlobalLog::log('Event checklist Created', 'checklist', ['event_id' => $this->event->id]);
        GlobalLog::discord_log('blue', 'Event checklist Created', 'checklist', ['event_id' => $this->event->id]);
    }

    public function render()
    {
        return view('livewire.admin.events.tabs.checklist');
    }
}
