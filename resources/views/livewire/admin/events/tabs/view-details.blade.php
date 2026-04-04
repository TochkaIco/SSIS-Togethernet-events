<div>
    <div class="flex flex-col md:flex-row md:space-x-3 mb-6">
        <div class="flex items-center gap-2">
            <span class="font-medium text-muted-foreground">{{ __('Number of Seats:') }}</span>
            <flux:badge color="orange" size="sm">
                {{ $event->num_of_seats }}
            </flux:badge>
        </div>

        @if($event->paid_entry===1)
            <div class="flex items-center gap-2">
                <span class="font-medium text-muted-foreground">{{ __('Entrance Fee:') }}</span>
                <flux:badge color="orange" size="sm">
                    {{ $event->entry_fee }} kr
                </flux:badge>
            </div>
        @else
            <flux:badge color="orange">
                {{ __('This event is free') . $event->entry_fee }}
            </flux:badge>
        @endif
    </div>

    @if($event->image_path)
        <div class="rounded-lg overflow-hidden mb-6 group relative">
            <img src="{{ asset('storage/' . $event->image_path) }}" alt="{{ __('Image') }}" class="w-full h-auto max-h-128 object-cover">
        </div>
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
