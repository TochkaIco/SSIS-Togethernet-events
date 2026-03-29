<div class="p-6 max-w-4xl mx-auto">
    <div class="flex flex-col items-start space-y-3 mb-8">
        <flux:button href="{{ route('admin.users') }}" variant="ghost" icon="arrow-left" wire:navigate>Back to Users</flux:button>
        <div class="flex items-center gap-6">
            <flux:avatar class="h-24 w-24" circle :name="$user->name" :initials="$user->initials()" :src="$user->profile_picture" />
            <div>
                <flux:heading size="xl">{{ $user->name }}</flux:heading>
                <flux:subheading><a href="mailto:{{ $user->email }}" class="hover:text-orange-300 hover:underline">{{ $user->email }}</a></flux:subheading>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        {{-- User Information --}}
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
            </div>
        </div>

        {{-- Roles and Permissions --}}
        <div class="space-y-6">
            <flux:heading size="xl">Access Control</flux:heading>

            <div>
                <flux:label class="mb-2 text-xl">Roles</flux:label>
                <div class="flex flex-wrap gap-2">
                    @forelse ($user->roles as $role)
                        <flux:badge variant="primary" size="sm">{{ $role->name }}</flux:badge>
                    @empty
                        <flux:text>No roles assigned.</flux:text>
                    @endforelse
                </div>
            </div>

            <div>
                <flux:label class="mb-2 text-xl">Permissions</flux:label>
                <div class="flex flex-wrap gap-1">
                    @forelse ($user->getAllPermissions() as $permission)
                        <flux:badge variant="ghost" size="sm" class="whitespace-nowrap w-min rounded-full border px-2 py-1 text-xs font-medium bg-yellow-500/10 text-yellow-500 border-yellow-500/20">
                            {{ str_replace('_', ' ', $permission->name) }}
                        </flux:badge>
                    @empty
                        <flux:text>No specific permissions.</flux:text>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
