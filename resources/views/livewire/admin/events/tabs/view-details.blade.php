<div>
    @if($event->image_path)
        <div class="rounded-lg overflow-hidden mb-6 group relative">
            <img src="{{ asset('storage/' . $event->image_path) }}" alt="{{ __('Image') }}" class="w-full h-auto max-h-128 object-cover">
        </div>
    @else
        <flux:field class="mb-6">
            <flux:label>{{ __('Event Image') }}</flux:label>
            <flux:input type="file" wire:model="newImage" />
            <flux:error name="newImage" />
        </flux:field>
    @endif

    <div class="mt-2 flex gap-x-3 items-center text-sm text-zinc-500 dark:text-zinc-400">
        <span>{{ __('Created') }} {{ $event->created_at->diffForHumans() }}</span>
        @if($event->created_at != $event->updated_at)
            <span>{{ __('Updated') }} {{ $event->updated_at->diffForHumans() }}</span>
        @endif
    </div>

    @if($event->description)
        <flux:card class="mt-6">
            <div class="prose prose-zinc dark:prose-invert max-w-none">
                {!! $event->formattedDescription !!}
            </div>
        </flux:card>
    @endif

    @if($event->links && count($event->links))
        <div class="mt-8">
            <h3 class="font-bold text-xl mb-4">{{ __('Links') }}</h3>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($event->links as $link)
                    <flux:card :href="$link" class="flex items-center gap-3">
                        <flux:icon.link class="size-4 text-zinc-400" />
                        <span class="truncate text-sm font-medium">{{ $link }}</span>
                    </flux:card>
                @endforeach
            </div>
        </div>
    @endif
</div>
