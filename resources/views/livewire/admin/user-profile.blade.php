<div class="p-6 max-w-4xl mx-auto">
    <div class="flex flex-col items-start space-y-3 mb-8">
        <flux:button href="{{ route('admin.users') }}" variant="ghost" icon="arrow-left" wire:navigate>{{ __('Back to Users') }}</flux:button>
        <div class="flex items-center gap-6">
            <flux:avatar class="h-24 w-24" circle :name="$user->name" :initials="$user->initials()" :src="$user->profile_picture" />
            <div>
                <div class="flex flex-col md:flex-row space-x-1 space-y-1 md:space-y-0">
                    <flux:input wire:model="name" class="font-semibold" :value="$user->name"></flux:input>
                    <flux:button class="cursor-pointer text-lg" wire:click="changeUserName" variant="ghost">{{ __('Save') }}</flux:button>
                </div>
                <flux:subheading><a href="mailto:{{ $user->email }}" class="hover:text-orange-300 hover:underline">{{ $user->email }}</a></flux:subheading>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        {{-- User Information --}}
        <div class="space-y-6">
            <flux:heading size="lg">{{ __('User Details') }}</flux:heading>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:label>{{ __('Created At') }}</flux:label>
                    <flux:text>{{ $user->created_at->format('M d, Y H:i') }}</flux:text>
                </div>
                <div>
                    <flux:label>{{ __('Last Activity') }}</flux:label>
                    <flux:text>{{ $lastActivity ? $lastActivity->diffForHumans() : __('Never') }}</flux:text>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <flux:label>{{ __('Class') }}</flux:label>
            <flux:select class="w-fit" wire:model.live="selectedClass" wire:key="user-select-class-{{ $user->id }}">
                <flux:select.option value="">{{ __('Unset') }}</flux:select.option>
                @foreach($user->validClasses() as $classOption)
                    <flux:select.option :value="$classOption">{{ $classOption }}</flux:select.option>
                @endforeach
            </flux:select>

            {{-- Roles and Permissions --}}
            <flux:heading size="xl">{{ __('Access Control') }}</flux:heading>

            <div>
                <flux:label class="mb-2 text-xl">{{ __('Roles') }}</flux:label>
                <div class="flex flex-wrap gap-2">
                    @forelse ($user->roles as $role)
                        <flux:badge variant="primary" size="sm">{{ $role->name }}</flux:badge>
                    @empty
                        <flux:text>{{ __('No roles assigned.') }}</flux:text>
                    @endforelse
                </div>
            </div>

            <div>
                <flux:label class="mb-2 text-xl">{{ __('Permissions') }}</flux:label>
                <div class="flex flex-wrap gap-1">
                    @forelse ($user->getAllPermissions() as $permission)
                        <flux:badge variant="ghost" size="sm" class="whitespace-nowrap w-min rounded-full border px-2 py-1 text-xs font-medium bg-yellow-500/10 text-yellow-500 border-yellow-500/20">
                            {{ str_replace('_', ' ', $permission->name) }}
                        </flux:badge>
                    @empty
                        <flux:text>{{ __('No specific permissions.') }}</flux:text>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
