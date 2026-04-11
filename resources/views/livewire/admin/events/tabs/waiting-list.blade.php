<div class="md:min-w-5xl">
    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('Name') }}</flux:table.column>
            <flux:table.column>{{ __('Class') }}</flux:table.column>
            <flux:table.column>{{ __('Email') }}</flux:table.column>
            @if($event->one_hour_periods)
                <flux:table.column>{{ __('Period') }}</flux:table.column>
            @endif
            <flux:table.column>{{ __('Registered At') }}</flux:table.column>
            <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse($waitingList as $waiting)
                <flux:table.row :key="$waiting->id">
                    <flux:table.cell>
                        <button wire:click="viewUserProfile({{ $waiting->id }})" class="flex items-center cursor-pointer gap-x-3 text-left">
                            <flux:avatar class="size-10" circle :initials="$waiting->initials()" :src="$waiting->profile_picture" />
                            <span class="font-medium hover:underline hover:text-orange-300">{{ $waiting->name }}</span>
                        </button>
                    </flux:table.cell>

                    <flux:table.cell>
                        <x-class-badge :user-class="$waiting->class ?? 'Unknown'" />
                    </flux:table.cell>

                    <flux:table.cell>
                        <a href="mailto:{{ $waiting->email }}" class="hover:text-orange-300 hover:underline">
                            {{ $waiting->email }}
                        </a>
                    </flux:table.cell>

                    @if($event->one_hour_periods)
                        <flux:table.cell>
                            @php
                                $periodLabel = $event->eventPeriods()->where('type', 'period')->where('number', $waiting->pivot->period)->first()?->label;
                            @endphp
                            {{ $periodLabel ?? __('N/A') }}
                        </flux:table.cell>
                    @endif

                    <flux:table.cell>{{ $waiting->pivot->created_at->diffForHumans() }}</flux:table.cell>

                    <flux:table.cell align="end">
                        <flux:button wire:click="moveToParticipants({{ $waiting->id }})" size="sm" variant="primary" class="cursor-pointer">
                            {{ __('Move to Participants') }}
                        </flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="4" class="text-center py-12">
                        <div class="flex flex-col items-center justify-center">
                            <flux:icon.users class="size-12 mb-4" />
                            <flux:heading>{{ __('No users on waiting list') }}</flux:heading>
                            <flux:subheading>{{ __('Users will appear here when the event is full.') }}</flux:subheading>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
