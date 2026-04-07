<div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-col gap-4">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('admin.events') }}" icon="layout-grid">{{ __('Events') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('admin.event.show', $event) }}">{{ $event->title }}</flux:breadcrumbs.item>
            @if($tab !== 'view')
                <flux:breadcrumbs.item>{{ __(str($tab)->headline()->toString()) }}</flux:breadcrumbs.item>
            @endif
        </flux:breadcrumbs>

        <div class="flex justify-between items-center">
            <h1 class="font-bold text-3xl">{{ $event->title }}</h1>
            <div class="flex gap-x-3 items-center">
                @haspermission('edit articles')
                <flux:button
                    variant="ghost"
                    icon="pencil-square"
                    class="cursor-pointer transition-all duration-300 shadow-xs hover:-translate-y-0.5 hover:shadow-2xl"
                    data-test="edit-event-button"
                    wire:click="eventEdit"
                >
                    {{ __('Edit') }}
                </flux:button>
                <flux:modal.trigger name="delete-event">
                    <flux:button variant="danger" class="cursor-pointer transition-all duration-300 shadow-xs hover:-translate-y-0.5 hover:shadow-2xl">
                        {{ __('Delete') }}
                    </flux:button>
                </flux:modal.trigger>

                <flux:modal name="delete-event" class="min-w-[22rem]">
                    <form action="{{ route('admin.event.destroy', $event) }}" method="post">
                        @csrf
                        @method('DELETE')
                        <div class="space-y-6">
                            <div>
                                <flux:heading size="lg">{{ __('Delete event?') }}</flux:heading>
                                <flux:text class="mt-2">
                                    {{ __("You're about to delete this event. This action cannot be reversed.") }}
                                </flux:text>
                            </div>

                            <div class="flex gap-2">
                                <flux:spacer />

                                <flux:modal.close>
                                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                                </flux:modal.close>

                                <flux:button type="submit" variant="danger">{{ __('Delete event') }}</flux:button>
                            </div>
                        </div>
                    </form>
                </flux:modal>
                @endhaspermission
            </div>
        </div>
    </div>

    <div class="mt-6">
        @if($tab === 'view')
            <livewire:admin.events.tabs.view-details :event="$event" :key="'view-'.$event->id" />
        @elseif($tab === 'participants')
            <livewire:admin.events.tabs.participants :event="$event" :key="'part-'.$event->id" />
        @elseif($tab === 'waiting')
            <livewire:admin.events.tabs.waiting-list :event="$event" :key="'wait-'.$event->id" />
        @elseif($tab === 'kiosk')
            <livewire:admin.events.tabs.kiosk.kiosk :event="$event" :key="'kiosk-'.$event->id" />
        @else
            <livewire:admin.events.tabs.view-details :event="$event" :key="'view-'.$event->id" />
        @endif
    </div>

    <x-admin.event.modal :event="$event" />
</div>
