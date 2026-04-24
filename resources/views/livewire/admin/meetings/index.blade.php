<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">{{ __('Meetings') }}</flux:heading>
            <flux:subheading>{{ __('Manage Togethernet meetings and protocols') }}</flux:subheading>
        </div>
        <flux:button wire:click="createMeeting" variant="primary" icon="plus">
            {{ __('Create Meeting') }}
        </flux:button>
    </div>

    <flux:table>
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
</div>
