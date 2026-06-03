<x-layouts::app :title="__('Confirm Tag')">
    <div class="py-12 max-w-lg mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <flux:card class="space-y-6">
            <div class="flex flex-col items-center gap-4">
                <flux:avatar :initials="$victim->initials()" size="xl" class="shrink-0" />
                <flux:heading size="xl">{{ __('Tag :name?', ['name' => $victim->name]) }}</flux:heading>
                <flux:text class="text-muted-foreground">{{ __('You are about to tag :name in the event :event.', ['name' => $victim->name, 'event' => $event->title]) }}</flux:text>
            </div>

            <form action="{{ route('qr_tag.scan', ['token' => $token]) }}" method="POST">
                @csrf
                <div class="flex flex-col gap-3 mt-6">
                    <flux:button type="submit" variant="primary" class="w-full cursor-pointer">
                        {{ __('Confirm Tag') }}
                    </flux:button>
                    <flux:button :href="route('event.show', $event)" variant="ghost" class="w-full cursor-pointer">
                        {{ __('Cancel') }}
                    </flux:button>
                </div>
            </form>
        </flux:card>
    </div>
</x-layouts::app>
