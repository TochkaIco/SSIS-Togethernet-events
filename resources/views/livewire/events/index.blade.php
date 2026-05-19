<div>
    <flux:text class="text-4xl font-bold text-center mb-6">{{ __("Togethernet's Events") }}</flux:text>
    <div class="text-muted-foreground flex flex-wrap items-center justify-center md:grid-cols-2 gap-6">
        @php
            [$upcomingEvents, $pastEvents] = $events->partition(fn($event) => $event->event_ends_at >= now());
        @endphp

        @if($events->isEmpty())
            <!-- Your empty placeholder state -->
            <div class="flex mx-auto my-auto relative h-120 w-240 flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20">
                    <flux:icon.calendar class="hidden md:block" />
                    <flux:text class="text-2xl md:text-4xl ml-3 cursor-default">{{ __('No Events Found') }}</flux:text>
                </x-placeholder-pattern>
            </div>
        @else
            <!-- 1. Upcoming & Ongoing Events -->
            @foreach($upcomingEvents as $event)
                <flux:card :key="'event-'.$event->id" class="relative max-w-3xl w-full h-135 flex flex-col transition-all duration-300 shadow-lg hover:-translate-y-1 hover:shadow-2xl">
                    <div class="absolute top-4 left-4 z-20">
                        @if(auth()->user())
                            @if(! $this->userIsRegistered($event->id))
                                @if($event->canRegister())
                                    <flux:button class="cursor-pointer" wire:click="registerUser({{ $event->id }})" variant="primary" class="cursor-pointer transition-all duration-350 hover:drop-shadow-[0_0_15px_rgba(255,176,74,0.6)]">{{ __('Register') }}</flux:button>
                                @endif
                            @else
                                <div class="flex flex-col items-start gap-3">
                                    @php
                                        $registration = auth()->user()->registrations()->where('event_id', $event->id)->first();
                                    @endphp

                                    {{-- Status Badge with soft "Status Light" --}}
                                    <div class="relative">
                                        @if($registration->in_waitinglist)
                                            <div class="absolute -inset-1 bg-yellow-500/20 blur-lg rounded-full"></div>
                                            <flux:badge size="sm" icon="clock" class="cursor-default relative bg-yellow-500/10! text-yellow-400! border-yellow-500/20 px-3 py-1">
                                                {{ __('Waiting List') }}
                                            </flux:badge>
                                        @else
                                            <div class="absolute -inset-1 bg-emerald-500/20 blur-lg rounded-full"></div>
                                            <flux:badge size="sm" icon="check" class="cursor-default relative bg-emerald-500/10! text-emerald-400! border-emerald-500/20 px-3 py-1">
                                                {{ __('Registered') }}
                                            </flux:badge>
                                        @endif
                                    </div>

                                    {{-- Unregister Button: Visible but Secondary --}}
                                    @if($event->canUnregister())
                                        <flux:modal.trigger name="unregister-confirmation">
                                            <flux:button
                                                variant="ghost"
                                                size="xs"
                                                icon="x-mark"
                                                wire:click="confirmUnregister({{ $event->id }})"
                                                class="bg-white/5! text-zinc-300! border border-white/10 hover:bg-red-500/20! hover:!text-red-400 hover:border-red-500/30 transition-all"
                                            >
                                                {{ __('Unregister') }}
                                            </flux:button>
                                        </flux:modal.trigger>
                                    @endif
                                </div>
                            @endif
                        @else
                            @if($event->canRegister())
                                <flux:button href="{{ route('login') }}" icon="user-plus" class="cursor-pointer" variant="primary">{{ __('Login to register for the event') }}</flux:button>
                            @endif
                        @endif
                    </div>

                    <div class="mb-auto">
                        @if($event->image_path)
                            <div class="mb-6 -mx-6 -mt-6 rounded-t-lg overflow-hidden">
                                <img src="{{ asset('storage/' . $event->image_path) }}" alt="{{ __('Image') }}" class="w-full h-auto max-h-60 object-cover mb-2">
                            </div>
                        @else
                            <div class="mb-6 -mx-6 -mt-6 rounded-t-lg overflow-hidden">
                                <img src="{{ asset('images/togethernet-feature.jpg') }}" alt="{{ __('Image') }}" class="w-full h-auto max-h-60 object-cover mb-2">
                            </div>
                        @endif

                        <a href="{{ route('event.show', $event) }}" class="text-accent-content font-semibold text-xl hover:underline hover:text-orange-300">{{ $event->title }}</a>
                        <div class="mt-2 flex-col space-y-2 md:flex-row md:space-y-0 items-center gap-2">
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
                        <p class="mt-5 line-clamp-4 overflow-hidden whitespace-pre-line">{{ html_entity_decode(strip_tags(Str::markdown($event->description)), ENT_QUOTES, 'UTF-8') }}</p>
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
            @endforeach

            <!-- 2. Section Divider for Past Events (Only shows if past events exist) -->
            @if($pastEvents->isNotEmpty())
                <flux:separator text="{{ __('Past Events') }}" />

                <!-- 3. Finished Events Loop -->
                @foreach($pastEvents as $event)
                    <flux:card :key="'event-'.$event->id" class="relative max-w-3xl w-full h-135 flex flex-col transition-all duration-300 shadow-lg hover:-translate-y-1 hover:shadow-2xl opacity-50 grayscale">
                        <div class="absolute top-4 left-4 z-20">
                            @if(auth()->user())
                                @if(! $this->userIsRegistered($event->id))
                                    @if($event->canRegister())
                                        <flux:button class="cursor-pointer" wire:click="registerUser({{ $event->id }})" variant="primary">{{ __('Register') }}</flux:button>
                                    @endif
                                @else
                                    <div class="flex flex-col items-start gap-2">
                                        @php
                                            $registration = auth()->user()->registrations()->where('event_id', $event->id)->first();
                                        @endphp

                                        @if($registration->in_waitinglist)
                                            <flux:badge color="yellow" icon="clock">{{ __('Waiting List') }}</flux:badge>
                                        @else
                                            <flux:badge color="green" icon="check">{{ __('Registered') }}</flux:badge>
                                        @endif

                                        @if($event->canUnregister())
                                            <flux:modal.trigger name="unregister-confirmation">
                                                <flux:button icon="x-mark" wire:click="confirmUnregister({{ $event->id }})" variant="danger" size="xs" class="cursor-pointer">{{ __('Unregister') }}</flux:button>
                                            </flux:modal.trigger>
                                        @endif
                                    </div>
                                @endif
                            @else
                                @if($event->canRegister())
                                    <flux:button href="{{ route('login') }}" icon="user-plus" class="cursor-pointer" variant="primary">{{ __('Login to register for the event') }}</flux:button>
                                @endif
                            @endif
                        </div>

                        <div class="mb-auto">
                            @if($event->image_path)
                                <div class="mb-6 -mx-6 -mt-6 rounded-t-lg overflow-hidden">
                                    <img src="{{ asset('storage/' . $event->image_path) }}" alt="{{ __('Image') }}" class="w-full h-auto max-h-60 object-cover mb-2">
                                </div>
                            @else
                                <div class="mb-6 -mx-6 -mt-6 rounded-t-lg overflow-hidden">
                                    <img src="{{ asset('images/togethernet-feature.jpg') }}" alt="{{ __('Image') }}" class="w-full h-auto max-h-60 object-cover mb-2">
                                </div>
                            @endif

                            <a href="{{ route('event.show', $event) }}" class="text-accent-content font-semibold text-xl hover:underline hover:text-orange-300">{{ $event->title }}</a>
                            <div class="mt-2 flex-col space-y-2 md:flex-row md:space-y-0 items-center gap-2">
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
                            <p class="mt-5 line-clamp-4 overflow-hidden whitespace-pre-line">{{ html_entity_decode(strip_tags(Str::markdown($event->description)), ENT_QUOTES, 'UTF-8') }}</p>
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
                @endforeach
            @endif
        @endif
    </div>

    <flux:modal name="unregister-confirmation" class="min-w-88">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Unregister from event?') }}</flux:heading>
                <flux:subheading>{!! __('Are you sure you want to unregister from this event? You can always register again later if there are spots available, but you will be moved to the end of the queue.') !!}</flux:subheading>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost" class="cursor-pointer">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="danger" class="cursor-pointer" wire:click="unregisterUser">{{ __('Unregister') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
