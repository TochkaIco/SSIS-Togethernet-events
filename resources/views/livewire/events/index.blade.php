<div>
    <flux:text class="text-4xl font-bold text-center mb-6">Togethernet's Events</flux:text>
    <div class="text-muted-foreground flex flex-wrap items-center justify-center md:grid-cols-2 gap-6">
        @forelse($events as $event)
            <flux:card :key="'event-'.$event->id" class="relative w-3xl h-120 flex flex-col">
                @if($this->eventIsActive($event))
                    @if(auth()->user())
                        @if(! $this->userIsRegistered($event->id))
                            <flux:button class="absolute z-10 w-min cursor-pointer" wire:click="registerUser({{ $event->id }})" variant="primary">Register</flux:button>
                        @else
                            <flux:modal.trigger name="unregister-confirmation">
                                <flux:button icon="x-mark" wire:click="confirmUnregister({{ $event->id }})" class="absolute z-10 w-min cursor-pointer" variant="primary">You are registered for this event</flux:button>
                            </flux:modal.trigger>
                        @endif
                    @else
                        <flux:button href="{{ route('login') }}" icon="user-plus" class="absolute z-10 w-min cursor-pointer" variant="primary">Login to register for the event</flux:button>
                    @endif
                @endif
                <div class="mb-auto relative inset-0 -mt-10">
                    @if($event->image_path)
                        <div class="mb-6 -mx-6 -mt-6 rounded-t-lg overflow-hidden">
                            <img src="{{ asset('storage/' . $event->image_path) }}" alt="{{ __('Image') }}" class="w-full h-auto max-h-60 object-cover mb-2">
                        </div>
                    @else
                        <div class="mb-6 -mx-6 -mt-6 rounded-t-lg overflow-hidden">
                            <x-placeholder-pattern class="stroke-gray-900/20 dark:stroke-neutral-100/20 w-full h-60 object-cover mb-2">
                                <flux:text class="text-4xl ml-3 text-center">No Image Specified</flux:text>
                            </x-placeholder-pattern>
                        </div>
                    @endif

                    <a href="{{ route('event.show', $event) }}" class="text-accent-content font-semibold text-xl hover:underline hover:text-orange-300">{{ $event->title }}</a>
                    <p class="mt-5 line-clamp-4 overflow-hidden whitespace-pre-line">{{ strip_tags(Str::markdown($event->description)) }}</p>
                </div>
                <div class="mt-auto">
                    <flux:separator class="mt-2" />
                    <div class="mt-2 flex gap-x-3 items-center justify-between text-sm">
                        <flux:label class="inline-block rounded-full border px-2 py-1 text-xs font-medium bg-yellow-500/10 text-yellow-500 border-yellow-500/20">
                            {{ $event->event_type->label() }}
                        </flux:label>
                        <span>{{ __('Created') }} {{ $event->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </flux:card>
        @empty
            <div class="flex mx-auto my-auto relative h-120 w-240 flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20">
                    <flux:icon.calendar />
                    <flux:text class="text-4xl ml-3">No Events Found</flux:text>
                </x-placeholder-pattern>
            </div>
        @endforelse
    </div>

    <flux:modal name="unregister-confirmation" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Unregister from event?</flux:heading>
                <flux:subheading>Are you sure you want to unregister from this event? You can always register again later if there are spots available.</flux:subheading>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost" class="cursor-pointer">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="danger" class="cursor-pointer" wire:click="unregisterUser">Unregister</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
