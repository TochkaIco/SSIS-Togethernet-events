<div class="py-8 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-col gap-4">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('events') }}" icon="layout-grid">{{ __('Events') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $event->title }}</flux:breadcrumbs.item>
            @if($tab !== 'view')
                <flux:breadcrumbs.item>{{ str($tab)->headline() }}</flux:breadcrumbs.item>
            @endif
        </flux:breadcrumbs>

        <div class="flex justify-between items-center">
            <h1 class="font-bold text-3xl">{{ $event->title }}</h1>

            @if($this->eventIsActive())
                @if(auth()->user())
                    @if(! $this->userIsRegistered($event->id))
                        <flux:button wire:click="registerUser({{ $event->id }})" variant="primary">{{ __('Register') }}</flux:button>
                    @else
                        <div class="flex flex-col items-end gap-2">
                            @php
                                $registration = auth()->user()->events()->where('event_id', $event->id)->first()->pivot;
                            @endphp

                            @if($registration->in_waitinglist)
                                <flux:badge color="yellow" icon="clock">{{ __('On Waiting List') }}</flux:badge>
                            @else
                                <flux:badge color="green" icon="check" class="cursor-default">{{ __('Registered as Participant') }}</flux:badge>
                            @endif

                            <flux:modal.trigger name="unregister-confirmation">
                                <flux:button icon="x-mark" wire:click="confirmUnregister({{ $event->id }})" variant="danger" size="sm" class="cursor-pointer">{{ __('Unregister') }}</flux:button>
                            </flux:modal.trigger>
                        </div>
                    @endif
                @else
                    <flux:button href="{{ route('login') }}" icon="user-plus" variant="primary">{{ __('Login to register') }}</flux:button>
                @endif
            @endif
        </div>
    </div>
    <div class="mt-6">
        <div class="flex flex-col md:flex-row md:space-x-3 mb-6">
            <div class="flex items-center gap-2">
                <span class="font-medium text-muted-foreground">{{ __('Number of Seats:') }}</span>
                <flux:badge color="orange" size="sm">
                    {{ $event->seatsTaken() }} / {{ $event->num_of_seats }}
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
                    {{ __('This event is free') }}
                </flux:badge>
            @endif
        </div>

        @if($event->image_path)
            <div class="rounded-lg overflow-hidden mb-6">
                <img src="{{ asset('storage/' . $event->image_path) }}" alt="{{ __('Image') }}" class="w-full h-auto max-h-128 object-cover">
            </div>
        @endif

        <div class="mt-2 flex gap-x-3 items-center text-sm text-zinc-500 dark:text-zinc-400">
            <flux:badge>{{ __('Starts at ') . $event->event_starts_at }}</flux:badge>
            <flux:badge>{{ __('Ends at ') . $event->event_ends_at }}</flux:badge>
        </div>

        @if($event->description)
            <flux:card class="mt-6">
                <div class="prose prose-zinc dark:prose-invert max-w-none">
                    {!! $event->formattedDescription !!}
                </div>
            </flux:card>
        @endif

        @if($event->links && $event->links->count())
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
    <flux:modal name="unregister-confirmation" class="min-w-[22rem]">
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
