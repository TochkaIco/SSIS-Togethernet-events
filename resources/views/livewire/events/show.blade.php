<div class="py-8 max-w-4xl mx-auto">
    <div class="flex justify-between items-center">
        <a href="{{ route('events') }}" class="flex items-center gap-x-2 text-sm font-medium">
            <x-icons.arrow-back/>
            {{ __('Back to Events') }}
        </a>

        @if($this->eventIsActive($event))
            @if(auth()->user())
                @if(! $this->userIsRegistered($event->id))
                    <flux:button class="absolute w-min cursor-pointer" wire:click="registerUser({{ $event->id }})" variant="primary">Register</flux:button>
                @else
                    <flux:button icon="x-mark" wire:click="unregisterUser({{ $event->id }})" class="absolute w-min cursor-pointer scale-80 md:scale-100" variant="primary">You are registered for this event</flux:button>
                @endif
            @else
                <flux:button href="{{ route('login') }}" icon="user-plus" class="absolute w-min cursor-pointer" variant="primary">Login to register for the event</flux:button>
            @endif
        @endif
    </div>
    <div class="mt-6">
        @if($event->image_path)
            <div class="rounded-lg overflow-hidden">
                <img src="{{ asset('storage/' . $event->image_path) }}" alt="{{ __('Image') }}" class="w-full h-auto max-h-100 object-cover mb-2">
            </div>
        @endif

        <h1 class="font-bold text-4xl">{{ $event->title }}</h1>

        <div class="mt-2 flex gap-x-3 items-center">
            <div class="flex gap-x-3 items-center text-accent-content text-sm">
                <span>{{ __('Created') }} {{ $event->created_at->diffForHumans() }}</span>
                @can('create articles')
                    @if($event->created_at != $event->updated_at)
                        <span>{{ __('Updated') }} {{ $event->updated_at->diffForHumans() }}</span>
                    @endif
                @endcan
            </div>
        </div>

        @if($event->description)
            <flux:card class="mt-6">
                <div class="text-accent-content max-w-none cursor-pointer prose prose-invert">{!! $event->formattedDescription !!}</div>
            </flux:card>
        @endif

        @if($event->links && $event->links->count())
            <div class="items-start">
                <h3 class="font-bold text-xl mt-6">{{ __('Links') }}</h3>

                <div class="mt-3 space-y-3">
                    @foreach($event->links as $link)
                        <flux:card :href="$link" class="text-green-500/80 flex font-medium gap-x-3 items-center">
                            <x-icons.external/>
                            {{ $link }}
                        </flux:card>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <x-admin.event.modal :event="$event" />
</div>
