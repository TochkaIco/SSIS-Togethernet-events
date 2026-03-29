<x-layouts::app title="{{ __('Event Show') }}">
    <div class="py-8 max-w-4xl mx-auto">
        <div class="flex justify-between items-center">
            @cannot('admin')
                <a href="{{ route('events') }}" class="flex items-center gap-x-2 text-sm font-medium">
                    <x-icons.arrow-back/>
                    {{ __('Back to Events') }}
                </a>
            @endcannot

            @can('admin')
                <a href="{{ route('admin.events') }}" class="flex items-center gap-x-2 text-sm font-medium">
                    <x-icons.arrow-back/>
                    {{ __('Back to Events') }}
                </a>
                <div class="flex gap-x-3 items-center">
                    <flux:button
                        x-data
                        variant="ghost"
                        class="cursor-pointer"
                        data-test="edit-event-button"
                        @click="$dispatch('open-modal', 'edit-event')"
                    >
                        <x-icons.external/>
                        {{ __('Edit Event') }}
                    </flux:button>
                    <form action="{{ route('admin.event.destroy', $event) }}" method="post" onsubmit="return confirm('{{ __('Are you sure you want to delete this event? This action cannot be undone.') }}')">
                        @csrf
                        @method('DELETE')
                        <flux:button type="submit" variant="danger" class="cursor-pointer">
                            {{ __('Delete') }}
                        </flux:button>
                    </form>
                </div>
            @endcan
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
                    @can('admin')
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
                <div>
                    <h3 class="font-bold text-xl mt-6">{{ __('Links') }}</h3>

                    <div class="mt-3 space-y-3">
                        @foreach($event->links as $link)
                            <flux:card :href="$link" class="text-green-500/80 flex font-medium gap-x-3 justify-between">
                                <x-icons.external />
                                <span class="truncate">
                                    {{ $link }}
                                </span>
                            </flux:card>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <x-admin.event.modal :event="$event" />
    </div>
</x-layouts::app>
