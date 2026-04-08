<div>
    <flux:text class="text-4xl font-bold text-center mb-6">{{ __("Togethernet's Events") }}</flux:text>
    <div class="text-muted-foreground flex flex-wrap items-center justify-center md:grid-cols-2 gap-6">
        @forelse($events as $event)
            <flux:card :key="'event-'.$event->id" class="relative w-3xl h-135 flex flex-col transition-all duration-300 shadow-lg hover:-translate-y-1 hover:shadow-2xl">
                @if($this->eventIsActive($event))
                    <div class="absolute top-4 left-4 z-20">
                        @if(auth()->user())
                            @if(! $this->userIsRegistered($event->id))
                                <flux:button class="cursor-pointer transition-all duration-300 shadow-lg hover:-translate-y-0.5 hover:shadow-2xl" wire:click="registerUser({{ $event->id }})" variant="primary">{{ __('Register') }}</flux:button>
                            @else
                                <div class="flex flex-col items-start gap-2">
                                    @php
                                        $registration = auth()->user()->events()->where('event_id', $event->id)->first()->pivot;
                                    @endphp

                                    @if($registration->in_waitinglist)
                                        <flux:badge color="yellow" icon="clock">{{ __('Waiting List') }}</flux:badge>
                                    @else
                                        <flux:badge color="green" icon="check">{{ __('Registered') }}</flux:badge>
                                    @endif

                                    <flux:modal.trigger name="unregister-confirmation">
                                        <flux:button icon="x-mark" wire:click="confirmUnregister({{ $event->id }})" variant="danger" size="xs" class="cursor-pointer">{{ __('Unregister') }}</flux:button>
                                    </flux:modal.trigger>
                                </div>
                            @endif
                        @else
                            <flux:button href="{{ route('login') }}" icon="user-plus" class="cursor-pointer transition-all duration-300 shadow-lg hover:-translate-y-0.5 hover:shadow-2xl" variant="primary">{{ __('Login to register for the event') }}</flux:button>
                        @endif
                    </div>
                @endif

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
                    <div class="mt-2 flex items-center gap-2">
                        <span class="text-sm font-medium text-muted-foreground">{{ __('Seats:') }}</span>
                        <flux:badge color="orange" size="sm">
                            @if($event->one_hour_periods)
                                {{ $event->seatsTaken() }} / {{ $event->num_of_seats * ($event->one_hour_periods_number ?? 1) }}
                            @else
                                {{ $event->seatsTaken() }} / {{ $event->num_of_seats }}
                            @endif
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

    <flux:modal name="unregister-confirmation" class="min-w-88">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Unregister from event?') }}</flux:heading>
                <flux:subheading>{!! __('Are you sure you want to unregister from this event? You can always register again later if there are spots available, but you will be moved to the <span class="font-bold text-red-500">end of the queue</span>.') !!}</flux:subheading>
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
