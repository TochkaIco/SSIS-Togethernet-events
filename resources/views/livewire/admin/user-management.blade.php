<div class="p-6">
    {{-- Header & Search --}}
    <div class="flex flex-col md:flex-row gap-4 mb-6">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Search by name or email..." class="flex-1" />

        <flux:select wire:model.live="filterRole" placeholder="Filter by Role" class="md:w-64">
            <flux:select.option value="">All Roles</flux:select.option>
            @foreach($allRoles as $role)
                <flux:select.option :value="$role->name">{{ ucfirst($role->name) }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    {{-- Users Table --}}
    <flux:table>
        <flux:table.columns>
            <flux:table.column>Index</flux:table.column>
            <flux:table.column>User</flux:table.column>
            <flux:table.column>Email</flux:table.column>
            <flux:table.column>Roles</flux:table.column>
            <flux:table.column>Permissions</flux:table.column>
            <flux:table.column align="end">Actions</flux:table.column>
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
                                {{ $user->getAllPermissions()->count() }} Permissions
                            </span>
                            <div class="flex flex-wrap gap-1 max-w-60">
                                @foreach ($user->getAllPermissions()->take(3) as $permission)
                                    <span class="text-[11px] text-zinc-500 italic">
                                        {{ Str::headline($permission->name) }}{{ !$loop->last ? ',' : '' }}
                                    </span>
                                @endforeach
                                @if($user->getAllPermissions()->count() > 3)
                                    <span class="text-[10px] text-zinc-400 font-medium">+{{ $user->getAllPermissions()->count() - 3 }} more</span>
                                @endif
                            </div>
                        </div>
                    </flux:table.cell>

                    {{-- Actions --}}
                    <flux:table.cell align="end">
                        <div class="flex justify-end mr-2 gap-2">
                            <flux:button size="sm" wire:click="editRoles({{ $user->id }})" class="cursor-pointer" icon="shield-check" variant="ghost">Manage</flux:button>

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

    <div class="mt-6">
        {{ $users->links() }}
    </div>

    {{-- Modal: Manage Roles & Permissions --}}
    <flux:modal name="edit-user-permissions" class="md:w-125">
        <div x-data="{ tab: 'roles' }" class="space-y-6">
            <div>
                <flux:heading size="lg">Access Control</flux:heading>
                <flux:subheading>Managing: <span class="font-bold text-zinc-800 dark:text-zinc-200">{{ $editingUserName }}</span></flux:subheading>
            </div>

            {{-- Tabs --}}
            <div class="flex p-1 bg-zinc-100 dark:bg-zinc-800 rounded-lg">
                <button @click="tab = 'roles'" :class="tab === 'roles' ? 'shadow-sm text-orange-300' : 'cursor-pointer'" class="flex-1 py-1.5 text-sm font-medium rounded-md transition-all">Roles</button>
                <button @click="tab = 'permissions'" :class="tab === 'permissions' ? 'shadow-sm text-orange-300' : 'cursor-pointer'" class="flex-1 py-1.5 text-sm font-medium rounded-md transition-all">Permissions</button>
            </div>

            {{-- Roles Content --}}
            <div x-show="tab === 'roles'">
                <flux:checkbox.group wire:model="userRoles" label="Assign Role Groups">
                    <div class="grid grid-cols-2 gap-3 mt-3">
                        @foreach($allRoles as $role)
                            <flux:checkbox :value="$role->name" :label="Str::headline($role->name)" />
                        @endforeach
                    </div>
                </flux:checkbox.group>
            </div>

            {{-- Permissions Content --}}
            <div x-show="tab === 'permissions'" x-cloak>
                <flux:checkbox.group wire:model="userPermissions" label="Individual Overrides">
                    <div class="grid grid-cols-2 gap-x-4 gap-y-2 mt-3 max-h-64 overflow-y-auto p-3 border rounded-xl bg-zinc-50 dark:bg-zinc-900">
                        @foreach($allPermissions as $permission)
                            <flux:checkbox :value="$permission->name" :label="Str::headline($permission->name)" />
                        @endforeach
                    </div>
                </flux:checkbox.group>
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t border-accent-foreground">
                <flux:modal.close><flux:button variant="ghost" class="cursor-pointer">Cancel</flux:button></flux:modal.close>
                <flux:button wire:click="savePermissions" variant="primary" class="cursor-pointer">Save Changes</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Modal: Dangerous Delete --}}
    <flux:modal name="confirm-user-deletion" class="md:w-100">
        <div x-data="{ canDelete: false, timer: 3 }"
             x-on:modal-show.window="canDelete = false; timer = 3; let interval = setInterval(() => { if(timer > 0) { timer-- } else { canDelete = true; clearInterval(interval) } }, 1000)">

            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Confirm Deletion</flux:heading>
                    <flux:subheading>
                        This action cannot be undone. You must wait <span x-text="timer" class="font-bold text-red-600"></span>s to confirm.
                    </flux:subheading>
                </div>

                <div class="flex gap-2 justify-end">
                    <flux:modal.close>
                        <flux:button variant="ghost">Cancel</flux:button>
                    </flux:modal.close>

                    <flux:button
                        wire:click="deleteUser"
                        variant="danger"
                        x-bind:disabled="!canDelete"
                        x-bind:class="!canDelete && 'opacity-50 grayscale'"
                    >
                        <span x-show="canDelete">Delete Permanently</span>
                        <span x-show="!canDelete">Wait (<span x-text="timer"></span>s)</span>
                    </flux:button>
                </div>
            </div>
        </div>
    </flux:modal>
</div>
