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

            @if($tab === 'view')
                @if($this->eventIsActive())
                    @if(auth()->user())
                        @if(! $this->userIsRegistered($event->id))
                            <flux:button wire:click="registerUser({{ $event->id }})" variant="primary">Register</flux:button>
                        @else
                            <flux:modal.trigger name="unregister-confirmation">
                                <flux:button icon="x-mark" wire:click="confirmUnregister({{ $event->id }})" class="absolute z-10 w-min cursor-pointer" variant="primary">You are registered</flux:button>
                            </flux:modal.trigger>
                        @endif
                    @else
                        <flux:button href="{{ route('login') }}" icon="user-plus" variant="primary">Login to register</flux:button>
                    @endif
                @endif
            @endif
        </div>
    </div>
    <div class="mt-6">
        @if($event->image_path)
            <div class="rounded-lg overflow-hidden mb-6">
                <img src="{{ asset('storage/' . $event->image_path) }}" alt="{{ __('Image') }}" class="w-full h-auto max-h-128 object-cover">
            </div>
        @endif

        <div class="mt-2 flex gap-x-3 items-center text-sm text-zinc-500 dark:text-zinc-400">
            <span>{{ __('Created') }} {{ $event->created_at->diffForHumans() }}</span>
            @can('create articles')
                @if($event->created_at != $event->updated_at)
                    <span>{{ __('Updated') }} {{ $event->updated_at->diffForHumans() }}</span>
                @endif
            @endcan
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
