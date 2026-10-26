<div>
    @if($editing)
        <div>
            <textarea wire:model.defer="itemsText" rows="5" class="w-full p-2 border rounded" placeholder="Enter each checklist item on a new line (Markdown supported)"></textarea>
            <div class="mt-4 flex space-x-2">
                <flux:button wire:click="saveChecklist" class="cursor-pointer">{{ __('Save') }}</flux:button>
                <flux:button variant="ghost" wire:click="cancelEdit" class="cursor-pointer">{{ __('Cancel') }}</flux:button>
            </div>
        </div>
    @else
        @if(count($items))
            <div class="flex flex-col md:flex-row justify-start md:justify-between space-y-6">
                <div class="space-y-2">
                    @foreach($items as $index => $item)
                        <div class="flex items-center">
                            <input type="checkbox" id="check-{{ $index }}" wire:click="toggle({{ $index }})" {{ $checked[$index] ? 'checked' : '' }} class="h-4 w-4 rounded cursor-pointer">
                            <label for="check-{{ $index }}" class="ml-2">
                                {!! \Illuminate\Support\Str::markdown($item) !!}
                            </label>
                        </div>
                    @endforeach
                </div>
                <flux:button icon="pencil-square" wire:click="editChecklist" class="cursor-pointer">{{ __('Edit Checklist') }}</flux:button>
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-16 gap-6">
                <flux:icon icon="check" class="size-16 text-muted-foreground" />
                <p class="text-muted-foreground text-lg">{{ __('No checklist configured for this event.') }}</p>
                @can('manage event checklists')
                    <flux:button variant="primary" wire:click="editChecklist" class="cursor-pointer">
                        {{ __('Create Checklist') }}
                    </flux:button>
                @endcan
            </div>
        @endif
    @endif
</div>
