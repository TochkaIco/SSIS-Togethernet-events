<div>
    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('Name') }}</flux:table.column>
            <flux:table.column>{{ __('Email') }}</flux:table.column>
            <flux:table.column>{{ __('Registered At') }}</flux:table.column>
            <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse($waitingList as $waiting)
                <flux:table.row :key="$waiting->id">
                    <flux:table.cell>
                        <button wire:click="viewUserProfile({{ $waiting->id }})" class="group flex items-center hover:underline hover:text-orange-300 cursor-pointer gap-x-3 text-left">
                            <flux:avatar class="size-10" :initials="$waiting->initials()" :src="$waiting->profile_picture" />
                            <span class="font-medium group-hover:text-orange-300 group-hover:underline">{{ $waiting->name }}</span>
                        </button>
                        <button wire:click="viewUserProfile({{ $waiting->id }})" class="flex items-center hover:underline hover:text-orange-300 cursor-pointer gap-x-3 text-left">
                            <flux:avatar class="size-10" :initials="$waiting->initials()" :src="$waiting->profile_picture" />
                            <span class="font-medium">{{ $waiting->name }}</span>
                        </button>
                    </flux:table.cell>

                    <flux:table.cell>
                        <a href="mailto:{{ $waiting->email }}" class="hover:text-orange-300 hover:underline">
                            {{ $waiting->email }}
                        </a>
                    </flux:table.cell>

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
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
