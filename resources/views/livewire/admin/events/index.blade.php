<div>
    <div class="flex items-center justify-center mb-10">
        <flux:button
            variant="primary"
            x-data
            data-test="create-event-button"
            class="cursor-pointer w-xl text-xl transition-all duration-300 shadow-lg hover:-translate-y-0.5 hover:shadow-2xl"
            x-on:click="$flux.modal('create-event').show()"
        >{{ __('Create Event') }}</flux:button>
    </div>
    <div class="text-muted-foreground flex flex-wrap items-center justify-center md:grid-cols-2 gap-6">
        @forelse($events as $event)
            <flux:card :key="'card-'.$event->id" class="w-3xl h-135 flex flex-col transition-all duration-300 shadow-lg hover:-translate-y-1 hover:shadow-2xl">
                <div class="mb-auto">
                    @if($event->image_path)
                        <div class="mb-6 -mx-6 -mt-6 rounded-t-lg overflow-hidden">
                            <img src="{{ asset('storage/' . $event->image_path) }}" alt="{{ __('Image') }}" class="w-full h-auto max-h-60 object-cover mb-2">
                        </div>
                    @else
                        <div class="mb-6 -mx-6 -mt-6 rounded-t-lg overflow-hidden">
                            <x-placeholder-pattern class="stroke-gray-900/20 dark:stroke-neutral-100/20 w-full h-60 object-cover mb-2">
                                <flux:text class="text-4xl ml-3 text-center cursor-default">{{ __('No Image Specified') }}</flux:text>
                            </x-placeholder-pattern>
                        </div>
                    @endif

                    <a href="{{ route('admin.event.show', $event) }}" class="text-accent-content font-semibold text-xl hover:underline hover:text-orange-300">{{ $event->title }}</a>
                    <div class="mt-2 flex items-center gap-2">
                        <span class="text-sm font-medium text-muted-foreground">{{ __('Seats:') }}</span>
                        <flux:badge color="orange" size="sm">
                            {{ $event->seatsTaken() }} / {{ $event->num_of_seats }}
                        </flux:badge>

                        @if($event->paid_entry===1)
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-muted-foreground">{{ __('Entrance Fee:') }}</span>
                                <flux:badge color="orange" size="sm">
                                    {{ $event->entry_fee }} kr
                                </flux:badge>
                            </div>
                        @else
                            <flux:badge color="orange">
                                {{ __('This event is free') }}
                            </flux:badge>
                        @endif
                    </div>
                    <p class="mt-5 line-clamp-4 overflow-hidden whitespace-pre-line">{{ strip_tags(Str::markdown($event->description)) }}</p>
                </div>
                <div class="mt-auto">
                    <flux:separator class="mt-2" />
                    <div class="mt-5 flex gap-x-3 items-center justify-between text-sm">
                        <flux:label class="inline-block rounded-full border px-2 py-1 text-xs font-medium bg-yellow-500/10 text-yellow-500 border-yellow-500/20">
                            {{ $event->event_type->label() }}
                        </flux:label>
                        <span>{{ __('Starts at ') . $event->event_starts_at->format('M j, Y') }}</span>
                    </div>
                </div>
            </flux:card>
        @empty
            <div class="flex mx-auto my-auto relative h-120 w-240 flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20">
                    <flux:icon.calendar />
                    <flux:text class="text-2xl md:text-4xl ml-3 cursor-default">{{ __('No Events Found') }}</flux:text>
                </x-placeholder-pattern>
            </div>
        @endforelse
    </div>

    <x-admin.event.modal />
</div>
