<div class="max-w-full overflow-x-hidden">
    <flux:table class="hidden md:table">
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
                            <flux:avatar class="size-10" circle :initials="$waiting->user->initials()" :src="$waiting->user->profile_picture" />
                            <span class="font-medium hover:underline hover:text-orange-300">{{ $waiting->user->name }}</span>
                        </button>
                    </flux:table.cell>

                    <flux:table.cell>
                        <x-class-badge :user-class="$waiting->user->class ?? 'Unknown'" />
                    </flux:table.cell>

                    <flux:table.cell>
                        <a href="mailto:{{ $waiting->user->email }}" class="hover:text-orange-300 hover:underline">
                            {{ $waiting->user->email }}
                        </a>
                    </flux:table.cell>

                    @if($event->one_hour_periods)
                        <flux:table.cell>
                            @php
                                $periodLabel = $waiting->eventPeriod?->label;
                            @endphp
                            {{ $periodLabel ?? __('N/A') }}
                        </flux:table.cell>
                    @endif

                    <flux:table.cell>{{ $waiting->created_at->diffForHumans() }}</flux:table.cell>

                    <flux:table.cell align="end">
                        <flux:button wire:click="moveToParticipants({{ $waiting->id }})" size="sm" variant="primary" class="cursor-pointer">
                            {{ __('Move to Participants') }}
                        </flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="text-center py-12 w-full">
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

    {{-- Waiting List for Mobile --}}
    <div class="md:hidden space-y-4">
        @forelse ($waitingList as $waiting)
            <div class="p-4 bg-white dark:bg-white/5 border border-zinc-200 dark:border-white/10 rounded-xl space-y-4">
                {{-- Header: Avatar, Name --}}
                <div class="flex items-start justify-between gap-2">
                    <button wire:click="viewUserProfile({{ $waiting->id }})" class="flex items-center gap-3 text-left min-w-0">
                        <flux:avatar circle class="size-12 flex-shrink-0" :initials="$waiting->user->initials()" :src="$waiting->user->profile_picture" />
                        <div class="min-w-0">
                            <div class="font-semibold text-zinc-800 dark:text-white truncate">{{ $waiting->user->name }}</div>
                            <div class="text-xs text-zinc-500 truncate">{{ $waiting->user->email }}</div>
                        </div>
                    </button>
                </div>

                <flux:separator />

                {{-- Body: Details --}}
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div class="space-y-1">
                        <flux:label class="text-xs font-medium uppercase tracking-wider">{{ __('Class') }}</flux:label>
                        <div>
                            <x-class-badge :user-class="$waiting->user->class ?? 'Unknown'" />
                        </div>
                    </div>

                    <div class="space-y-1">
                        <flux:label class="text-xs font-medium uppercase tracking-wider">{{ __('Registered At') }}</flux:label>
                        <div class="text-zinc-600 dark:text-zinc-400">
                            {{ $waiting->created_at->diffForHumans() }}
                        </div>
                    </div>

                    @if($event->one_hour_periods)
                        <div class="col-span-2 space-y-1">
                            <flux:label class="text-xs font-medium uppercase tracking-wider">{{ __('Period') }}</flux:label>
                            <div class="text-zinc-600 dark:text-zinc-400">
                                {{ $waiting->eventPeriod?->label ?? __('N/A') }}
                            </div>
                        </div>
                    @endif

                    <div class="col-span-2 pt-2">
                        <flux:button wire:click="moveToParticipants({{ $waiting->id }})" size="sm" variant="primary" class="w-full cursor-pointer">
                            {{ __('Move to Participants') }}
                        </flux:button>
                    </div>
                </div>
            </div>
        @empty
            <div class="p-8 text-center bg-white dark:bg-white/5 border border-zinc-200 dark:border-white/10 rounded-xl">
                <flux:icon.users class="size-10 mx-auto mb-3 opacity-50" />
                <flux:heading>{{ __('No users on waiting list') }}</flux:heading>
            </div>
        @endforelse
    </div>
</div>
