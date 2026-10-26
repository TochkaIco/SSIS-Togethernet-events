<?php

namespace App\Livewire\Admin\Events\Tabs;

use App\Models\Event;
use App\Models\EventChecklist;
use App\Models\GlobalLog;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Checklist extends Component
{
    public Event $event;

    public ?EventChecklist $checklist = null;

    public string $itemsText = '';

    public array $items = [];

    public array $checked = [];

    public bool $editing = false;

    public ?int $importEventId = null;

    public bool $showImportModal = false;

    protected $listeners = [
        'refreshChecklist' => '$refresh',
    ];

    public function mount(Event $event): void
    {
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
        $this->authorize('manage event checklists');

        $this->editing = true;
        $this->itemsText = implode("\n", $this->items);
    }

    public function cancelEdit(): void
    {
        $this->authorize('manage event checklists');

        $this->editing = false;
        $this->itemsText = implode("\n", $this->items);
    }

    public function toggle(int $index): void
    {
        $this->authorize('manage event checklists');

        $this->checked[$index] = ! ($this->checked[$index] ?? false);
        $this->saveChecklist();
    }

    #[Computed]
    public function renderedItems(): array
    {
        $render = [];
        foreach ($this->items as $index => $item) {
            $trimmed = trim($item);
            $isHeader = Str::startsWith($trimmed, '#');
            $isCheckbox = Str::startsWith($trimmed, '- ');
            if ($isHeader) {
                $render[] = ['type' => 'header', 'text' => $item];
            } elseif ($isCheckbox) {
                $displayText = ltrim($trimmed, '- ');
                $render[] = [
                    'type' => 'checkbox',
                    'text' => $displayText,
                    'checked' => $this->checked[$index] ?? false,
                    'index' => $index,
                ];
            } else {
                $render[] = ['type' => 'text', 'text' => $item];
            }
        }

        return $render;
    }

    public function saveChecklist(): void
    {
        $this->authorize('manage event checklists');

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

        GlobalLog::log('Event checklist Saved', 'checklist', ['event_id' => $this->event->id]);
        GlobalLog::discord_log('blue', 'Event checklist Saved', 'checklist', ['event_id' => $this->event->id]);
    }

    public function openImportModal(): void
    {
        $this->authorize('manage event checklists');

        $this->reset(['importEventId']);
        $this->showImportModal = true;
    }

    /**
     * @throws \Throwable
     */
    public function importFromEvent(): void
    {
        $this->authorize('manage event checklists');

        $sourceEvent = Event::findOrFail($this->importEventId);
        $currentEvent = $this->event;

        DB::transaction(function () use ($sourceEvent, $currentEvent) {
            $currentEvent->checklist()->updateOrCreate(
                ['event_id' => $currentEvent->id],
                ['items' => $sourceEvent->checklist->items]
            );
        });

        Flux::toast(__('Checklist imported successfully.'), variant: 'success');
        $this->showImportModal = false;
        $this->dispatch('refreshChecklist');
    }

    public function render(): View
    {
        $availableEvents = Event::where('id', '!=', $this->event->id)
            ->whereHas('checklist')
            ->get();

        return view('livewire.admin.events.tabs.checklist', [
            'availableEvents' => $availableEvents,
            'renderedItems' => $this->renderedItems,
        ]);
    }
}
