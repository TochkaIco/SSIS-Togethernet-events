<div class="p-6">
    {{-- Header & Search --}}
    <div class="flex flex-col md:flex-row gap-4 mb-6">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="{{ __('Search by name or email...') }}" class="flex-1" />

        <div class="flex gap-2 md:gap-4">
            <flux:select wire:model.live="filterClassGroup" placeholder="{{ __('Filter by Class') }}" class="md:w-64 flex-1">
                <flux:select.option value="">{{ __('All Classes') }}</flux:select.option>
                @foreach($allClassGroups as $classGroup)
                    <flux:select.option :value="$classGroup">{{ ucfirst($classGroup) }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="filterRole" placeholder="{{ __('Filter by Role') }}" class="md:w-64 flex-1">
                <flux:select.option value="">{{ __('All Roles') }}</flux:select.option>
                @foreach($allRoles as $role)
                    <flux:select.option :value="$role->name">{{ ucfirst($role->name) }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>
    </div>

    {{-- Users Table for Desktop --}}
    <flux:table class="hidden md:table">
        <flux:table.columns>
            <flux:table.column>{{ __('User') }}</flux:table.column>
            <flux:table.column>{{ __('Class') }}</flux:table.column>
            <flux:table.column>{{ __('Email') }}</flux:table.column>
            <flux:table.column>{{ __('Roles') }}</flux:table.column>
            <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($users as $user)
                <flux:table.row :key="'user-'.$user->id" class="hover:bg-zinc-50 dark:hover:bg-white/5 transition-colors group">
                    {{-- Avatar & Name --}}
                    <flux:table.cell>
                        <div class="flex items-center ml-2 gap-x-3">
                            <flux:avatar circle class="size-10" :initials="$user->initials()" :src="$user->profile_picture" />
                            <span class="font-medium">{{ $user->name }}</span>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <x-class-badge :user-class="$user->class ?? 'Unknown'" />
                    </flux:table.cell>

                    <flux:table.cell>
                        <span class="text-zinc-500">
                            {{ $user->email }}
                        </span>
                    </flux:table.cell>

                    {{-- Roles Badges --}}
                    <flux:table.cell>
                        <div class="flex space-x-1">
                            <div class="flex flex-wrap gap-1 w-min max-w-50">
                                @foreach ($user->roles as $role)
                                    <flux:badge size="sm" color="yellow" class="whitespace-nowrap">
                                        {{ $role->name }}
                                    </flux:badge>
                                @endforeach
                            </div>

                            <flux:tooltip toggleable>
                                <flux:button icon="information-circle" size="sm" variant="ghost" />
                                <flux:tooltip.content class="max-w-[20rem] space-y-2">
                                    <flux:label>{{ __('Roles') . ':' }}</flux:label>
                                    <div class="flex flex-wrap gap-1 max-w-60">
                                        @foreach ($user->getAllPermissions() as $permission)
                                            <span>
                                                {{ Str::headline($permission->name) }}{{ !$loop->last ? ',' : '' }}
                                            </span>
                                        @endforeach
                                    </div>
                                </flux:tooltip.content>
                            </flux:tooltip>
                        </div>
                    </flux:table.cell>

                    {{-- Actions --}}
                    <flux:table.cell align="end">
                        <div class="flex justify-end mr-2">
                            @if($user->canBeImpersonated() && $user->id !== auth()->id())
                                <flux:button
                                    size="sm"
                                    wire:click="impersonate({{ $user->id }})"
                                    variant="ghost"
                                    icon="camera"
                                    class="cursor-pointer"
                                >
                                    {{ __('Impersonate') }}
                                </flux:button>
                            @else
                                <flux:badge size="sm" color="zinc">{{ __('N/A') }}</flux:badge>
                            @endif
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    {{-- Users Table for Mobile --}}
    <div class="md:hidden space-y-4">
        @foreach ($users as $user)
            <div class="p-4 bg-white dark:bg-white/5 border border-zinc-200 dark:border-white/10 rounded-xl space-y-4">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3 text-left">
                        <flux:avatar circle class="size-12" :initials="$user->initials()" :src="$user->profile_picture" />
                        <div>
                            <div class="font-semibold text-zinc-800 dark:text-white">{{ $user->name }}</div>
                            <x-class-badge :user-class="$user->class ?? 'Unknown'" />
                        </div>
                    </div>

                    @if($user->canBeImpersonated() && $user->id !== auth()->id())
                        <flux:button
                            size="sm"
                            wire:click="impersonate({{ $user->id }})"
                            icon="camera"
                            variant="ghost"
                            class="cursor-pointer"
                        />
                    @endif
                </div>

                <div class="flex flex-col gap-2 text-sm">
                    <div class="flex flex-col">
                        <flux:label class="text-xs font-medium uppercase tracking-wider">{{ __('Email') }}</flux:label>
                        <a href="mailto:{{ $user->email }}" class="hover:text-orange-400 text-muted-foreground underline truncate">
                            {{ $user->email }}
                        </a>
                    </div>

                    <flux:label class="text-xs font-medium uppercase tracking-wider">{{ __('Roles') }}</flux:label>
                    <div class="flex flex-wrap gap-5 mt-3 ml-2">
                        @foreach ($user->roles as $role)
                            <flux:badge size="sm" color="yellow" inset>{{ $role->name }}</flux:badge>
                        @endforeach
                    </div>

                    <flux:tooltip toggleable>
                        <flux:button icon="information-circle" size="sm" variant="ghost" />
                        <flux:tooltip.content class="max-w-[20rem] space-y-2">
                            <flux:label>{{ __('Roles') . ':' }}</flux:label>
                            <div class="flex flex-wrap gap-1 max-w-60">
                                @foreach ($user->getAllPermissions() as $permission)
                                    <span>
                                            {{ Str::headline($permission->name) }}{{ !$loop->last ? ',' : '' }}
                                        </span>
                                @endforeach
                            </div>
                        </flux:tooltip.content>
                    </flux:tooltip>
                </div>
            </div>
        @endforeach
    </div>

    <flux:pagination :paginator="$users" scroll-to class="mt-3" />
</div>
