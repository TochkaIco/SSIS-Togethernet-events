<div class="py-8 max-w-7xl md:min-w-3xl mx-auto px-0 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-col gap-4">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('admin.events') }}" icon="layout-grid">{{ __('Events') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('admin.event.show', $event) }}">{{ $event->title }}</flux:breadcrumbs.item>
            @if($tab !== 'view')
                <flux:breadcrumbs.item>{{ __(str($tab)->headline()->toString()) }}</flux:breadcrumbs.item>
            @endif
        </flux:breadcrumbs>

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex flex-col md:flex-row items-start md:items-center space-y-1 md:space-x-3">
                <h1 class="font-bold text-2xl sm:text-3xl wrap-break-word min-w-0 flex-1">{{ $event->title }}</h1>
                @if($event->isFinished())
                    <flux:badge size="sm" icon="flag" class="cursor-default bg-red-500/40! border-zinc-500/20 px-3 py-1 justify-center shrink-0">
                        {{ __('Event Finished') }}
                    </flux:badge>
                @endif
            </div>
            <div class="flex space-x-3 items-center justify-between">
                @if($event->event_type === \App\EventType::QR_TAG)
                    <flux:button
                        variant="ghost"
                        icon="tv"
                        href="{{ route('event.tv', $event) }}"
                        target="_blank"
                        class="cursor-pointer transition-all duration-300 shadow-xs hover:-translate-y-0.5 hover:shadow-2xl"
                    >
                        {{ __('TV View') }}
                    </flux:button>
                @endif

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
                    <flux:button
                        variant="danger"
                        class="cursor-pointer transition-all duration-300 shadow-xs hover:-translate-y-0.5 hover:shadow-2xl"
                        :disabled="!$event->canDelete()"
                        :tooltip="!$event->canDelete() ? 'Cannot delete events with participants.' : null"
                    >
                        {{ __('Delete') }}
                    </flux:button>
                </flux:modal.trigger>

                <flux:modal name="delete-event" class="min-w-[22rem]">
                    @if(!$event->canDelete())
                        <div class="space-y-6">
                            <div>
                                <flux:heading size="lg">{{ __('Cannot Delete Event') }}</flux:heading>
                                <flux:text class="mt-2 text-red-500">
                                    {{ __('This event has participants and was created more than 30 minutes ago.') }}
                                </flux:text>
                            </div>
                            <div class="flex gap-2">
                                <flux:spacer />
                                <flux:modal.close>
                                    <flux:button variant="ghost">{{ __('Close') }}</flux:button>
                                </flux:modal.close>
                            </div>
                        </div>
                    @else
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
                    @endif
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
        @elseif($tab === 'waiting' && $event->event_type !== \App\EventType::QR_TAG)
            <livewire:admin.events.tabs.waiting-list :event="$event" :key="'wait-'.$event->id" />
        @elseif($tab === 'qr-tag' && $event->event_type === \App\EventType::QR_TAG)
            @can('manage qr-tag')
                <livewire:admin.events.tabs.qr-tag :event="$event" :key="'qrtag-'.$event->id" />
            @endcan
        @elseif($tab === 'kiosk' && $event->event_type !== \App\EventType::QR_TAG)
            <livewire:admin.events.tabs.kiosk.kiosk :event="$event" :key="'kiosk-'.$event->id" />
        @else
            <livewire:admin.events.tabs.view-details :event="$event" :key="'view-'.$event->id" />
        @endif
    </div>

    <x-admin.event.modal :event="$event" />
</div>
