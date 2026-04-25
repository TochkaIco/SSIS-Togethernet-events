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

            <flux:button icon="user-plus" wire:click="createUserModal" class="cursor-pointer" />
        </div>
    </div>

    {{-- Users Table for Desktop --}}
    <flux:table class="hidden md:table">
        <flux:table.columns>
            <flux:table.column>{{ __('Index') }}</flux:table.column>
            <flux:table.column>{{ __('User') }}</flux:table.column>
            <flux:table.column>{{ __('Class') }}</flux:table.column>
            <flux:table.column>{{ __('Email') }}</flux:table.column>
            <flux:table.column>{{ __('Roles') }}</flux:table.column>
            <flux:table.column>{{ __('Permissions') }}</flux:table.column>
            <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($users as $user)
                <flux:table.row :key="'user-'.$user->id" class="hover:bg-zinc-50 dark:hover:bg-white/5 transition-colors group">
                    {{-- Pagination-aware Index --}}
                    <flux:table.cell class="group-hover:text-zinc-800 dark:group-hover:text-zinc-200 transition-colors text-center">
                        {{ $users->firstItem() + $loop->index }}
                    </flux:table.cell>

                    {{-- Avatar & Name --}}
                    <flux:table.cell>
                        <button wire:click="viewUserProfile({{ $user->id }})" class="flex items-center cursor-pointer gap-x-3 text-left">
                            <flux:avatar circle class="size-10" :initials="$user->initials()" :src="$user->profile_picture" />
                            <span class="font-medium group-hover:text-orange-300 group-hover:underline">{{ $user->name }}</span>
                        </button>
                    </flux:table.cell>

                    <flux:table.cell>
                        <x-class-badge :user-class="$user->class ?? 'Unknown'" />
                    </flux:table.cell>

                    <flux:table.cell>
                        <a href="mailto:{{ $user->email }}" class="text-zinc-500 hover:text-orange-300 hover:underline">
                            {{ $user->email }}
                        </a>
                    </flux:table.cell>

                    {{-- Roles Badges --}}
                    <flux:table.cell>
                        <div class="flex flex-wrap gap-1 max-w-50">
                            @foreach ($user->roles as $role)
                                <flux:badge size="sm" color="yellow" class="whitespace-nowrap">
                                    {{ $role->name }}
                                </flux:badge>
                            @endforeach
                        </div>
                    </flux:table.cell>

                    {{-- Permissions Summary --}}
                    <flux:table.cell>
                        <div class="flex flex-col gap-1">
                            <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200">
                                {{ $user->getAllPermissions()->count() }} {{ __('Permissions') }}
                            </span>
                            <div class="flex flex-wrap gap-1 max-w-60">
                                @foreach ($user->getAllPermissions()->take(3) as $permission)
                                    <span class="text-[11px] text-zinc-500 italic">
                                        {{ Str::headline($permission->name) }}{{ !$loop->last ? ',' : '' }}
                                    </span>
                                @endforeach
                                @if($user->getAllPermissions()->count() > 3)
                                    <span class="text-[10px] text-zinc-400 font-medium">+{{ $user->getAllPermissions()->count() - 3 }} {{ __('more') }}</span>
                                @endif
                            </div>
                        </div>
                    </flux:table.cell>

                    {{-- Actions --}}
                    <flux:table.cell align="end">
                        <div class="flex justify-end mr-2 gap-2">
                            <flux:button size="sm" wire:click="editAccess({{ $user->id }})" class="cursor-pointer" icon="shield-check" variant="ghost">{{ __('Manage') }}</flux:button>

                            @can('delete users')
                                <flux:button
                                    size="sm"
                                    wire:click="confirmDelete({{ $user->id }})"
                                    variant="ghost"
                                    icon="trash"
                                    class="hover:text-red-600 cursor-pointer"
                                />
                            @endcan
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    <div class="md:hidden space-y-4">
        @foreach ($users as $user)
            <div class="p-4 bg-white dark:bg-white/5 border border-zinc-200 dark:border-white/10 rounded-xl space-y-4">
                {{-- Header: Avatar, Name, and Actions --}}
                <div class="flex items-start justify-between">
                    <button wire:click="viewUserProfile({{ $user->id }})" class="flex items-center gap-3 text-left">
                        <flux:avatar circle class="size-12" :initials="$user->initials()" :src="$user->profile_picture" />
                        <div>
                            <div class="font-semibold text-zinc-800 dark:text-white">{{ $user->name }}</div>
                            <div class="text-sm text-zinc-500">#{{ $users->firstItem() + $loop->index }}</div>
                        </div>
                    </button>

                    <div class="flex gap-1">
                        <flux:button size="sm" wire:click="editAccess({{ $user->id }})" icon="shield-check" variant="ghost" />
                        @can('delete users')
                            <flux:button size="sm" wire:click="confirmDelete({{ $user->id }})" variant="ghost" icon="trash" class="text-red-500" />
                        @endcan
                    </div>
                </div>

                <flux:separator />

                {{-- Body: Details Grid --}}
                <div class="flex flex-col gap-4 text-sm">
                    <div class="space-y-3">
                        <div class="flex flex-col">
                            <flux:label class="text-xs font-medium uppercase tracking-wider">{{ __('Email') }}</flux:label>
                            <a href="mailto:{{ $user->email }}" class="hover:text-orange-400 text-muted-foreground underline truncate">
                                {{ $user->email }}
                            </a>
                        </div>

                        <flux:label class="text-xs font-medium uppercase tracking-wider mr-1">{{ __('Class') }}</flux:label>
                        <x-class-badge :user-class="$user->class ?? 'Unknown'" />
                    </div>

                    <div class="col-span-2 space-3">
                        <flux:label class="text-xs font-medium uppercase tracking-wider">{{ __('Roles') }}</flux:label>
                        <div class="flex flex-wrap gap-5 mt-3 ml-2">
                            @foreach ($user->roles as $role)
                                <flux:badge size="sm" color="yellow" inset>{{ $role->name }}</flux:badge>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-span-2">
                        <div class="text-xs font-medium text-zinc-400 uppercase tracking-wider mb-1">{{ __('Permissions') }}</div>
                        <div class="text-zinc-600 dark:text-zinc-400 italic text-xs">
                            {{ $user->getAllPermissions()->take(5)->pluck('name')->map(fn($n) => Str::headline($n))->join(', ') }}
                            @if($user->getAllPermissions()->count() > 5)
                                <span class="font-bold text-zinc-800 dark:text-zinc-200"> +{{ $user->getAllPermissions()->count() - 5 }} more</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <flux:pagination :paginator="$users" scroll-to class="mt-3" />

    {{-- Modal: Manage Roles & Permissions --}}
    <flux:modal name="edit-user-permissions" class="md:w-125">
        <div x-data="{ tab: 'roles' }" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Access Control') }}</flux:heading>
                <flux:subheading>{{ __('Managing:') }} <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ $editingUserName }}</span></flux:subheading>
            </div>

            {{-- Tabs --}}
            <div class="flex p-1 bg-zinc-100 dark:bg-zinc-800 rounded-lg">
                <button @click="tab = 'roles'" :class="tab === 'roles' ? 'shadow-sm text-orange-300' : 'cursor-pointer'" class="flex-1 py-1.5 text-sm font-medium rounded-md transition-all">{{ __('Roles') }}</button>
                <button @click="tab = 'permissions'" :class="tab === 'permissions' ? 'shadow-sm text-orange-300' : 'cursor-pointer'" class="flex-1 py-1.5 text-sm font-medium rounded-md transition-all">{{ __('Permissions') }}</button>
            </div>

            {{-- Roles Content --}}
            <div x-show="tab === 'roles'">
                <flux:checkbox.group wire:model="userRoles" :label="__('Assign Role Groups')">
                    <div class="grid grid-cols-2 gap-3 mt-3">
                        @foreach($allRoles as $role)
                            <flux:checkbox :value="$role->name" :label="Str::headline($role->name)" />
                        @endforeach
                    </div>
                </flux:checkbox.group>
            </div>

            {{-- Permissions Content --}}
            <div x-show="tab === 'permissions'" x-cloak>
                <flux:checkbox.group
                    wire:model="userPermissions"
                    :label="__('Individual Overrides')"
                    wire:key="permissions-group-{{ $editingUserId }}"
                >
                    <div class="grid grid-cols-2 gap-x-4 gap-y-2 mt-3 max-h-64 overflow-y-auto p-3 border rounded-xl bg-zinc-50 dark:bg-zinc-900">
                        @foreach($allPermissions as $permission)
                            <flux:checkbox
                                wire:key="perm-{{ $permission->id }}-{{ $editingUserId }}"
                                :value="$permission->name"
                                :label="Str::headline($permission->name)"
                            />
                        @endforeach
                    </div>
                </flux:checkbox.group>
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t border-accent-foreground">
                <flux:modal.close><flux:button variant="ghost" class="cursor-pointer">{{ __('Cancel') }}</flux:button></flux:modal.close>
                <flux:button wire:click="savePermissions" variant="primary" class="cursor-pointer">{{ __('Save Changes') }}</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Modal: Dangerous Delete --}}
    <flux:modal name="confirm-user-deletion" class="md:w-100">
        <div x-data="{ canDelete: false, timer: 3 }"
             x-on:modal-show.window="canDelete = false; timer = 3; let interval = setInterval(() => { if(timer > 0) { timer-- } else { canDelete = true; clearInterval(interval) } }, 1000)"
        >
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Confirm Deletion') }}</flux:heading>
                    <flux:subheading>
                        {{ __('This action cannot be undone. You must wait ') }}<span x-text="timer" class="font-bold text-red-600"></span>{{ __('s to confirm.') }}
                    </flux:subheading>
                </div>

                <div class="flex gap-2 justify-end">
                    <flux:modal.close>
                        <flux:button variant="ghost" class="cursor-pointer">{{ __('Cancel') }}</flux:button>
                    </flux:modal.close>

                    <flux:button
                        wire:click="deleteUser"
                        variant="danger"
                        class="cursor-pointer"
                        x-bind:disabled="!canDelete"
                        x-bind:class="!canDelete && 'opacity-50 grayscale'"
                    >
                        <span x-show="canDelete">{{ __('Delete Permanently') }}</span>
                        <span x-show="!canDelete">{{ __('Wait') }} (<span x-text="timer"></span>s)</span>
                    </flux:button>
                </div>
            </div>
        </div>
    </flux:modal>

    {{-- Modal: Create User --}}
    <flux:modal name="create-user" class="md:w-100">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Create User') }}</flux:heading>
                <flux:subheading>
                    {{ __('Here you can create a user that hasn\'t logged in yet') }}
                </flux:subheading>
            </div>

            <div class="space-y-3">
                <flux:input wire:model="createUserName" label="{{ __('Name') }} *" placeholder="{{ __('User\'s Name') }}" required />
                <flux:input wire:model="createUserEmail" label="{{ __('Email') }} *" placeholder="12abcd@stockholmscience.se" required />
                <flux:select label="{{ __('Class') }} *" class="w-fit" wire:model.live="createUserClass" wire:key="user-select-class-{{ $user->id }}" required >
                    <flux:select.option value="">{{ __('Unset') }}</flux:select.option>
                    @foreach($user->validClasses() as $classOption)
                        <flux:select.option :value="$classOption">{{ $classOption }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div class="flex gap-2 justify-end">
                <flux:modal.close>
                    <flux:button variant="ghost" class="cursor-pointer">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button
                    wire:click="createUser"
                    variant="primary"
                    class="cursor-pointer"
                >
                    {{ __('Create') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
