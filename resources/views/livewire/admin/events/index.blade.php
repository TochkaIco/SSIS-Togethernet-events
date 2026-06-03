<div>
    {{-- Search & Filters --}}
    <div class="flex flex-col md:flex-row gap-4 mb-8">
        <flux:button
            variant="primary"
            x-data
            icon="plus-circle"
            data-test="create-event-button"
            class="cursor-pointer max-w-xl text-xl transition-all duration-300 shadow-lg hover:-translate-y-0.5 hover:shadow-2xl"
            x-on:click="$flux.modal('create-event').show()"
        >
            {{ __('Create Event') }}
        </flux:button>

        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="{{ __('Search events...') }}" class="flex-1" />

        <div class="flex gap-2 md:gap-4 flex-1">
            <flux:select wire:model.live="filterType" placeholder="{{ __('Filter by Type') }}" class="flex-1">
                <flux:select.option value="">{{ __('All Types') }}</flux:select.option>
                @foreach(\App\EventType::cases() as $type)
                    <flux:select.option :value="$type->value">{{ $type->label() }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="filterStatus" placeholder="{{ __('Filter by Status') }}" class="flex-1">
                <flux:select.option value="">{{ __('All Statuses') }}</flux:select.option>
                <flux:select.option value="upcoming">{{ __('Upcoming') }}</flux:select.option>
                <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                <flux:select.option value="finished">{{ __('Finished') }}</flux:select.option>
            </flux:select>
        </div>
    </div>

    <div class="text-muted-foreground flex flex-wrap items-center justify-center md:grid-cols-2 gap-6">
        @forelse($events as $event)
            <flux:card :key="'card-'.$event->id" class="relative max-w-3xl w-full h-min flex flex-col transition-all duration-300 shadow-lg hover:-translate-y-1 hover:shadow-2xl">
                <a href="{{ route('admin.event.show', $event) }}" class="absolute inset-0 z-0">
                    <span class="sr-only">View {{ $event->title }}</span>
                </a>

                <div class="mb-auto">
                    @if($event->image_path)
                        <div class="mb-2 md:mb-3 -mx-6 -mt-6 rounded-t-lg overflow-hidden">
                            <img src="{{ asset('storage/' . $event->image_path) }}" alt="{{ __('Image') }}" class="w-full h-36 md:h-60 object-cover mb-2">
                        </div>
                    @else
                        <div class="mb-2 md:mb-3 -mx-6 -mt-6 rounded-t-lg overflow-hidden">
                            <x-placeholder-pattern class="stroke-gray-900/20 dark:stroke-neutral-100/20 w-full h-36 md:h-60 object-cover mb-2">
                                <flux:text class="text-4xl ml-3 text-center cursor-default">{{ __('No Image Specified') }}</flux:text>
                            </x-placeholder-pattern>
                        </div>
                    @endif

                    <h2 class="text-accent-content font-semibold text-xl">{{ $event->title }}</h2>
                    <div class="mt-2 flex-col space-y-3 md:flex-row md:space-y-0 items-center gap-2">
                        @if($event->event_type !== \App\EventType::QR_TAG)
                            <span class="text-sm font-medium text-muted-foreground">{{ __('Seats:') }}</span>
                            <flux:badge color="orange" size="sm">
                                @if($event->one_hour_periods)
                                    {{ $event->seatsTaken() }} / {{ $event->num_of_seats * ($event->one_hour_periods_number ?? 1) }}
                                @else
                                    {{ $event->seatsTaken() }} / {{ $event->num_of_seats }}
                                @endif
                            </flux:badge>
                        @endif

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
                    <p class="mt-3 md:mt-5 line-clamp-4 overflow-hidden whitespace-pre-line">{{ html_entity_decode(strip_tags(Str::markdown($event->description)), ENT_QUOTES, 'UTF-8') }}</p>
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
                    <flux:icon.calendar class="hidden md:block" />
                    <flux:text class="text-2xl md:text-4xl ml-3 cursor-default">{{ __('No Events Found') }}</flux:text>
                </x-placeholder-pattern>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        <flux:pagination :paginator="$events" scroll-to />
    </div>

    <x-admin.event.modal />
</div>
