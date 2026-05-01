<div>
    <div class="flex flex-col md:min-w-5xl md:flex-row md:space-x-3 space-y-3 md:space-y-0 mb-6">
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

    @if($event->one_hour_periods)
        <flux:badge>{{ __('Date') }}:<span class="ml-2 text-orange-300">{{ $event->event_starts_at->format('M j, Y') }}</span></flux:badge>
    @else
        <div class="mt-2 flex flex-col md:flex-row space-y-3 md:gap-x-3 md:space-y-0 md:items-center text-sm">
            <flux:badge>{{ __('Starts at ') }}<span class="ml-2 text-orange-300">{{ $event->event_starts_at->format('M j, Y, h:i') }}</span></flux:badge>
            <flux:badge>{{ __('Ends at ') }}<span class="ml-2 text-orange-300">{{ $event->event_ends_at->format('M j, Y, h:i') }}</span></flux:badge>
        </div>
    @endif

    <div class="mt-6 flex flex-col md:flex-row space-y-3 md:gap-x-3 md:space-y-0 md:items-center text-sm">
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

    @if($event->periods()->count() > 1)
        <h3 class="font-bold mt-6 mb-3">{{ __('Event Schedule') }}</h3>
        <div class="flex flex-col">
            @foreach($event->eventPeriods() as $item)
                @if($item->type === 'period')
                    {{-- Period Row --}}
                    <flux:badge class="p-1 border flex items-center justify-between">
                        <span class="font-medium px-2 italic">Period {{ $item->number }}</span>

                        <span class="border-l-4 border-l-orange-300 border border-orange-300 p-1 font-bold tracking-wider rounded text-sm bg-orange-300/20">
                            {{ $item->label }}
                        </span>
                    </flux:badge>
                @else
                    {{-- Break Row --}}
                    <div class="flex flex-col items-center justify-center">
                        <flux:icon icon="arrow-down" />
                        <div class="bg-accent-foreground px-3 text-[12px] font-bold uppercase tracking-widest text-muted-foreground border border-accent-content rounded-full shadow-sm">
                            {{ $item->label }}
                        </div>
                        <flux:icon icon="arrow-down" />
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    @if($event->links && count($event->links))
        <div class="mt-8">
            <h3 class="font-bold text-xl mb-4">{{ __('Links') }}</h3>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($event->links as $link)
                    <flux:card :href="$link" class="flex items-center gap-3 transition-all duration-300 shadow-lg hover:-translate-y-1 hover:shadow-2xl hover:text-orange-300">
                        <flux:icon.link class="size-4 text-zinc-400" />
                        <span class="truncate text-sm font-medium">{{ $link }}</span>
                    </flux:card>
                @endforeach
            </div>
        </div>
    @endif
</div>
