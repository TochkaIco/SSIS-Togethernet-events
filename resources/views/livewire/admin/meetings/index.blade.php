<div class="p-6">
    <div class="flex items-center justify-between mb-6 space-x-2">
        <div>
            <flux:heading size="xl">{{ __('Meetings') }}</flux:heading>
            <flux:subheading>{{ __('Manage Togethernet meetings and protocols') }}</flux:subheading>
        </div>
        <flux:button wire:click="createMeeting" variant="primary" icon="plus">
            {{ __('Create Meeting') }}
        </flux:button>
    </div>

    {{-- Users Table for Desktop --}}
    <flux:table class="hidden md:table">
        <flux:table.columns>
            <flux:table.column>{{ __('Title') }}</flux:table.column>
            <flux:table.column>{{ __('Date') }}</flux:table.column>
            <flux:table.column>{{ __('Starts At') }}</flux:table.column>
            <flux:table.column>{{ __('Ends At') }}</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($meetings as $meeting)
                <flux:table.row :key="$meeting->id">
                    <flux:table.cell>
                        <a href="{{ route('admin.meetings.show', $meeting) }}" class="font-medium cursor-pointer hover:underline hover:text-orange-300">{{ $meeting->title }}</a>
                    </flux:table.cell>
                    <flux:table.cell>{{ $meeting->meeting_starts_at->format('Y-m-d') }}</flux:table.cell>
                    <flux:table.cell>{{ $meeting->meeting_starts_at->format('H:i') }}</flux:table.cell>
                    <flux:table.cell>{{ $meeting->meeting_ends_at ? $meeting->meeting_ends_at->format('H:i') : '-' }}</flux:table.cell>
                    <flux:table.cell class="flex justify-end gap-2">
                        <flux:button :href="route('admin.meetings.show', $meeting)" icon="eye" size="sm" variant="ghost" wire:navigate />
                        <flux:button :href="route('admin.meetings.protocol', $meeting)" icon="pencil-square" size="sm" variant="ghost" wire:navigate />
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    {{-- Users Table for Mobile --}}
    <div class="space-y-4 md:hidden">
        @foreach ($meetings as $meeting)
            <div class="p-4 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-sm">
                <div class="flex justify-between items-start mb-3">
                    <div class="flex-1">
                        <a href="{{ route('admin.meetings.show', $meeting) }}" class="text-lg font-bold text-zinc-800 dark:text-white hover:underline decoration-orange-300">
                            {{ $meeting->title }}
                        </a>
                        <div class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                            <flux:icon.calendar variant="micro" class="inline mr-1" />
                            {{ $meeting->meeting_starts_at->format('Y-m-d') }}
                        </div>
                    </div>

                    <div class="flex gap-1">
                        <flux:button :href="route('admin.meetings.show', $meeting)" icon="eye" size="sm" variant="ghost" wire:navigate />
                        <flux:button :href="route('admin.meetings.protocol', $meeting)" icon="pencil-square" size="sm" variant="ghost" wire:navigate />
                    </div>
                </div>

                <div class="flex items-center gap-4 py-2 border-t border-zinc-100 dark:border-zinc-700 mt-2 pt-3">
                    <div>
                        <p class="text-xs uppercase tracking-wider text-zinc-400">{{ __('Starts') }}</p>
                        <p class="font-medium">{{ $meeting->meeting_starts_at->format('H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wider text-zinc-400">{{ __('Ends') }}</p>
                        <p class="font-medium">{{ $meeting->meeting_ends_at ? $meeting->meeting_ends_at->format('H:i') : '-' }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <flux:pagination :paginator="$meetings" scroll-to class="mt-3" />
</div>
