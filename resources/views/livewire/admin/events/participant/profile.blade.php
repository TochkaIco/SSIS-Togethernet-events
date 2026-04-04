<div class="p-6 max-w-4xl mx-auto">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div class="flex flex-col items-start space-y-3 mb-8">
            <flux:button href="{{ route('admin.event.show', ['event' => $event, 'tab' => 'participants']) }}" variant="ghost" icon="arrow-left" wire:navigate>{{ __('Back to Participants') }}</flux:button>
            <div class="flex items-center gap-6">
                <flux:avatar class="h-24 w-24" circle :name="$user->name" :initials="$user->initials()" :src="$user->profile_picture" />
                <div>
                    <flux:heading size="xl">{{ $user->name }}</flux:heading>
                    <flux:subheading><a href="mailto:{{ $user->email }}" class="hover:text-orange-300 hover:underline">{{ $user->email }}</a></flux:subheading>
                </div>
            </div>
        </div>

        <div class="flex space-x-1">
            <flux:label>Role: </flux:label>
            @if($this->participantIsWorking())
                <flux:badge size="sm" color="orange">{{ __('Worker') }}</flux:badge>
            @else
                <flux:badge size="sm">{{ __('Attendee') }}</flux:badge>
            @endif

            @if($this->user->pivot->in_waitinglist)
                <flux:badge size="sm" color="yellow">{{ __('On Waiting List') }}</flux:badge>
            @else
                <flux:badge size="sm" color="green">{{ __('Registered Participant') }}</flux:badge>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-6">
        <div class="space-y-6">
            <flux:heading size="lg">User Details</flux:heading>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:label>Created At</flux:label>
                    <flux:text>{{ $user->created_at->format('M d, Y H:i') }}</flux:text>
                </div>
                <div>
                    <flux:label>Last Activity</flux:label>
                    <flux:text>{{ $lastActivity ? $lastActivity->diffForHumans() : 'Never' }}</flux:text>
                </div>
                <div>
                    <flux:label>Registered For Event At</flux:label>
                    <flux:text>{{ $this->user->pivot->created_at->format('M d, Y H:i') }}</flux:text>
                </div>
            </div>
        </div>

        {{-- Roles and Permissions --}}
        <div class="space-y-6">
            <flux:heading size="xl">Access Control</flux:heading>

            <div>
                <flux:label class="mb-2">Roles</flux:label>
                <div class="flex flex-wrap gap-2">
                    @forelse ($user->roles as $role)
                        <flux:badge variant="primary" size="sm">{{ $role->name }}</flux:badge>
                    @empty
                        <flux:text>No roles assigned.</flux:text>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
