<div class="py-8 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
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
                    class="cursor-pointer"
                    data-test="edit-event-button"
                    wire:click="eventEdit"
                >
                    <flux:icon.pencil-square class="mr-2 size-4" />
                    {{ __('Edit') }}
                </flux:button>
                <form action="{{ route('admin.event.destroy', $event) }}" method="post" onsubmit="return confirm('{{ __('Are you sure you want to delete this event?') }}')">
                    @csrf
                    @method('DELETE')
                    <flux:button type="submit" variant="danger" class="cursor-pointer">
                        {{ __('Delete') }}
                    </flux:button>
                </form>
                @endhaspermission
            </div>
        </div>

        <flux:navbar class="-mb-px overflow-x-auto">
            <flux:navbar.item wire:click="$set('tab', 'view')" :current="$tab === 'view'" class="cursor-pointer">{{ __('View') }}</flux:navbar.item>
            <flux:navbar.item wire:click="$set('tab', 'participants')" :current="$tab === 'participants'" class="cursor-pointer">{{ __('Participants') }}</flux:navbar.item>
            <flux:navbar.item wire:click="$set('tab', 'waiting')" :current="$tab === 'waiting'" class="cursor-pointer">{{ __('Waiting List') }}</flux:navbar.item>
            <flux:navbar.item wire:click="$set('tab', 'kiosk')" :current="$tab === 'kiosk'" class="cursor-pointer">{{ __('Kiosk') }}</flux:navbar.item>
        </flux:navbar>
    </div>

    <div class="mt-6">
        @if($tab === 'view')
            <livewire:admin.events.tabs.view-details :event="$event" :key="'view-'.$event->id" />
        @elseif($tab === 'participants')
            <livewire:admin.events.tabs.participants :event="$event" :key="'part-'.$event->id" />
        @elseif($tab === 'waiting')
            <livewire:admin.events.tabs.waiting-list :event="$event" :key="'wait-'.$event->id" />
        @elseif($tab === 'kiosk')
            <livewire:admin.events.tabs.kiosk :event="$event" :key="'kiosk-'.$event->id" />
        @endif
    </div>

    <x-admin.event.modal :event="$event" />
</div>
